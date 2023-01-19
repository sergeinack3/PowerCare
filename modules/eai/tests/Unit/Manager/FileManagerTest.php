<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Tests\Unit\Manager;

use Exception;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\Manager\Exceptions\FileManagerException;
use Ox\Interop\Eai\Manager\FileManager;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Files\CFileTraceability;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\System\CSenderHTTP;
use Ox\Tests\OxUnitTestCase;

class FileManagerTest extends OxUnitTestCase
{
    /** @var string */
    private const REGEX_ISO_DATE = '(\d{4})-(\d{2})-(\d{2})';
    /** @var string */
    private const REGEX_ISO_DATETIME = self::REGEX_ISO_DATE . ' (\d{2}):(\d{2}):(\d{2})';

    /** @var CInteropActor */
    private static $actor;

    public function testExceptionNoContent(): void
    {
        $this->expectException(FileManagerException::class);
        $this->expectExceptionCode(FileManagerException::CONTENT_EMPTY);

        (new FileManager())->store(new CFile());
    }

    public function testExceptionNoTarget(): void
    {
        $this->expectException(FileManagerException::class);
        $this->expectExceptionCode(FileManagerException::NO_TARGET_OBJECT);

        $file = new CFile();
        $file->setContent('content');
        (new FileManager())->store($file);
    }

    public function testExceptionInvalidStoreFile(): void
    {
        $this->expectException(FileManagerException::class);
        $this->expectExceptionCode(FileManagerException::INVALID_STORE_FILE);

        $file = $this->getFileMocked();
        $file->method("store")->willReturn('an error message');

        $file->setContent('content');
        (new FileManager())->store($file);
    }

    public function testExceptionLoadMatchingWithInvalidTarget(): void
    {
        $this->expectException(FileManagerException::class);
        $this->expectExceptionCode(FileManagerException::FILE_CONTEXT_DIVERGENCE);

        $id_400 = uniqid('idex_file_', true);
        $tag    = 'testExceptionLoadMatchingWithInvalidTarget';
        $file   = $this->getNewFile();
        $idex   = CIdSante400::getMatch($file->_class, $tag, $id_400, $file->_id);
        $idex->store();

        $file->_id          = null;
        $file->object_class = 'other_class';
        $file->setContent('new content file');

        (new FileManager())
            ->enableLoadMatching($id_400)
            ->setTag($tag)
            ->store($file);
    }

    public function testFileWithIdAndLoadMatching(): void
    {
        $file_fixture = $this->getNewFile();

        $file      = $this->getFileMocked();
        $file->_id = 1;
        $file->setContent("content");
        $file->file_name = $file_fixture->file_name;
        $file->file_type = $file_fixture->file_type;

        $stored_file = (new FileManager())
            ->enableLoadMatching(true)
            ->store($file);
        $this->assertSame($file, $stored_file);

        $stored_file = (new FileManager())
            ->enableLoadMatching('idex_sante')
            ->store($file);
        $this->assertSame($file, $stored_file);
    }

    public function testStoreFileWithMatchingIdex(): void
    {
        $id_400 = uniqid('idex_file_', true);
        $tag    = 'testStoreFileWithMatchingIdex';
        $file   = $this->getNewFile();
        $idex   = CIdSante400::getMatch($file->_class, $tag, $id_400, $file->_id);
        $idex->store();

        $file->_id = null;
        $file->setContent('new content file');

        $stored_file = (new FileManager())
            ->enableLoadMatching($id_400)
            ->setTag($tag)
            ->store($file);

        $this->assertSame($stored_file, $file);
    }

    public function testStoreFileWithNonMatchingIdex(): void
    {
        $id_400                   = uniqid('idex_file_', true);
        $tag                      = 'testStoreFileWithMatchingIdex';
        $file                     = $this->getNewFile();
        $file->_id                = null;
        $file->file_real_filename = null;
        $file->setContent('new content file');

        $stored_file = (new FileManager())
            ->enableLoadMatching($id_400)
            ->setTag($tag)
            ->store($file);

        $idex = CIdSante400::getMatch($stored_file->_class, $tag, $id_400, $stored_file->_id);

        $this->assertNotNull($stored_file->_id);
        $this->assertNotNull($idex->_id);
    }

    public function testStoreFileWithLoadMatching(): void
    {
        $file                     = $this->getNewFile();
        $file_id_expected         = $file->_id;
        $file->_id                = null;
        $file->file_real_filename = null;
        $file->setContent('new content file');

        $stored_file = (new FileManager())
            ->enableLoadMatching(true)
            ->store($file);

        $this->assertNotNull($stored_file->_id);
        $this->assertEquals($file_id_expected, $stored_file->_id);
        $this->assertSame($file, $stored_file);
    }


    /**
     * @return CFile
     * @throws Exception
     */
    private function getNewFile(bool $store = true): CFile
    {
        $file            = new CFile();
        $file->file_type = "text/plain";
        $file->file_name = uniqid('testFileWithIdAndLoadMatching') . 'txt';
        $file->setContent('content text');
        $file->setObject(CMediusers::get());
        $file->fillFields();
        $file->updateFormFields();
        if ($store) {
            if ($msg = $file->store()) {
                throw new Exception($msg);
            }
        }

        return $file;
    }

