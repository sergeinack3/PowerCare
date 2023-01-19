<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Tests\Unit\Import;

use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Sample\Entities\CSampleNationality;
use Ox\Mediboard\Sample\Import\SampleNationalityImport;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use ReflectionException;

/**
 * Test the importation of CSampleNationality objects.
 */
class SampleNationalityImportTest extends OxUnitTestCase
{
    /** @var string */
    private static $nationality_name;

    /** @var string */
    private static $nationality_code;

    /**
     * Trying to import a CSampleNationality from an invalid or empty array return false.
     *
     * @throws TestsException|ReflectionException
     */
    public function testImportObjectFromInvalidArray(): void
    {
        $import = new SampleNationalityImport();
        $this->assertFalse($this->invokePrivateMethod($import, 'importObject', []));
    }

    /**
     * Importation of a CSampleNationality using the array provided.
     * After the importation the CSampleNationality is put in a cache of the SampleNationalityImport object to
     * avoid imporing it twice.
     *
     * @throws ReflectionException|TestsException
     */
    public function testImportObjectOk(): SampleNationalityImport
    {
        self::$nationality_name = uniqid();
        self::$nationality_code = substr(self::$nationality_name, strlen(self::$nationality_name) - 5, 5);

        $import = new SampleNationalityImport();
        $this->assertTrue(
            $this->invokePrivateMethod(
                $import,
                'importObject',
                [
                    SampleNationalityImport::NAME_NODE => self::$nationality_name,
                    'code'                             => self::$nationality_code,
                ]
            )
        );

        $cache = $this->getPrivateProperty($import, 'objects_cache');
        $this->assertArrayHasKey(self::$nationality_name, $cache);

        return $import;
    }

    /**
     * Assert that the importation of an already imported CSampleNationality return true but does not return a new one.
     *
     * @depends testImportObjectOk
     */
    public function testImportObjectFromCache(SampleNationalityImport $import): void
    {
        $this->assertTrue(
            $this->invokePrivateMethod(
                $import,
                'importObject',
                [
                    SampleNationalityImport::NAME_NODE => self::$nationality_name,
                    'code'                             => self::$nationality_code,
                ]
            )
        );

        $nationality = new CSampleNationality();
        $nationality->code = self::$nationality_code;
        $this->assertEquals(1, $nationality->countMatchingListEsc());
    }
}
