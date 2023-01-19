<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api;

use Ox\Core\Kernel\Event\AppListener;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernel;

abstract class UnitTestRequest extends OxUnitTestCase
{
    /** @var array */
    protected $listeners = [];

    protected function makeKernel(): HttpKernel
    {
        $dispatcher = new EventDispatcher();

        foreach ($this->listeners as $_listener) {
            $dispatcher->addSubscriber($_listener);
        }

        $resolver     = new ControllerResolver();
        $requestStack = new RequestStack();

        return new HttpKernel($dispatcher, $resolver, $requestStack);
    }

    protected function addListener(EventSubscriberInterface $listener)
    {
        // Do not use AppListener for test because it is incompatible with the current bootstrap file.
        if (!$listener instanceof AppListener) {
            $this->listeners[] = $listener;
        }
    }

    protected function handleRequest($request)
    {
        $kernel = $this->makeKernel();

        return $kernel->handle($request);
    }
}
