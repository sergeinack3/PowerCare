<?php

/**
 * @package Mediboard\Files\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files\Tests\Unit;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFileUserView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CSourceIdentite;
use Ox\Mediboard\Patients\Tests\Fixtures\SimplePatientFixtures;
use Ox\Mediboard\System\CObjectEncryption;
use Ox\Tests\OxUnitTestCase;

/**
 * Class CFileTest
 */
class CFileTest extends OxUnitTestCase
{
    /** @var array */
    private static $files_path = [];

    /** @var CFile */
    private $file;

    /**
     * Remove the created files
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        foreach (static::$files_path as $_path) {
            @unlink($_path);
        }
    }

    /**
     * Prepare $this->file
     */
    public function setUp(): void
    {
        parent::setUp();

        $uniqid = uniqid('', true);

        $this->file               = new CFile();
        $this->file->file_name    = "{$uniqid}.txt";
        $this->file->file_type    = 'text/plain';
        $this->file->object_class = 'CUser';
        $this->file->object_id    = 1;
    }

    public function test__construct(): void
    {
        $file = new CFile();
        $this->assertInstanceOf(CFile::class, $file);
    }

    public function testLoadRefAuthor(): void
    {
        $file = new CFile();
        $this->assertInstanceOf(CMediusers::class, $file->loadRefAuthor());
    }

    public function testLoadNames(): void
    {
        $file = CFile::loadNamed(CMediusers::get(), "file");
        $this->assertInstanceOf(CFile::class, $file);
    }

    public function testCanCreate(): void
    {
        $can_create = CFile::canCreate(CMediusers::get());
        $this->assertIsBool($can_create);
    }

    public function testFillFields(): void
    {
        $file = new CFile();
        $file->fillFields();

        $this->assertEquals(CMbDT::dateTime(), $file->file_date);
        $this->assertIsString($file->file_real_filename);
    }

    public function testLoadRefReadStatus(): void
    {
        $file = new CFile();
        $this->assertInstanceOf(CFileUserView::class, $file->loadRefReadStatus());
    }

    public function testGetThumbnailDataURI(): void
    {
        $file             = new CFile();
        $file->_id        = 1;
        $file->_file_path = CAppUI::conf("root_dir") . "/modules/printing/samples/test_page.pdf";
        $this->assertStringContainsString("base64", $file->getThumbnailDataURI());
    }

    public function testIsImage(): void
    {
        $file            = new CFile();
        $file->file_type = "image/png";
        $this->assertTrue($file->isImage());
    }

    public function testConvertTifPagesToPDF(): void
    {
        $file_name = 'loremipsum.tiff';
        $file_tmp  = __DIR__ . '/../Resources/' . $file_name;

        $content = CFile::convertTifPagesToPDF([$file_tmp]);

        $tmp_file = __DIR__ . '/../../../../tmp/CFileTest_testConvertTifPagesToPDF.pdf';

        if (file_exists($tmp_file)) {
            unlink($tmp_file);
        }

        file_put_contents($tmp_file, $content);
        $this->assertFileExists($tmp_file);
    }

    /**
     * Store a file with FS write and DB store success
     */
    public function testStoreFileWithContentOkAndSpecOk(): void
    {
        $this->file->fillFields();
        $this->file->setContent('test');

        $msg = $this->file->store();

        $this->assertNull($msg);
        $this->assertFileExists($this->file->_file_path);
        $this->assertEquals('test', file_get_contents($this->file->_file_path));

        static::$files_path[] = $this->file->_file_path;
    }

    /**
     * Fail to store a file with content ok but spec KO
     */
    public function testStoreFileWithContentOkAndSpecKo(): void
    {
        // Field should be invalidated by spec
        $this->file->file_date = 'notADate';

        $this->file->fillFields();
        $this->file->setContent('test');

        $msg = $this->file->store();

        $this->assertNotNull($msg);
        $this->assertFileDoesNotExist($this->file->_file_path);
    }

    /**
     * Fail to store a file with no content but spec Ok
     */
    public function testStoreFileWithContentKoAndSpecOk(): void
    {
        $this->file->fillFields();
        $this->file->setContent(null);
        $this->file->updateFormFields();

        $msg = $this->file->store();

        $this->assertNotNull($msg);
        $this->assertFileDoesNotExist($this->file->_file_path);
    }

    /**
     * Fail to store a file with no content and spec Ko
     */
    public function testStoreFileWithContentKoAndSpecKo(): void
    {
        $this->file->fillFields();
        $this->file->setContent(null);

        // Field should be invalidated by spec
        $this->file->file_date = 'notADate';
        $this->file->updateFormFields();

        $msg = $this->file->store();

        $this->assertNotNull($msg);
        $this->assertFileDoesNotExist($this->file->_file_path);
    }

    /**
     * Update the content of a file with success
     */
    public function testUpdateFileWithContentOkAndSpecOk(): void
    {
        $this->file->fillFields();
        $this->file->setContent('test');
        $msg = $this->file->store();

        if (!$this->file->_id) {
            $this->fail($msg);
        }

        $this->file->setContent('modified');
        $msg = $this->file->store();

        $this->assertNull($msg);
        $this->assertFileExists($this->file->_file_path);
        $this->assertEquals('modified', file_get_contents($this->file->_file_path));

        static::$files_path[] = $this->file->_file_path;
    }