    /**
     * @return string[][]
     */
    public function providerGenerateFilename(): array
    {
        $filename           = uniqid("file_", true) . ".txt";
        $file_category      = new CFilesCategory();
        $file_category->_id = 1;
        $file_category->nom = $category_name = "category";

        return [
            'filename - ' . FileManager::STRATEGY_FILENAME_DATE               => [
                FileManager::STRATEGY_FILENAME_DATE,
                "/^" . self::REGEX_ISO_DATETIME . "_$filename$/",
                $filename,
            ],
            'filename - ' . FileManager::STRATEGY_FILENAME_DATE_CATEGORY      => [
                FileManager::STRATEGY_FILENAME_DATE_CATEGORY,
                "/^" . self::REGEX_ISO_DATETIME . "_{$category_name}_$filename$/",
                $filename,
                $file_category,
            ],
            'filename - ' . FileManager::STRATEGY_FILENAME_TIMESTAMP_CATEGORY => [
                FileManager::STRATEGY_FILENAME_TIMESTAMP_CATEGORY,
                "/^" . self::REGEX_ISO_DATETIME . "_{$category_name}_[0-9]{13}_$filename$/",
                $filename,
                $file_category,
            ],
            'filename - ' . FileManager::STRATEGY_FILENAME_DEFAULT            => [
                FileManager::STRATEGY_FILENAME_DEFAULT,
                "/$filename/",
                $filename,
            ],
        ];
    }

    /**
     * @dataProvider providerGenerateFilename
     *
     * @param string $strategy
     * @param string $pattern
     * @param string $filename
     *
     * @return void
     * @throws FileManagerException
     */
    public function testGenerateFilename(
        string $strategy,
        string $pattern,
        string $filename,
        ?CFilesCategory $category = null
    ): void {
        $file = $this->getFileMocked();
        $file->setContent("content");
        $file->file_name = $filename;
        $stored_file     = (new FileManager($strategy))
            ->enableLoadMatching(true)
            ->setCategory($category)
            ->store($file);

        $this->assertMatchesRegularExpression($pattern, $stored_file->file_name);
    }

    public function testFileWithTraceabilityAsTarget(): void
    {
        $file = $this->getFileMocked();
        $file->object_id = $file->object_class = null;
        $file->setContent("content");

        $file_manager = (new FileManager())
            ->enableTraceability($this->getTraceability());
        $stored_file = $file_manager->store($file);

        $file_traceability = $file_manager->getTraceability();
        $this->assertNotNull($file_traceability->_id);
        $this->assertEquals("pending", $file_traceability->status);
        $this->assertEquals($file_traceability->_class, $stored_file->object_class);
        $this->assertEquals($file_traceability->_id, $stored_file->object_id);
        $this->assertEquals($stored_file->_class, $file_traceability->object_class);
        $this->assertEquals($stored_file->_id, $file_traceability->object_id);
    }

    public function testFileWithTraceabilityAndTarget(): void
    {
        $file = $this->getNewFile();
        $file->setContent("content");

        $file_manager = (new FileManager())
            ->enableTraceability($this->getTraceability());
        $stored_file = $file_manager->store($file);

        $file_traceability = $file_manager->getTraceability();
        $this->assertNotNull($file_traceability->_id);
        $this->assertEquals("auto", $file_traceability->status);
        $this->assertEquals($stored_file->_class, $file_traceability->object_class);
        $this->assertEquals($stored_file->_id, $file_traceability->object_id);
    }


    /**
     * @return CFile
     * @throws Exception
     */
    private function getFileMocked(): CFile
    {
        $file               = ($this->getMockBuilder(CFile::class))
            ->disableOriginalConstructor()
            ->onlyMethods(['store', 'updateFormFields'])
            ->getMock();
        $file->object_class = 'CMediusers';
        $file->object_id    = CMediusers::get()->_id;

        return $file;
    }

    public function testFileWithTypeDmp(): void
    {
        $file = $this->getFileMocked();
        $file->setContent("content");

        $stored_file = (new FileManager())
            ->setTypeDmp('SYNTH')
            ->store($file);

       $this->assertEquals("1.2.250.1.213.1.1.4.12^SYNTH", $stored_file->type_doc_dmp);
    }

    private function getTraceability(): CFileTraceability
    {
        $traceability = new CFileTraceability();
        $traceability->setActor($this->getActor());
        $traceability->group_id = CGroups::loadCurrent()->_id;
        $traceability->user_id = CMediusers::get()->_id;
        $traceability->status = 'pending';

        return $traceability;
    }

    private function getActor(): CInteropActor
    {
        if (!$actor = self::$actor) {
            $actor = CSenderHTTP::getSampleObject();
            $actor->group_id = CGroups::loadCurrent()->_id;
            if ($msg = $actor->store()) {
                throw new Exception($msg);
            }
        }

        return self::$actor = $actor;
    }
}
