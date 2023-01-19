<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Tests\Unit\Import;

use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Sample\Entities\CSampleCasting;
use Ox\Mediboard\Sample\Entities\CSampleCategory;
use Ox\Mediboard\Sample\Import\SampleCategoryImport;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use ReflectionException;

/**
 * Test the importation of CSampleCategory objects.
 */
class SampleCategoryImportTest extends OxUnitTestCase
{
    /** @var string */
    private static $cat_name;


    /**
     * Trying to import a CSampleCateogry from an invalid array (no key 'name') should return false.
     *
     * @throws TestsException|ReflectionException
     */
    public function testImportObjectFromInvalidArray(): void
    {
        $import = new SampleCategoryImport();
        $this->assertFalse($this->invokePrivateMethod($import, 'importObject', []));
    }

    /**
     * The import of a CSampleCategory put that category in a cache in the SampleCategoryImport object.
     *
     * @throws ReflectionException|TestsException
     */
    public function testImportObjectOk(): SampleCategoryImport
    {
        $import = new SampleCategoryImport();

        self::$cat_name = uniqid();

        $this->assertTrue(
            $this->invokePrivateMethod($import, 'importObject', [SampleCategoryImport::NAME_NODE => self::$cat_name,])
        );

        $cache = $this->getPrivateProperty($import, 'objects_cache');
        $this->assertArrayHasKey(self::$cat_name, $cache);

        return $import;
    }

    /**
     * The importation of a CSampleCategory that is already in the cache of the SampleCategoryImport does not create
     * a new CSampleCateogry.
     *
     * @depends testImportObjectOk
     */
    public function testImportObjectAlreadyInCache(SampleCategoryImport $import): void
    {
        $this->assertTrue(
            $this->invokePrivateMethod($import, 'importObject', [SampleCategoryImport::NAME_NODE => self::$cat_name,])
        );

        $cat = new CSampleCategory();
        $cat->name = self::$cat_name;
        $this->assertEquals(1, $cat->countMatchingListEsc());
    }

    /**
     * The extraction of data without a root node should always return an empty array.
     *
     * @throws ReflectionException|TestsException
     */
    public function testExtractDataWithoutRootNode(): void
    {
        $import = $this->getMockBuilder(SampleCategoryImport::class)
            ->onlyMethods(['getFileContent'])
            ->getMock();
        $import->method('getFileContent')->willReturn('{}');

        $this->assertEquals([], $this->invokePrivateMethod($import, 'extractData'));
    }

    /**
     * The extraction of datas return an array by json_decoding the data read from the file.
     *
     * @throws ReflectionException|TestsException
     */
    public function testExtractDataOk(): array
    {
        $cat_array = $this->getSampleCategoriesArray();

        $import = $this->getMockBuilder(SampleCategoryImport::class)
            ->onlyMethods(['getFileContent'])
            ->getMock();
        $import->method('getFileContent')
            ->willReturn(json_encode([SampleCategoryImport::ROOT_NODE => $cat_array]));

        $this->assertEquals($cat_array, $this->invokePrivateMethod($import, 'extractData'));

        return $cat_array;
    }

    /**
     * The import method return the number of imported or already present CSampleCategory.
     *
     * @depends testExtractDataOk
     */
    public function testImport(array $extracted_data): void
    {
        $import = $this->getMockBuilder(SampleCategoryImport::class)
            ->onlyMethods(['extractData'])
            ->getMock();
        $import->method('extractData')->willReturn($extracted_data);

        $this->assertEquals(count($extracted_data), $import->import());
    }

    private function getSampleCategoriesArray(): array
    {
        return [
            [SampleCategoryImport::NAME_NODE => uniqid()],
            [SampleCategoryImport::NAME_NODE => 'Action'],
            [SampleCategoryImport::NAME_NODE => 'Thriller'],
        ];
    }
}
