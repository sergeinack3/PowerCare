<?php

/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers\Tests\Unit\Controllers\Legacy;

use DirectoryIterator;
use Ox\Core\CMbException;
use Ox\Core\CMbPath;
use Ox\Mediboard\Mediusers\CMediusersXmlImportManager;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class CMediusersXmlImportManagerTest extends OxUnitTestCase
{
    /**
     * @dataProvider getMimeTypeOkProvider
     */
    public function testGetMimeTypeOk(string $filename, string $expected_mime): void
    {
        $manager = new CMediusersXmlImportManager();
        $this->assertEquals($expected_mime, $this->invokePrivateMethod($manager, 'getMimeType', $filename));
    }

    public function testGetMimeTypeFileDoesNotExists(): void
    {
        $manager  = new CMediusersXmlImportManager();
        $filename = uniqid();
        // No translations
        $this->expectExceptionMessage('CFile-not-exists');
        $this->invokePrivateMethod($manager, 'getMimeType', $filename);
    }

    public function testGetMimeTypeNotAllowedMimeType(): void
    {
        $manager  = new CMediusersXmlImportManager();
        $filename = dirname(__DIR__) . '/Resources/mediuser_bad_file.xml';
        $this->expectExceptionMessage('CMediusersImportLegacyController-Error-file must be in list. Type provided');
        $this->invokePrivateMethod($manager, 'getMimeType', $filename);
    }

    public function testInitUploadPath(): void
    {
        $manager = new CMediusersXmlImportManager();
        $this->assertEquals(dirname(__DIR__, 4) . '/tmp/import_mediusers', $manager->getUploadPath());
    }

    public function testUnzipFilesOk(): void
    {
        $mock = $this->getMockBuilder(CMediusersXmlImportManager::class)
            ->onlyMethods(['moveUploadedFile'])
            ->getMock();

        $upload_dir = $mock->getUploadPath();
        $this->copyFileToUploadDir($upload_dir);

        $this->assertEquals(
            $mock->getUploadPath(),
            $this->invokePrivateMethod($mock, 'unzipFiles', 'tmp_name', 'mediusers_zip_file.zip')
        );

        CMbPath::emptyDir($upload_dir);
    }

    public function testIsValidFile(): void
    {
        $manager   = new CMediusersXmlImportManager();

        $it = new DirectoryIterator(dirname(__DIR__) . '/Resources/isValidFile');

        foreach ($it as $file_info) {
            $this->assertEquals(($file_info->getExtension() === 'xml'), $this->invokePrivateMethod($manager, 'isValidFile', $file_info));
        }
    }

    public function testImportMediusersWithException(): void
    {
        $mock = $this->getMockBuilder(CMediusersXmlImportManager::class)
            ->onlyMethods(['moveUploadedFile', 'importFile', 'createDir'])
            ->getMock();

        $mock->method('importFile')->willThrowException(new CMbException('Import error'));

        $this->copyFileToUploadDir($mock->getUploadPath());

        $mock->importMediusers([], dirname(__DIR__) . '/Resources/mediusers_zip_file.zip', 'mediusers_zip_file.zip');

        $this->assertEquals([['Import error', UI_MSG_WARNING]], $mock->getErrors());
        $this->assertTrue(CMbPath::isEmptyDir($mock->getUploadPath()));
    }

    public function getMimeTypeOkProvider(): array
    {
        return [
            'xml' => [dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Resources/mediusers_xml_file.xml', 'text/xml'],
            'zip' => [dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Resources/mediusers_zip_file.zip', 'application/zip'],
        ];
    }

    private function copyFileToUploadDir(string $upload_dir): void
    {
        if (!is_dir($upload_dir)) {
            CMbPath::forceDir($upload_dir);
        }

        copy(dirname(__DIR__) . '/Resources/mediusers_zip_file.zip', $upload_dir . '/mediusers_zip_file.zip');
    }
}
