<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Kernel\Event;

use Exception;
use Ox\Cli\Console\IAppDependantCommand;
use Ox\Core\CApp;
use Ox\Core\Kernel\Routing\RequestHelperTrait;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AppListener implements EventSubscriberInterface
{
    use RequestHelperTrait;

    private CApp                  $app;
    private ContainerBagInterface $params;

    public function __construct(ContainerBagInterface $params)
    {
        $this->app    = CApp::getInstance();
        $this->params = $params;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST   => [
                ['onRequest', 100],
                //                ['onRouting', 31], // SF Route listener is 32
            ],
            KernelEvents::RESPONSE  => ['onResponse', 100],
            KernelEvents::TERMINATE => ['onTerminate', 90],
            ConsoleEvents::COMMAND  => ['onCommand', 100],
        ];
    }

    public function onCommand(ConsoleCommandEvent $command_event)
    {
        $cmd = $command_event->getCommand();

        if ($cmd instanceof IAppDependantCommand) {
            $this->app->startForCli($cmd->getName());
        }
    }

    /**
     * @param RequestEvent $event
     *
     * @return void
     * @throws Exception
     */
    public function onRequest(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        // ignore routes _wdt _profiler _error
        if (!$this->isRequestApiOrGui($event->getRequest())) {
            return;
        }

        if ($this->app->isStarted()) {
            return;
        }

        $request = $event->getRequest();
        $return  = $this->app->startForRequest($request);

        if ($return instanceof Response) {
            $event->setResponse($return);
        }
    }


    /**
     * @param RequestEvent $event
     *
     * @throws Exception
     */
    public function onRouting(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $this->app->setPublic($request);
    }

    /**
     * @param ResponseEvent $event
     *
     * @return void
     * @throws Exception
     */
    public function onResponse(ResponseEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->app->isStarted()) {
            $event->stopPropagation();

            return;
        }

        $request = $event->getRequest();
        $this->app->stop($request);
    }

    /**
     * @param TerminateEvent $event
     *
     * @throws Exception
     */
    public function onTerminate(TerminateEvent $event)
    {
        if (!$this->app->isStarted()) {
            $event->stopPropagation();

            return;
        }

        if ($event->getRequest()->server->has('OX_REQUEST_NO_LOG')
            && $this->params->get('kernel.environment') === 'test') {
            return;
        }

        $request = $event->getRequest();
        $this->app->terminate($request);
    }


    public function getApp(): CApp
    {
        return $this->app;
    }
}
