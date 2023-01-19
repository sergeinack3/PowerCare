<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Unit\Export\Description;

use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\Export\Description\CCSVPatientExportDescriptionWriter;
use Ox\Mediboard\Patients\Export\Description\CXMLPatientExportFieldDescription;
use Ox\Mediboard\Patients\Export\Description\CXMLPatientExportInstanceDescription;
use Ox\Tests\OxUnitTestCase;

class CCSVPatientExportDescriptionWriterTest extends OxUnitTestCase
{
    public function testInitCsvBadPath(): void
    {
        $unique = uniqid();
        $this->expectExceptionObject(
            new CMbException(
                'CCSVPatientExportDescriptionWritter-Error-Cannot write file', "/tmp/{$unique}/test"
            )
        );

        // Use @ to avoid writting error to std::out
        @new CCSVPatientExportDescriptionWriter("/tmp/{$unique}/test");
    }

    public function testInitCsvOk(): void
    {
        $writer = new CCSVPatientExportDescriptionWriter('/tmp/test');
        $csv    = $this->getPrivateProperty($writer, 'csv');
        $this->assertInstanceOf(CCSVFile::class, $csv);
        $this->assertEquals(CCSVPatientExportDescriptionWriter::HEADERS, $csv->column_names);

        $this->invokePrivateMethod($writer, 'close');
        unlink('/tmp/test');
    }

