<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Status;

use Exception;
use Ox\Status\Controllers\StatusController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class StatusAuthListener implements EventSubscriberInterface
{
    const PUBLIC_ROUTES = [
        'status_home',
    ];

    /**
     * @param RequestEvent $event
     *
     * @return void
     * @throws Exception
     */
    public function doAuth(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // home page
        if (in_array($request->get('_route'), self::PUBLIC_ROUTES)) {
            return;
        }

        // private routes
        StatusController::doAuth($request);
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['doAuth', 20]],
        ];
    }

}
