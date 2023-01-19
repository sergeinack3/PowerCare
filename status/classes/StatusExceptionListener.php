<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Status;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class StatusExceptionListener implements EventSubscriberInterface
{
    /**
     *
     * @param ExceptionEvent $event
     *
     * @return void
     */
    public function handleException(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();

        $status = $e instanceof StatusException ? $e->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
        $data   = [
            'id'      => md5(serialize($e->getTraceAsString())),
            'status'  => $status,
            'message' => $e->getMessage(),
        ];

        $response = new JsonResponse($data, $status, [], false);

        $event->setResponse($response);
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [['handleException', 100]],
        ];
    }
}
