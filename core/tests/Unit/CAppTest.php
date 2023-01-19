<?php
/**
 * @package Core\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Tests\OxUnitTestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CAppTest
 * @package Ox\Core\Tests\Unit
 */
class CAppTest extends OxUnitTestCase
{
    /**
     * Expected order of shutdown callbacks
     * - Apps at first, in order of registering
     * - AUTOLOAD
     * - EVENT
     * - MUTEX
     * - PEACE
     * - ERROR
     */
    public function testRegisterShutdown()
    {
        $expected_order = [
            [static::class, 'app1Shutdown'],
            [static::class, 'app2Shutdown'],
            [static::class, 'app3Shutdown'],
            [static::class, 'autoloadShutdown'],
            [static::class, 'eventShutdown'],
            [static::class, 'mutexShutdown'],
            [static::class, 'sessionShutdown'],
            [static::class, 'peaceShutdown'],
            [static::class, 'errorShutdown'],
            [static::class, 'cronShutdown'],
        ];

        // Random call order, but should conserve the correct sorting thanks to priorities
        CApp::registerShutdown([static::class, 'errorShutdown'], CApp::ERROR_PRIORITY);
        CApp::registerShutdown([static::class, 'app1Shutdown'], CApp::APP_PRIORITY);
        CApp::registerShutdown([static::class, 'mutexShutdown'], CApp::MUTEX_PRIORITY);
        CApp::registerShutdown([static::class, 'cronShutdown'], CApp::CRON_PRIORITY);
        CApp::registerShutdown([static::class, 'app2Shutdown'], CApp::APP_PRIORITY);
        CApp::registerShutdown([static::class, 'peaceShutdown'], CApp::PEACE_PRIORITY);
        CApp::registerShutdown([static::class, 'sessionShutdown'], CApp::SESSION_PRIORITY);
        CApp::registerShutdown([static::class, 'app3Shutdown'], CApp::APP_PRIORITY);
        CApp::registerShutdown([static::class, 'autoloadShutdown'], CApp::AUTOLOAD_PRIORITY);
        CApp::registerShutdown([static::class, 'eventShutdown'], CApp::EVENT_PRIORITY);

        $callbacks_order = [];
        foreach (CApp::getShutdownCallbacks() as $_callback) {
            $callbacks_order[] = $_callback;
        }

        // Asserting the priorities are what we expected
        $this->assertEquals($expected_order, $callbacks_order);
    }

    public function testSingelton()
    {
        $instance = CApp::getInstance();
        $this->assertInstanceOf(CApp::class, $instance);
        $this->assertDirectoryExists($this->invokePrivateMethod($instance, 'getRootDir'));
        $this->assertSame($instance, CApp::getInstance());
    }

    public function testStart()
    {
        $instance  = CApp::getInstance();
        $reflexion = new ReflectionClass($instance);
        $prop      = $reflexion->getProperty('is_started');
        $prop->setAccessible(true);
        $prop->setValue($instance, true);
        $this->expectExceptionMessage('The app is already started');
        $instance->startForRequest(new Request());
        $prop->setValue($instance, false);
    }

    public function testStop()
    {
        $instance  = CApp::getInstance();
        $reflexion = new ReflectionClass($instance);
        $prop      = $reflexion->getProperty('is_started');
        // restore default value
        $prop->setAccessible(true);
        $prop->setValue($instance, false);

        $req = new Request();
        $this->expectExceptionMessage('The app is not started');
        $instance->stop($req);
    }

    public function testTerminated()
    {
        $instance = CApp::getInstance();
        $req      = new Request();
        $this->expectExceptionMessage('The app is not start&stop correctly.');
        $instance->terminate($req);
    }

    public function testRipLoop(): void
    {
        CApp::setInRip(true);

        $this->expectExceptionObject(new Exception(CAppUI::tr('CApp-Error-Rip-as-already-been-called')));
        CApp::rip();
    }

    ///////////////////////////////////////////////
    /// Functions used for testRegisterShutdown ///
    //////////////////////////////////////////////

    public static function errorShutdown()
    {
    }

    public static function mutexShutdown()
    {
    }

    public static function sessionShutdown()
    {
    }

    public static function autoloadShutdown()
    {
    }

    public static function eventShutdown()
    {
    }

    public static function peaceShutdown()
    {
    }

    public static function app1Shutdown()
    {
    }

    public static function app2Shutdown()
    {
    }

    public static function app3Shutdown()
    {
    }

    public static function cronShutdown()
    {
    }
}
