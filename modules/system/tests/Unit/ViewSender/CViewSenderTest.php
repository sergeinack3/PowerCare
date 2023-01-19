<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Unit\ViewSender;

use Ox\Core\CMbDT;
use Ox\Mediboard\System\ViewSender\CViewSender;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class CViewSenderTest extends OxUnitTestCase
{
    private $files = [];

    public function tearDown(): void
    {
        parent::tearDown();

        foreach ($this->files as $_file_path) {
            if (file_exists($_file_path)) {
                unlink($_file_path);
            }
        }
    }

    public function testCanDeleteFile(): void
    {
        // Cannot use data provider because of time check
        $datas = [
            [CMbDT::dateTime(), '1234abcd', '1234abcd', true],
            [CMbDT::dateTime('-1 HOUR'), uniqid(), uniqid(), true],
            [CMbDT::dateTime('-1 HOUR'), '1234abcd', '1234abcd', true],
            [CMbDT::dateTime(), uniqid(), uniqid(), false],
        ];

        foreach ($datas as [$file_date, $unique, $static_unique, $expected]) {
            $sender = new CViewSender();
            $sender->setForceUniqueId($static_unique);
            $this->assertEquals($expected, $sender->canDeleteFile($file_date, $unique));
        }
    }

    public function testAddTempFileDoesNotExists(): void
    {
        $sender = new CViewSender();
        $this->invokePrivateMethod($sender, 'addTempFile', 'file_path');

        $files = ($sender->getRemainingFilesCache())->get();
        
        $this->assertNotEmpty($files);
        $this->assertArrayHasKey('file_path', $files);
        $this->assertEquals($sender->getUniqueId(), $files['file_path'][1]);
    }

    public function testAddTempFileReplace(): void
    {
        $sender = new CViewSender();
        $this->invokePrivateMethod($sender, 'addTempFile', 'file_path');

        $new_unique = uniqid();
        $sender->setForceUniqueId($new_unique);
        $this->invokePrivateMethod($sender, 'addTempFile', 'file_path');
        
        $files = ($sender->getRemainingFilesCache())->get();
        $this->assertEquals($new_unique, $files['file_path'][1]);
    }

    public function testClearRemainingFiles(): void
    {
        $sender = new CViewSender();
        $temp_dir = dirname(__DIR__, 5) . '/tmp';

        $this->files[] = $still_existing = tempnam($temp_dir, 'test');
        $this->files[] = $test1 = tempnam($temp_dir, 'test');
        $this->files[] = $test2 = tempnam($temp_dir, 'test');
        $non_exists = uniqid();

        $this->assertFileExists($still_existing);
        $this->assertFileExists($test1);
        $this->assertFileExists($test2);
        $this->assertFileDoesNotExist($non_exists);

        $this->invokePrivateMethod($sender, 'addTempFile', $still_existing);

        $sender->setForceUniqueId(uniqid());

        $this->invokePrivateMethod($sender, 'addTempFile', $test1);
        $this->invokePrivateMethod($sender, 'addTempFile', $test2);
        $this->invokePrivateMethod($sender, 'addTempFile', $non_exists);

        CViewSender::clearRemainingFiles();

        $this->assertFileDoesNotExist($test1);
        $this->assertFileDoesNotExist($test2);
        $files = ($sender->getRemainingFilesCache())->get();

        $this->assertArrayNotHasKey($test1, $files);
        $this->assertArrayNotHasKey($test2, $files);
        $this->assertArrayNotHasKey($non_exists, $files);
        $this->assertArrayHasKey($still_existing, $files);
        $this->assertFileExists($still_existing);
    }
}
