<?php
/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Mutex;

use Ox\Core\CMbException;
use Ox\Core\Mutex\CMbAPCMutex;
use Ox\Core\Mutex\CMbFileMutex;
use Ox\Core\Mutex\CMbMutex;
use Ox\Core\Mutex\CMbMutexDriver;
use Ox\Core\Mutex\CMbRedisMutex;
use Ox\Tests\OxUnitTestCase;

class CMutexTest extends OxUnitTestCase
{

    public function testConstructFailed()
    {
        global $dPconfig;
        $config_svg                = $dPconfig['mutex_drivers'];
        $dPconfig['mutex_drivers'] = 'toto';

        try {
            new CMbMutex('ipsum');
        } catch (CMbException $exception) {
            $this->assertEquals($exception->getMessage(), 'No mutex driver available');
            $dPconfig['mutex_drivers'] = $config_svg;
        }
    }

    public function testConstructSuccess()
    {
        /* Default mutex in CI is APCU $driver */
        $driver = new CMbMutex('ipsum');
        $this->assertInstanceOf(CMbAPCMutex::class, $driver->getDriver());
    }

    public function testFileMutexExists()
    {
        $driver = new CMbFileMutex('lorem1');
        $driver->acquire(2);
        $this->assertTrue($this->invokePrivateMethod($driver, 'fileExists'));

        return $driver;
    }

    /**
     * @group schedules
     *
     * @dataProvider driverProvider
     *
     * @runInSeparateProcess
     */
    public function testLock($driver_class)
    {
        /** @var CMbMutexDriver $driver */
        $driver = new $driver_class('lorem');
        $this->assertTrue($driver->lock(2));

        $driver_bis = new $driver_class('lorem');
        $this->assertFalse($driver_bis->lock(1));

        sleep(3);
        $this->assertTrue($driver_bis->lock(1));

        $driver->release();
        $driver_bis->release();
    }

    public function driverProvider()
    {
        $drivers = [];
        foreach (CMbMutex::$drivers as $key => $driver_class) {
            $drivers[$key] = [$driver_class];
        }

        return $drivers;
    }

    public function testRedisLock(): CMbRedisMutex
    {
        $key    = uniqid('redis-mutex');
        $driver = new CMbRedisMutex($key);
        $this->assertTrue($driver->lock(3));

        return $driver;
    }

    /**
     * @depends testRedisLock
     * @runInSeparateProcess
     */
    public function testRedisConcurency(CMbRedisMutex $driver)
    {
        $key    = $this->getPrivateProperty($driver, 'key');
        $expire = $this->getPrivateProperty($driver, 'expire');

        $driver_bis = new CMbRedisMutex($key);

        $driver_bis->acquire(10);
        
        $this->assertEqualsWithDelta(microtime(true), $expire, 1.0);

        $driver_bis->release();
    }
}