    /**
     * @dataProvider getDataForFieldProvider
     */
    public function testGetDataForField(
        CXMLPatientExportFieldDescription $field,
        string $class_tr,
        string $short_class_name,
        string $root_path,
        array $expected
    ): void {
        // Avoid creating files
        $mock = $this->getMockBuilder(CCSVPatientExportDescriptionWriter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals(
            $expected,
            $this->invokePrivateMethod(
                $mock,
                'getDataForField',
                $field,
                $class_tr,
                $short_class_name,
                $root_path
            )
        );
    }

    /**
     * @group schedules
     */
    public function testWriteDescriptions(): void
    {
        $file_path = '/tmp/test';
        $writer = new CCSVPatientExportDescriptionWriter($file_path);

        $patient = new CPatient();
        $patient_description = new CXMLPatientExportInstanceDescription($patient);
        $patient_description->add('nom');
        $patient_description->add('medecin_traitant');

        $file = new CFile();
        $file_description = new CXMLPatientExportInstanceDescription($file);
        $file_description->add('file_id');
        $file_description->add('object_id');
        $file_description->add('file_name');

        $writer->writeDescriptions(['CPatient' => $patient_description, 'CFile' => $file_description]);

        $fp = fopen($file_path, 'r');

        $expected = $this->getExpectedFileForWriteDescription();

        while ($line = fgetcsv($fp, 0, ';', '"')) {
            $this->assertTrue(in_array($line, $expected));
        }

        unlink($file_path);
    }

    public function getDataForFieldProvider(): array
    {
        $patient             = new CPatient();
        $patient_description = new CXMLPatientExportFieldDescription($patient, 'nom');

        $file                       = new CFile();
        $file_id_description        = new CXMLPatientExportFieldDescription($file, 'file_id');
        $file_object_id_description = new CXMLPatientExportFieldDescription($file, 'object_id');

        return [
            'patient_nom'  => [
                $patient_description,
                CAppUI::tr('CPatient'),
                'CPatient',
                '//object[@class="CPatient"]',
                [
                    CCSVPatientExportDescriptionWriter::HEADER_CLASS_TR         => CAppUI::tr('CPatient'),
                    CCSVPatientExportDescriptionWriter::HEADER_SHORT_CLASS_NAME => 'CPatient',
                    CCSVPatientExportDescriptionWriter::HEADER_FIELD_NAME       => 'nom',
                    CCSVPatientExportDescriptionWriter::HEADER_FIELD_TR         => CAppUI::tr('CPatient-nom'),
                    CCSVPatientExportDescriptionWriter::HEADER_FIELD_DESC       => CAppUI::tr('CPatient-nom-desc'),
                    CCSVPatientExportDescriptionWriter::HEADER_FIELD_PROP       => $patient->getProps()['nom'],
                    CCSVPatientExportDescriptionWriter::HEADER_FIELD_SQL_PROP   => $patient->_specs['nom']->getDBSpec(),
                    CCSVPatientExportDescriptionWriter::HEADER_FIELD_PATH       => '//object[@class="CPatient"]/field[@name="nom"]',
                ],
            ],
            'file_file_id' => [
                $file_id_description,
                CAppUI::tr('CFile'),
                'CFile',
                '//object[@class="CFile"]',
                [
                    CCSVPatientExportDescriptionWriter::HEADER_CLASS_TR         => CAppUI::tr('CFile'),
                    CCSVPatientExportDescriptionWriter::HEADER_SHORT_CLASS_NAME => 'CFile',
                    CCSVPatientExportDescriptionWriter::HEADER_FIELD_NAME       => 'file_id',
                    CCSVPatientExportDescriptionWriter::HEADER_FIELD_TR         => CAppUI::tr('CFile-file_id'),
                    CCSVPatientExportDescriptionWriter::HEADER_FIELD_DESC       => CAppUI::tr('CFile-file_id-desc'),
                    CCSVPatientExportDescriptionWriter::HEADER_FIELD_PROP       => $file->getProps()['file_id'],
                    CCSVPatientExportDescriptionWriter::HEADER_FIELD_SQL_PROP   => $file->_specs['file_id']->getDBSpec(),
                    CCSVPatientExportDescriptionWriter::HEADER_FIELD_PATH       => '//object[@class="CFile"]/@id',
                ],
            ],
            'file_object_id' => [
                $file_object_id_description,
                CAppUI::tr('CFile'),
                'CFile',
                '//object[@class="CFile"]',
                [
                    CCSVPatientExportDescriptionWriter::HEADER_CLASS_TR         => CAppUI::tr('CFile'),
                    CCSVPatientExportDescriptionWriter::HEADER_SHORT_CLASS_NAME => 'CFile',
                    CCSVPatientExportDescriptionWriter::HEADER_FIELD_NAME       => 'object_id',
                    CCSVPatientExportDescriptionWriter::HEADER_FIELD_TR         => CAppUI::tr('CFile-object_id'),
                    CCSVPatientExportDescriptionWriter::HEADER_FIELD_DESC       => CAppUI::tr('CFile-object_id-desc'),
                    CCSVPatientExportDescriptionWriter::HEADER_FIELD_PROP       => $file->getProps()['object_id'],
                    CCSVPatientExportDescriptionWriter::HEADER_FIELD_SQL_PROP   => $file->_specs['object_id']->getDBSpec(),
                    CCSVPatientExportDescriptionWriter::HEADER_FIELD_PATH       => '//object[@class="CFile"]/@object_id',
                ],
            ],
        ];
    }

    private function getExpectedFileForWriteDescription(): array
    {
        $datas = [];
        $headers = [];
        foreach (CCSVPatientExportDescriptionWriter::HEADERS as $header) {
            $headers[] = CAppUI::tr('CCSVPatientExportDescriptionWriter.headers.' . $header);
        }

        $datas[] = $headers;

        $patient = new CPatient();
        $file = new CFile();

        // Build line for nom
        $datas[] = [
            CAppUI::tr('CPatient'),
            'CPatient',
            'nom',
            CAppUI::tr('CPatient-nom'),
            CAppUI::tr('CPatient-nom-desc'),
            $patient->getProps()['nom'],
            $patient->_specs['nom']->getDBSpec(),
            '//object[@class="CPatient"]/field[@name="nom"]',
        ];

        // Build line for medecin_traitant
        $datas[] = [
            CAppUI::tr('CPatient'),
            'CPatient',
            'medecin_traitant',
            CAppUI::tr('CPatient-medecin_traitant'),
            CAppUI::tr('CPatient-medecin_traitant-desc'),
            $patient->getProps()['medecin_traitant'],
            $patient->_specs['medecin_traitant']->getDBSpec(),
            '//object[@class="CPatient"]/@medecin_traitant',
        ];

        // Build line for file_id
        $datas[] = [
            CAppUI::tr('CFile'),
            'CFile',
            'file_id',
            CAppUI::tr('CFile-file_id'),
            CAppUI::tr('CFile-file_id-desc'),
            $file->getProps()['file_id'],
            $file->_specs['file_id']->getDBSpec(),
            '//object[@class="CFile"]/@id',
        ];

        // Build line for object_id
        $datas[] = [
            CAppUI::tr('CFile'),
            'CFile',
            'object_id',
            CAppUI::tr('CFile-object_id'),
            CAppUI::tr('CFile-object_id-desc'),
            $file->getProps()['object_id'],
            $file->_specs['object_id']->getDBSpec(),
            '//object[@class="CFile"]/@object_id',
        ];

        // Build line for file_name
        $datas[] = [
            CAppUI::tr('CFile'),
            'CFile',
            'file_name',
            CAppUI::tr('CFile-file_name'),
            CAppUI::tr('CFile-file_name-desc'),
            $file->getProps()['file_name'],
            $file->_specs['file_name']->getDBSpec(),
            '//object[@class="CFile"]/field[@name="file_name"]',
        ];

        return $datas;
    }
}
