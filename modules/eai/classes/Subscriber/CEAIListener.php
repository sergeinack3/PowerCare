<?php

namespace Ox\Interop\Eai\Subscriber;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\Kernel\Routing\RequestHelperTrait;
use Ox\Interop\Eai\Controllers\CEAIController;
use Ox\Mediboard\Admin\CUser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CEAIListener implements EventSubscriberInterface, IShortNameAutoloadable
{
    use RequestHelperTrait;

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => [
                ['authenticateSender'],
            ],
        ];
    }

    /**
     * @param ControllerEvent $event
     *
     * @throws Exception
     */
    public function authenticateSender(ControllerEvent $event): void
    {
        if (!$this->supports($event)) {
            return;
        }

        CEAIController::authenticateSender($event->getRequest(), CUser::get());
    }

    private function supports(KernelEvent $event): bool
    {
        return $this->getController($event->getRequest()) instanceof CEAIController;
    }
}
