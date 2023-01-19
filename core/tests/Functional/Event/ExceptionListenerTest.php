<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Functional\Event;

use Exception;
use Monolog\Logger;
use Ox\Core\Kernel\Event\ExceptionListener;
use Ox\Tests\OxWebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ExceptionListenerTest extends OxWebTestCase
{
    public function testExceptionListener()
    {
        $kernel = static::createKernel();

        $container = static::getContainer();

        $subscriber = new ExceptionListener(
            $container->get('twig'),
            new Logger('test'),
            $container
        );

        // gui
        $req   = Request::create('/gui/lorem/ipsum');
        $event = new ExceptionEvent($kernel, $req, HttpKernelInterface::MAIN_REQUEST, new Exception('ipsum'));
        $subscriber->onException($event);
        $this->assertTrue($event->hasResponse());
        $this->assertInstanceOf(Response::class, $event->getResponse());

        // api
        $req   = Request::create('/api/foo/bar');
        $event = new ExceptionEvent($kernel, $req, HttpKernelInterface::MAIN_REQUEST, new Exception('ipsum'));
        $subscriber->onException($event);
        $this->assertTrue($event->hasResponse());
        $this->assertInstanceOf(JsonResponse::class, $event->getResponse());
    }
}
