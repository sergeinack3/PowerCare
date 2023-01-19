<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Kernel\Event;

use Ox\Core\CController;
use Ox\Core\CPermission;
use Ox\Core\Kernel\Routing\RequestHelperTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PermissionListener implements EventSubscriberInterface
{
    use RequestHelperTrait;

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onController', 90],
        ];
    }

    /**
     * @param ControllerEvent $event
     *
     * @return void
     */
    public function onController(ControllerEvent $event): void
    {
        $controller = $event->getController();
        $controller = is_array($controller) ? $controller[0] : $controller;

        // Only handle CController subclasses
        if (!$this->supports($controller)) {
            return;
        }

        $request = $event->getRequest();

        $permission = new CPermission($controller, $event->getRequest());

        if ($this->isRequestPublic($request)) {
            $permission->checkModuleStatus();

            return;
        }

        // Check permission
        $permission->check();
    }

    /**
     * @param $controller
     *
     * @return bool
     */
    private function supports($controller): bool
    {
        return is_subclass_of($controller, CController::class);
    }
}
