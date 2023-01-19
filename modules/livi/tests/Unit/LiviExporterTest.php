<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Livi\Tests\Unit;

use Ox\Core\CMbException;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Livi\LiviExporter;
use Ox\Tests\OxUnitTestCase;
use ReflectionClass;

class LiviExporterTest extends OxUnitTestCase
{

    public function goodDataProvider(): array
    {
        return [
            [[['9835b5bd-2bd5-4e15-9190-8b6b2006895f']]],
            [[['6b4a3373-1a9b-42a1-92e3-663e8da95577']]],
            [[['638ff729-4b89-42cf-b4c8-0aaf0286d131']]],
            [[['73ebb2ee-fee7-4146-96ce-b1ed296125e4']]],
        ];
    }

    public function badDataProvider(): array
    {
        return [
            "readline returns null"                 => [[null], "common-msg-No patient found."],
            "readline returns false"                => [[false], "common-msg-No patient found."],
            "readline returns []"                   => [[[]], "common-msg-No patient found."],
            "readline returns wrong id"             => [[["wrong id"]], "LiviExporter-error-Invalid id format"],
            "readline returns more than one column" => [
                [
                    [
                        "col1" => "73ebb2ee-fee7-4146-96ce-b1ed296125e4",
                        "col2" => "73ebb2ee-fee7-4146-96ce-b1ed296125e4",
                    ],
                ],
                "common-error-Invalid format",
            ],
        ];
    }

    public function goodIdsPatientProvider(): array
    {
        return [
            [["patient0" => '6b4a3373-1a9b-42a1-92e3-663e8da95577']],
            [
                [
                    "patient1" => '9835b5bd-2bd5-4e15-9190-8b6b2006895f',
                    "patient2" => '6b4a3373-1a9b-42a1-92e3-663e8da95577',
                ],
            ],
            [
                [
                    "patient3" => '6b4a3373-1a9b-42a1-92e3-663e8da95577',
                    "patient4" => '87f1f3c3-a319-470f-a5e8-42112eb43909',
                    "patient5" => '4630be45-224d-4b5c-b080-fe7e767ac1c6',
                ]
            ],
        ];
    }

    /**
     * @dataProvider  goodDataProvider
     * @throws CMbException
     */
    public function testFromCsv(array $data): void
    {
        $csv_file = $this->getCsvMock($data);
        $exporter = LiviExporter::fromCsv($csv_file);

        $this->assertInstanceOf(LiviExporter::class, $exporter);
    }

    public function testEmptyCsvThrowsException(): void
    {
        $this->expectException(CMbException::class);

        $csv_file = $this->getEmptyCsvMock();
        LiviExporter::fromCsv($csv_file);
    }

    /**
     * @dataProvider badDataProvider
     * @throws CMbException
     */
    public function testBadCsvDataThrowsException(array $data, string $exception_message): void
    {
        $this->expectExceptionMessage($exception_message);

        $csv_file = $this->getBadCsvMock($data);
        LiviExporter::fromCsv($csv_file);
    }

    /**
     * @dataProvider goodIdsPatientProvider
     * @throws CMbException
     */
    public function testToZip(array $patient_ids): void
    {
        $exporter = $this->getLiviExporterMock($patient_ids);

        $date_debut = '01-01-1970';
        $date_fin   = '31-12-1970';

        $actual = $exporter->toZip($date_debut, $date_fin);

        $this->assertIsString($actual);
    }

    public function testInvalidDatesToZip(): void
    {
        $this->expectException(CMbException::class);

        $exporter = new LiviExporter([]);

        $date_debut = '31-12-1970';
        $date_fin   = '01-01-1970';

        $exporter->toZip($date_debut, $date_fin);
    }

    private function getCsvMock(array $data): CCSVFile
    {
        $csv_file = $this->getMockBuilder(CCSVFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $csv_file->expects($this->any())
            ->method('readLine')
            ->will(
                $this->onConsecutiveCalls(...$data)
            );

        return $csv_file;
    }

    private function getEmptyCsvMock(): CCSVFile
    {
        $csv_file = $this->getMockBuilder(CCSVFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $csv_file->expects($this->any())
            ->method('readLine')
            ->willReturn([]);

        return $csv_file;
    }

    private function getBadCsvMock(array $data): CCSVFile
    {
        $csv_file = $this->getMockBuilder(CCSVFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $csv_file->expects($this->any())
            ->method('readLine')
            ->will(
                $this->onConsecutiveCalls(...$data)
            );

        return $csv_file;
    }

    private function getLiviExporterMock(array $patient_ids): LiviExporter
    {
        $exporter = $this->getMockBuilder(LiviExporter::class)
            ->disableOriginalConstructor()
            ->getMock();


        $exporter->expects($this->any())->method('toZip')->willReturn("Lorem Ipsum");

        $reflection_livi_exporter = new ReflectionClass(LiviExporter::class);

        $reflection_patients_ids = $reflection_livi_exporter->getProperty('patients_livi_ids');

        $reflection_patients_ids->setAccessible(true);
        $reflection_patients_ids->setValue($exporter, $patient_ids);

        $exporter->expects($this->any())->method('fromCsv')->willReturn($exporter);

        return $exporter;
    }
}
