<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Kernel\Event;

use Ox\Core\Api\Request\Etags;
use Ox\Core\Kernel\Routing\RequestHelperTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Etag listener to handle the etag caching for a Request
 */
final class EtagListener implements EventSubscriberInterface
{
    use RequestHelperTrait;

    /** @var Etags */
    private $request_etags;

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST  => ['onRequest', 35],
            KernelEvents::RESPONSE => ['onResponse', 110],
        ];
    }

    /**
     * @param RequestEvent $event
     */
    public function onRequest(RequestEvent $event)
    {
        if (!$this->supports($event)) {
            return;
        }

        $request             = $event->getRequest();
        $this->request_etags = Etags::createFromRequest($request);
    }

    /**
     * @param ResponseEvent $event
     *
     * @return void|null
     */
    public function onResponse(ResponseEvent $event)
    {
        $response      = $event->getResponse();
        $response_etag = $response->getEtag();

        if (!$this->supports($event) || !$response_etag || $response->getStatusCode() === Response::HTTP_NOT_MODIFIED) {
            return null;
        }

        // Match etags request & etag response
        if ($this->request_etags && $this->request_etags->hasEtag($response_etag)) {
            $response = $this->getNotModifiedResponse($response_etag);
            $event->setResponse($response);
        }
        // todo need stop propagation ?
    }

    private function getNotModifiedResponse($etag): JsonResponse
    {
        return (new JsonResponse(null, Response::HTTP_NOT_MODIFIED))->setEtag($etag);
    }

    /**
     * Supports only request API
     */
    private function supports(KernelEvent $event): bool
    {
        return $this->isRequestApi($event->getRequest());
    }

    public function getRequestEtags()
    {
        return $this->request_etags;
    }
}
