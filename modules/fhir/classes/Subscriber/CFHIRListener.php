<?php

namespace Ox\Interop\Fhir\Subscriber;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\Kernel\Routing\RequestHelperTrait;
use Ox\Interop\Connectathon\CBlink1;
use Ox\Interop\Fhir\Api\Request\CRequestFormats;
use Ox\Interop\Fhir\Controllers\CFHIRController;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionSearch;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Mediboard\Admin\CUser;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class CFHIRListener implements EventSubscriberInterface, IShortNameAutoloadable
{
    use RequestHelperTrait;

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => [
                ['controll', 60],
            ],
            KernelEvents::EXCEPTION  => [
                ['onException', 200],
            ],
            KernelEvents::RESPONSE   => [
                ['onResponse', 300],
            ],
        ];
    }

    /**
     * @param ControllerEvent $event
     *
     * @return void
     * @throws Exception
     */
    public function controll(ControllerEvent $event): void
    {
        if (!$this->supports($event)) {
            return;
        }

        // activate led when message comme in eai
        // $this->blink1Start($event);

        // Authenticate sender
        $this->authenticateSender($event);

        // start handle request
        $this->start($event);

        // resolve ressource
        $this->resolveResource($event);

        // validate interaction
        $this->validateInteraction($event);

        // cast query parameters
        $this->castParameters($event);
    }

    /**
     * @param ResponseEvent $event
     *
     * @return void
     * @throws Exception
     */
    public function onResponse(ResponseEvent $event): void
    {
        if (!$this->supports($event)) {
            return;
        }

        // controll led when message is treated
        // $this->blink1Stop($event);

        // controll contentLength
        $this->contentLength($event);

        // stop logging
        $this->stop($event);
    }

    /**
     * @param ControllerEvent $event
     *
     * @throws Exception
     */
    public function blink1Start(ControllerEvent $event): void
    {
        CFHIRController::initBlink1();
    }

    /**
     * @param ControllerEvent $event
     *
     * @throws Exception
     */
    public function start(ControllerEvent $event): void
    {
        CFHIRController::start($event->getRequest());
    }

    /**
     * @param ControllerEvent $event
     */
    public function resolveResource(ControllerEvent $event): void
    {
        // route AppFine
        if ($this->isAppFine($event)) {
            return;
        }

        $request = $event->getRequest();
        CFHIRController::resolveResource($request);
    }

    /**
     * @param ResponseEvent $event
     *
     * @throws Exception
     */
    public function blink1Stop(ResponseEvent $event): void
    {
        $response      = $event->getResponse();
        $status_code   = $response->getStatusCode();
        $blink_pattern = null;

        if ($status_code === Response::HTTP_UNAUTHORIZED || $status_code === Response::HTTP_FORBIDDEN) {
            $blink_pattern = CFHIR::BLINK1_UNKNOW;
        } elseif ($status_code >= 200 && $status_code < 300) {
            $blink_pattern = CFHIR::BLINK1_OK;
        } elseif ($status_code >= 300 && $status_code < 400) {
            $blink_pattern = CFHIR::BLINK1_WARNING;
        } elseif ($status_code >= 400) {
            $blink_pattern = CFHIR::BLINK1_ERROR;
        }

        CBlink1::getInstance()->stopPattern($blink_pattern);
        CBlink1::getInstance()->playPattern($blink_pattern);
    }

    /**
     * @param ResponseEvent $event
     */
    public function contentLength(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $headers  = $response->headers;

        if (!$headers->has('Content-Length') && !$headers->has('Transfer-Encoding')) {
            $headers->set('Content-Length', strlen($response->getContent()));
        }
    }

    /**
     * @param ExceptionEvent $event
     *
     * @throws InvalidArgumentException
     */
    public function onException(ExceptionEvent $event): void
    {
        if (!$this->supports($event)) {
            return;
        }

        $throwable = $event->getThrowable();
        if (!$throwable instanceof CFHIRException) {
            $http_status  = $throwable instanceof HttpExceptionInterface ? $throwable->getStatusCode() : 500;
            $http_headers = $throwable instanceof HttpExceptionInterface ? $throwable->getHeaders() : [];
            $throwable    = new CFHIRException(
                $throwable->getMessage(),
                $http_status,
                $http_headers,
                $throwable->getCode()
            );
        }
        //$event->setThrowable($throwable);
        return;

        $format   = $event->getRequest()->get(
            CRequestFormats::KEY_RESOURCE_CONTENT_TYPE,
            CRequestFormats::CONTENT_TYPE_JSON
        );
        $response = $throwable->makeResponse($format);
        $event->setResponse($response);
    }

    /**
     * @param ResponseEvent $event
     *
     * @throws Exception
     */
    public function stop(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        CFHIRController::stop($response);
    }

    /**
     * @param ControllerEvent $event
     *
     * @throws Exception
     */
    public function authenticateSender(ControllerEvent $event): void
    {
        // exclude route from auth by sender
        if (preg_match('@fhir/appFine/Form@', $event->getRequest()->getPathInfo())) {
            return;
        }

        $user = CUser::get();
        CFHIRController::authenticateSender($event->getRequest(), $user);
    }

    /**
     * @param ControllerEvent $event
     *
     * @throws Exception
     */
    public function validateInteraction(ControllerEvent $event): void
    {
        // route AppFine
        if ($this->isAppFine($event)) {
            return;
        }

        CFHIRController::validateInteraction($event->getRequest());
    }

    private function isAppFine(ControllerEvent $event): bool
    {
        // patch for appFine calls
        $request   = $event->getRequest();
        $is_search = $request->attributes->get('object_class') === CFHIRInteractionSearch::class;

        return !$is_search && preg_match('@/fhir/appFine@', $event->getRequest()->getPathInfo());
    }

    /**
     * Cast query parameters for allow the format ?a=1&a=2
     *
     * @param ControllerEvent $event
     */
    public function castParameters(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        if (!empty($request->query) && $query_string = $request->server->get('QUERY_STRING')) {
            $parts    = explode('&', $query_string);
            $elements = [];
            foreach ($parts as $part) {
                [$key, $value] = explode('=', $part, 2);
                $value = urldecode($value);

                if (array_key_exists($key, $elements)) {
                    $value_already_set = $elements[$key];
                    if (!is_array($value_already_set)) {
                        $value_already_set = [$value_already_set];
                    }

                    $elements[$key] = array_merge($value_already_set, [$value]);
                } else {
                    $elements[$key] = $value;
                }
            }

            $request->query = new ParameterBag($elements);
        }
    }

    /**
     * @param KernelEvent $event
     *
     * @return bool
     */
    protected function supports(KernelEvent $event): bool
    {
        return is_subclass_of($this->getController($event->getRequest()), CFHIRController::class);
    }
}
