<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Unit\Export;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CMbPath;
use Ox\Core\Import\CMbObjectExport;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\Export\CXMLPatientExport;
use Ox\Mediboard\Patients\Tests\Fixtures\SimplePatientFixtures;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\OxUnitTestCase;
use ReflectionProperty;

/**
 * @group schedules
 */
class CXMLPatientExportTest extends OxUnitTestCase
{
    public function testGetPatientsToExportWithId(): void
    {
        $patient = $this->getObjectFromFixturesReference(CPatient::class, SimplePatientFixtures::SAMPLE_PATIENT);

        $export = new CXMLPatientExport('test', [CXMLPatientExport::OPTION_PATIENT => $patient->_id]);

        $patients_found = $this->invokePrivateMethod($export, 'getPatientsToExport');

        $this->assertEquals(1, $export->getTotal());

        $patient_found = array_pop($patients_found);
        $this->assertEquals($patient->_id, $patient_found->_id);
    }

    public function testGetPatientsToExportWithIdDoesNotExists(): void
    {
        $export = new CXMLPatientExport('test', [CXMLPatientExport::OPTION_PATIENT => -1]);

        $this->expectExceptionObject(new CMbModelNotFoundException('common-error-Object not found'));

        $this->invokePrivateMethod($export, 'getPatientsToExport');
    }

    /**
     * @config dPpatients CPatient function_distinct 2
     */
    public function testGetPatientsToExportIsGroup(): void
    {
        $prat     = CMediusers::get();
        $function = $prat->loadRefFunction();
        $group_id = $function->group_id;

        // Create some patients in the user's group
        for ($i = 0; $i < 5; $i++) {
            /** @var CPatient $pat */
            $pat           = CPatient::getSampleObject();
            $pat->group_id = $group_id;
            $this->storeOrFailed($pat);
        }


        $export = new CXMLPatientExport(
            'test',
            [
                CXMLPatientExport::OPTION_PRATICIENS => [$prat->_id],
                CXMLPatientExport::OPTION_STEP       => 10,
            ]
        );

        $patients_found = $this->invokePrivateMethod($export, 'getPatientsToExport');

        $this->assertNotEmpty($patients_found);

        foreach ($patients_found as $pat) {
            $this->assertEquals($group_id, $pat->group_id);
        }
    }

    /**
     * @config dPpatients CPatient function_distinct 0
     */
    public function testGetPatientsToExport(): void
    {
        $medecin   = $this->getObjectFromFixturesReference(CMediusers::class, UsersFixtures::REF_USER_MEDECIN);
        $infirmier = $this->getObjectFromFixturesReference(CMediusers::class, UsersFixtures::REF_USER_INFIRMIER);
        $group     = CGroups::loadCurrent();

        // Create some patients + sejours
        for ($i = 0; $i < 5; $i++) {
            /** @var CPatient $pat */
            $pat = CPatient::getSampleObject();
            $this->storeOrFailed($pat);

            /** @var CSejour $sejour */
            $sejour               = CSejour::getSampleObject();
            $sejour->patient_id   = $pat->_id;
            $sejour->praticien_id = ($i % 2) ? $medecin->_id : $infirmier->_id;
            $sejour->group_id     = $group->_id;
            $this->storeOrFailed($sejour);
        }

        $prat_ids = [$medecin->_id, $infirmier->_id];

        $export = new CXMLPatientExport(
            'test',
            [
                CXMLPatientExport::OPTION_PRATICIENS => $prat_ids,
                CXMLPatientExport::OPTION_STEP       => 10,
            ]
        );

        $patients_found = $this->invokePrivateMethod($export, 'getPatientsToExport');
        $this->assertNotEmpty($patients_found);
        $this->assertGreaterThan(0, $export->getTotal());
    }

    public function testExportPatientCannotCreateDir(): void
    {
        $export = $this->getMockBuilder(CXMLPatientExport::class)
            ->onlyMethods(['createDir'])
            ->setConstructorArgs(['/test/'])
            ->getMock();

        $export->method('createDir')->willReturn(false);

        $patient        = new CPatient();
        $patient->_guid = 'CPatient-1';

        $this->expectExceptionObject(
            new CMbException('CXMLPatientExport-Error-Unable to create directory', '/test/CPatient-1')
        );
        $this->invokePrivateMethod($export, 'exportPatient', $patient);
    }

