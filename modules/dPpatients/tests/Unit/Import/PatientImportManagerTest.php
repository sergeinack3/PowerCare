<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Unit\Import;

use Ox\Core\CMbException;
use Ox\Core\CMbPath;
use Ox\Mediboard\Patients\CPatientXMLImport;
use Ox\Mediboard\Patients\Export\CXMLPatientExport;
use Ox\Mediboard\Patients\Import\Xml\PatientImportManager;
use Ox\Tests\OxUnitTestCase;

class PatientImportManagerTest extends OxUnitTestCase
{
    public function testAddIgnoredClasses(): void
    {
        $reset_ignore = CPatientXMLImport::$_ignored_classes;

        CPatientXMLImport::$_ignored_classes = [];

        $manager = new PatientImportManager(
            'foo',
            0,
            0,
            [PatientImportManager::OPTION_IGNORE_CLASSES => 'CPatient|CSejour']
        );
        $this->invokePrivateMethod($manager, 'addIgnoredClasses');

        $this->assertArrayContentsEquals(
            array_merge(['CPatient', 'CSejour'], CPatientXMLImport::$_prescription_classes),
            CPatientXMLImport::$_ignored_classes
        );

        CPatientXMLImport::$_ignored_classes = $reset_ignore;
    }

    public function testAddIgnoredClassesWithPrescription(): void
    {
        $reset_ignore = CPatientXMLImport::$_ignored_classes;

        CPatientXMLImport::$_ignored_classes = [];
        $manager                             = new PatientImportManager(
            'foo',
            0,
            0,
            [
                PatientImportManager::OPTION_IGNORE_CLASSES => 'CPatient|CSejour',
                PatientImportManager::OPTION_IMPORT_PRESC   => true,
            ]
        );
        $this->invokePrivateMethod($manager, 'addIgnoredClasses');

        $this->assertArrayContentsEquals(
            ['CPatient', 'CSejour'],
            CPatientXMLImport::$_ignored_classes
        );

        CPatientXMLImport::$_ignored_classes = $reset_ignore;
    }

    public function testExtractPatientDataBadExtension(): void
    {
        $manager = new PatientImportManager('foo', 0, 0, []);

        $this->expectExceptionObject(
            new CMbException(
                'CXMLPatientExport-Error-Type is not valid use one of',
                'txt',
                CXMLPatientExport::ARCHIVE_TYPE_TAR . ', ' . CXMLPatientExport::ARCHIVE_TYPE_ZIP
            )
        );

        $this->invokePrivateMethod(
            $manager,
            'extractPatientData',
            dirname(__DIR__, 2) . '/Resources/Import/bad_extension.txt'
        );
    }

    public function testExtractPatientData(): void
    {
        $manager = new PatientImportManager('foo', 0, 0, []);

        $resources_dir = dirname(__DIR__, 2) . '/Resources/Import/';

        $tar_directory = $resources_dir . 'extract_patient_data_tar';
        $this->assertDirectoryDoesNotExist($tar_directory);

        $zip_directory = $resources_dir . 'extract_patient_data_zip';
        $this->assertDirectoryDoesNotExist($zip_directory);

        try {
            $this->invokePrivateMethod($manager, 'extractPatientData', $tar_directory . '.tar');
            $this->assertDirectoryExists($tar_directory);
            $this->assertFileExists($tar_directory . '/tar_extract_ok.txt');

            $this->invokePrivateMethod($manager, 'extractPatientData', $zip_directory . '.zip');
            $this->assertDirectoryExists($zip_directory);
            $this->assertFileExists($zip_directory . '/zip_extract_ok.txt');
        } finally {
            if (is_dir($tar_directory)) {
                CMbPath::remove($tar_directory);
            }

            if (is_dir($zip_directory)) {
                CMbPath::remove($zip_directory);
            }
        }
    }

    /**
     * @dataProvider importSinglePatientProvider
     */
    public function testImportSinglePatient(string $directory_path, bool $xml_exists): void
    {
        $xml_import = $this->getMockBuilder(CPatientXMLImport::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager = $this->getMockBuilder(PatientImportManager::class)
            ->onlyMethods(['getImporter', 'import'])
            ->disableOriginalConstructor()
            ->getMock();

        $manager->method('getImporter')->willReturn($xml_import);

        $this->assertEquals(
            $xml_exists,
            $this->invokePrivateMethod($manager, 'importSinglePatient', $directory_path, false, [], null)
        );
    }

    public function testImport(): void
    {
        $dir = dirname(__DIR__, 2) . '/Resources/Import/ImportPatients';

        $manager = $this->getMockBuilder(PatientImportManager::class)
            ->onlyMethods(['prepareCacheAndHandlers', 'addIgnoredClasses', 'importSinglePatient'])
            ->setConstructorArgs([$dir, 0, 10, []])
            ->getMock();

        $this->assertEquals(2, $manager->import());
    }

    public function importSinglePatientProvider(): array
    {
        $resource_dir = dirname(__DIR__, 2) . '/Resources/Import/';

        return [
            'xml ok'             => [$resource_dir . 'xml_ok', true],
            'not an xml'         => [$resource_dir . 'not_an_xml', false],
            'tar with xml'       => [$resource_dir . 'tar_with_xml', true],
            'tar without xml'    => [$resource_dir . 'tar_without_xml', false],
            'zip with xml'       => [$resource_dir . 'zip_with_xml', true],
            'zip without xml'    => [$resource_dir . 'zip_without_xml', false],
            'tar with extension' => [$resource_dir . 'tar_with_xml.tar', true],
        ];
    }
}