    /**
     * Fail to update the content of a file, spec Ko
     */
    public function testUpdateFileWithContentOkAndSpecKo(): void
    {
        $this->file->fillFields();
        $this->file->setContent('test');
        $msg = $this->file->store();

        if (!$this->file->_id) {
            $this->fail($msg);
        }

        $this->file->file_date = 'notADate';
        $this->file->setContent('modified');
        $msg = $this->file->store();

        $this->assertNotNull($msg);
        $this->assertEquals('test', file_get_contents($this->file->_file_path));

        static::$files_path[] = $this->file->_file_path;
    }

    /**
     * Move a file that exists
     */
    public function testMoveFileExistsAndSpecOk(): void
    {
        $tmp_path = $this->prepareTmpFile('test');

        $this->file->fillFields();
        $this->file->setMoveFrom($tmp_path);
        $msg = $this->file->store();

        $this->assertNull($msg);
        $this->assertFileExists($this->file->_file_path);
        $this->assertEquals('test', file_get_contents($this->file->_file_path));
        $this->assertFileDoesNotExist($tmp_path);

        static::$files_path[] = $this->file->_file_path;
    }

    /**
     * Fail to move a file due to specs
     */
    public function testMoveFileExistsAndSpecKo(): void
    {
        $tmp_path = $this->prepareTmpFile('test');

        $this->file->fillFields();
        $this->file->file_date = 'notADate';

        $this->file->setMoveFrom($tmp_path);
        $msg = $this->file->store();
        $this->file->updateFormFields();

        $this->assertNotNull($msg);
        $this->assertFileDoesNotExist($this->file->_file_path);
        $this->assertFileExists($tmp_path);

        static::$files_path[] = $tmp_path;
    }

    /**
     * Fail to move a file that does not exists
     */
    public function testMoveFileNotExists(): void
    {
        $tmp_path = uniqid() . '.txt';

        $this->file->fillFields();
        $this->file->setMoveFrom($tmp_path);
        $msg = $this->file->store();
        $this->file->updateFormFields();

        $this->assertEquals('CFile-error-No file to move', $msg);
        $this->assertFileDoesNotExist($this->file->_file_path);
    }

    /**
     * Copy a file that exists
     */
    public function testCopyFileExistsAndSpecOk(): void
    {
        $tmp_path = $this->prepareTmpFile('test');

        $this->file->fillFields();
        $this->file->setCopyFrom($tmp_path);
        $msg = $this->file->store();

        $this->assertNull($msg);
        $this->assertFileExists($this->file->_file_path);
        $this->assertEquals('test', file_get_contents($this->file->_file_path));
        $this->assertFileExists($tmp_path);

        static::$files_path[] = $this->file->_file_path;
        static::$files_path[] = $tmp_path;
    }

    /**
     * Fail to copy a file that does not exists
     */
    public function testCopyFileNotExists(): void
    {
        $tmp_path = uniqid() . '.txt';

        $this->file->fillFields();
        $this->file->setCopyFrom($tmp_path);
        $msg = $this->file->store();
        $this->file->updateFormFields();

        $this->assertEquals('CFile-error-No file to move', $msg);
        $this->assertFileDoesNotExist($this->file->_file_path);
    }

    /**
     * Fail to copy a file with spec Ko
     */
    public function testCopyFileExistsAndSpecKo(): void
    {
        $tmp_path = $this->prepareTmpFile('test');

        $this->file->fillFields();
        $this->file->file_date = 'notADate';

        $this->file->setCopyFrom($tmp_path);
        $msg = $this->file->store();
        $this->file->updateFormFields();

        $this->assertNotNull($msg);
        $this->assertFileDoesNotExist($this->file->_file_path);
        $this->assertFileExists($tmp_path);

        static::$files_path[] = $tmp_path;
    }

    /**
     * @param string $file_name
     * @param string $result
     *
     * @dataProvider sanitizeFileRealFilenameProvider
     */
    public function testSanitizeFileRealFilename(string $file_name, string $expected_result): void
    {
        $file                     = new CFile();
        $file->file_real_filename = $file_name;

        $this->invokePrivateMethod($file, 'sanitizeFileRealFilename');

        $this->assertEquals($expected_result, $file->file_real_filename);
    }

    public function sanitizeFileRealFilenameProvider(): array
    {
        return [
            'file_name_ok'              => ['aa5d6g47tr8g4rgdf5g6', 'aa5d6g47tr8g4rgdf5g6'],
            'file_name_with_dot'        => ['./aa5d6g47tr8g..4rgdf5g6', 'aa5d6g47tr8g4rgdf5g6'],
            'file_name_with_other_char' => ['a/a5\d6\\g|4`7tr_8g..4"#r~gdf5g6', 'aa5d6g47tr8g4rgdf5g6'],
            'file_name_with_space'      => ['aa5d6g47      tr8g 4rgdf5g6', 'aa5d6g47tr8g4rgdf5g6'],
        ];
    }

    /**
     * Create a temp file
     *
     * @param string $content Content to put in the file
     *
     * @return false|string
     */
    protected function prepareTmpFile(string $content = 'test')
    {
        $tmp = tempnam('', 'tu');
        file_put_contents($tmp, $content);

        return $tmp;
    }
}
