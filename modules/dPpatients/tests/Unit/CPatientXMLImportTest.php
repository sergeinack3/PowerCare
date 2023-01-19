<?php
/**
 * @package Mediboard\Patients\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Unit;

use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Mediboard\Patients\CPatientXMLImport;
use Ox\Tests\OxUnitTestCase;

class CPatientXMLImportTest extends OxUnitTestCase
{
    protected $import;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();
        $route        = CAppUI::conf('root_dir');
        $file         = $route . '/modules/dPpatients/tests/Resources/import_xml_test.xml';
        $this->import = new CPatientXMLImport($file);
    }

    /**
     * @config [CConfiguration] importTools import import_tag 0
     * @config dPpatients import_tag 0
     */
    public function testGetImportTagDefault()
    {
        $this->assertEquals('migration', $this->import->getImportTag());
    }

    /**
     * @config [CConfiguration] importTools import import_tag 0
     * @config dPpatients import_tag toto_instance
     */
    public function testGetImportTagConfigInstance()
    {
        $this->assertEquals('toto_instance', $this->import->getImportTag());
    }

    /**
     * @config [CConfiguration] importTools import import_tag toto
     */
    //  public function testGetImportTagConfigEtab() {
    //    $this->markTestSkipped('Weird behaviour with gconf called from multiple tests in a row');
    //    $this->assertEquals('toto', $this->import->getImportTag());
    //  }

    //  public function testStoreObject() {
    //    $this->markTestSkipped('Soon');
    //  }
    //
    //  public function testGetObjectsList() {
    //    $this->markTestSkipped('Soon');
    //  }
    //
    //  public function testGetObjectGuidByFwdRef() {
    //    $this->markTestSkipped('Soon');
    //  }

    /**
     * @throws CMbException
     */
    public function testConstructFileNotExists()
    {
        $this->expectException(CMbException::class);
        $file_name = __DIR__ . "/../../../tmp/test_file.txt";

        new CPatientXMLImport($file_name);
    }
}
