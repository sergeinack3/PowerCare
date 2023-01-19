<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Kernel\Event;

use Ox\Core\Auth\Authenticators\ApiTokenAuthenticator;
use Ox\Core\Kernel\Routing\RequestHelperTrait;
use Ox\Core\Kernel\Routing\RouteManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class CorsListener implements EventSubscriberInterface
{
    use RequestHelperTrait;

    /** @var bool */
    private $is_request_options = false;

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST  => ['onRequest', 9999],
            KernelEvents::RESPONSE => ['onResponse', 9999],
        ];
    }

    /**
     * @param RequestEvent $event
     *
     * @return void
     */
    public function onRequest(RequestEvent $event): void
    {
        if (!$this->supports($event)) {
            return;
        }

        $request = $event->getRequest();
        $method  = $request->getRealMethod();

        if (Request::METHOD_OPTIONS === $method) {
            $this->is_request_options = true;
            $response                 = new Response();
            $response->setStatusCode('204', 'No content');
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }

    /**
     * @param ResponseEvent $event
     *
     * @return void
     */
    public function onResponse(ResponseEvent $event): void
    {
        if (!$this->supports($event)) {
            return;
        }

        if ($this->is_request_options) {
            $event->stopPropagation();
        }

        $response = $event->getResponse();
        $request  = $event->getRequest();
        if ($response) {
            $allow_headers = 'Accept, Content-Type, Authorization, ' . ApiTokenAuthenticator::TOKEN_HEADER_KEY;
            $response->headers->set('Access-Control-Allow-Methods', RouteManager::ALLOWED_METHODS);
            $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('Origin', '*'));
            $response->headers->set('Access-Control-Allow-Headers', $allow_headers);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }
    }

    /**
     * Supports only main requests that are API
     */
    private function supports(KernelEvent $event): bool
    {
        return $event->isMainRequest() && $this->isRequestApi($event->getRequest());
    }

    public function isRequestOption(): bool
    {
        return $this->is_request_options;
    }
}