    public function testExportPatientCannotWriteFile(): void
    {
        $export = $this->getMockBuilder(CXMLPatientExport::class)
            ->onlyMethods(['createDir', 'writeXmlFile'])
            ->setConstructorArgs(['/test/'])
            ->getMock();

        $export->method('createDir')->willReturn(true);
        $export->method('writeXmlFile')->willReturn(false);

        $patient        = new CPatient();
        $patient->_guid = 'CPatient-1';

        $this->expectExceptionObject(
            new CMbException('CXMLPatientExport-Error-Unable to write file', '/test/CPatient-1/export.xml')
        );
        $this->invokePrivateMethod($export, 'exportPatient', $patient);
    }

    public function testBuildFwAndBackRefTreeNotificationNotInstalled(): void
    {
        $mod_notif                           = CModule::$installed['notifications'];
        CModule::$installed['notifications'] = null;

        $export = new CXMLPatientExport('/tmp', []);
        $this->assertEquals(
            CMbObjectExport::DEFAULT_FWREFS_TREE,
            $this->invokePrivateMethod($export, 'buildFwRefsTree')
        );
        $this->assertEquals(
            CMbObjectExport::DEFAULT_BACKREFS_TREE,
            $this->invokePrivateMethod($export, 'buildBackRefsTree')
        );

        CModule::$installed['notifications'] = $mod_notif;
    }

    public function testBuildFwAndBackRefTreeNotificationInstalled(): void
    {
        $mod_notif = CModule::$installed['notifications'];
        if (!$mod_notif) {
            CModule::$installed['notifications'] = new CModule();
        }

        $export = new CXMLPatientExport('/tmp', []);
        $this->assertEquals(
            array_merge(CMbObjectExport::DEFAULT_FWREFS_TREE, CMbObjectExport::NOTIF_FW_TREE),
            $this->invokePrivateMethod($export, 'buildFwRefsTree')
        );
        $this->assertEquals(
            array_merge(CMbObjectExport::DEFAULT_BACKREFS_TREE, CMbObjectExport::NOTIF_BACK_TREE),
            $this->invokePrivateMethod($export, 'buildBackRefsTree')
        );

        CModule::$installed['notifications'] = $mod_notif;
    }

    public function testCreateDir(): void
    {
        $unique = uniqid();
        $export = new CXMLPatientExport('/tmp', []);

        $this->assertDirectoryDoesNotExist('/tmp/' . $unique);

        $property = new ReflectionProperty($export, 'current_dir');
        $property->setAccessible(true);
        $property->setValue($export, '/tmp/' . $unique);

        $this->assertTrue($this->invokePrivateMethod($export, 'createDir'));
        $this->assertDirectoryExists('/tmp/' . $unique);

        rmdir('/tmp/' . $unique);
    }

    public function testExportPatientNotFound(): void
    {
        $export = new CXMLPatientExport('/tmp', [CXMLPatientExport::OPTION_PATIENT => -1]);

        $this->assertEquals(0, $export->export());
    }

    public function testExport(): void
    {
        // Avoid having errors for CMedecin
        $infos               = CAppUI::$locale_info;
        CAppUI::$locale_info = true;

        $mock = $this->getMockBuilder(CXMLPatientExport::class)
            ->onlyMethods(['writeXmlFile', 'writeFieldsDescriptionFile', 'writeExportDescriptionFile', 'createDir'])
            ->setConstructorArgs(
                [
                    '/tmp',
                    [
                        CXMLPatientExport::OPTION_PRATICIENS => [CMediusers::get()->_id],
                        CXMLPatientExport::OPTION_STEP       => 2,
                    ],
                ]
            )
            ->getMock();

        // Avoid writing files or directories
        $mock->method('writeXmlFile')->willReturn(true);
        $mock->method('createDir')->willReturn(true);

        $this->assertEquals(2, $mock->export());

        CAppUI::$locale_info = $infos;
    }

    /**
     * @dataProvider createArchiveProvider
     */
    public function testCeateTarArchive(string $extension, string $method): void
    {
        $dir = dirname(__DIR__, 2) . '/Resources/Import/ImportPatients';

        $this->assertFileDoesNotExist($dir . $extension);
        $export = new CXMLPatientExport('foo');
        try {
            $this->invokePrivateMethod($export, $method, $dir);
            $this->assertFileExists($dir . $extension);
        } finally {
            if (file_exists($dir . $extension)) {
                CMbPath::remove($dir . $extension);
            }
        }
    }

    public function createArchiveProvider(): array
    {
        return [
            'tar' => ['.tar', 'createTarArchive'],
            'zip' => ['.zip', 'createZipArchive'],
        ];
    }
}
