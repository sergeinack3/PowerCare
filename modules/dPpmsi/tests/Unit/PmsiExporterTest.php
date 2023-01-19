<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Pmsi\Tests\Unit;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Pmsi\PmsiExporter;
use Ox\Mediboard\Pmsi\Tests\Fixtures\PSMIExporterFixtures;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use ReflectionException;

class PmsiExporterTest extends OxUnitTestCase
{
    /**
     * @param string|null $date_min
     * @param string|null $date_max
     * @param array       $type_adm
     * @param string      $type_operation
     *
     * @throws CMbException
     * @dataProvider invalidDatesPmsiProvider
     */
    public function testInvalidPeriodExportThrowException(
        ?string $date_min = null,
        ?string $date_max = null,
        array $type_adm = [],
        string $type_operation = "planned"
    ): void {
        $this->expectException(Exception::class);

        $pmsi_exporter = new PmsiExporter();

        $pmsi_exporter->exportOperationsToCsv($date_min, $date_max, $type_adm, $type_operation);
    }

    /**
     * @return array[]
     */
    public function invalidDatesPmsiProvider(): array
    {
        return [
            "date max > date min" => [
                "date_min" => '31-12-2021',
                "date_max" => '01-01-2021',
                [],
                "planned",
            ],
            "date max null"       => [
                "date_min" => '31-12-1970',
                "date_max" => null,
                [],
                "planned",
            ],
            "date min null"       => [
                "date_min" => null,
                "date_max" => '01-01-1970',
                [],
                "unplanned",
            ],
        ];
    }

    /**
     * @throws Exception
     * @dataProvider exportOperationsToCsvProvider
     */
    public function testexportOperationsToCsvReturnNumberOfOperations(
        ?string $date_min = null,
        ?string $date_max = null,
        array $type_adm = [],
        string $type_operation = "planned",
        int $expected = 0
    ): void {
        $pmsi_exporter = $this->getPmsiExporterMock();

        $actual = $pmsi_exporter->exportOperationsToCsv($date_min, $date_max, $type_adm, $type_operation);

        $this->assertEquals($expected, $actual);
    }

    public function exportOperationsToCsvProvider(): array
    {
        return [
            "test 1" => [
                "date_min" => '01-01-2021',
                "date_max" => '31-01-2021',
                [],
                "planned",
                2,
            ],
        ];
    }

    /**
     * @param string|null $date_min
     * @param string|null $date_max
     * @param array       $type_adm
     *
     * @throws Exception
     * @dataProvider getCurrentOperationsProvider
     */
    public function testgetCurrentOperations(
        ?string $date_min = null,
        ?string $date_max = null,
        array $type_adm = []
    ): void {
        $pmsi_exporter           = new PmsiExporter();
        $pmsi_exporter->date_min = $date_min;
        $pmsi_exporter->date_max = $date_max;
        $pmsi_exporter->types    = $type_adm;

        $actual = $this->invokePrivateMethod($pmsi_exporter, "getCurrentOperations");

        $this->assertIsArray($actual);

        foreach ($actual as $_op) {
            $this->assertNotNull($_op->_id);
        }
    }

    /**
     * @return array[]
     */
    public function getCurrentOperationsProvider(): array
    {
        return [
            "tout les types" => [
                "date_min" => '01-01-2021',
                "date_max" => '31-12-2021',
                [""],
                "planned",
            ],
            "comp"           => [
                "date_min" => '01-01-2021',
                "date_max" => '31-12-2021',
                ["comp"],
                "planned",
            ],
            "ambu"           => [
                "date_min" => '01-01-2021',
                "date_max" => '31-12-2021',
                ["ambu"],
                "planned",
            ],
            "comp + ambu"    => [
                "date_min" => '01-01-2021',
                "date_max" => '31-12-2021',
                ["comp", "ambu"],
                "planned",
            ],
        ];
    }

    /**
     * @return PmsiExporter
     * @throws Exception
     */
    public function getPmsiExporterMock(): PmsiExporter
    {
        $operation   = $this->getObjectFromFixturesReference(
            COperation::class,
            PSMIExporterFixtures::TAG_PSMI_OPERATION
        );
        $operation_2 = $this->getObjectFromFixturesReference(
            COperation::class,
            PSMIExporterFixtures::TAG_PSMI_OPERATION_2
        );
        $exporter    = $this->getMockBuilder(PmsiExporter::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                              "getCurrentOperations",
                              "generateCSVFile",
                              "writeLinePmsiOperation",
                          ])
            ->getMock();

        $exporter->method("getCurrentOperations")->willReturn([$operation, $operation_2]);
        $exporter->method("writeLinePmsiOperation")->willReturn([]);
        $exporter->method("generateCSVFile")->willReturn($this->getCSVExportFileMock([]));

        return $exporter;
    }

    /**
     * @throws TestsException
     * @throws ReflectionException
     * @dataProvider writeLinePmsiOperationProvider
     */
    public function testWriteLinePmsiReturnOperationData(
        COperation $operation,
        CPatient $patient,
        array $expected
    ): void {
        $exporter = new PmsiExporter();

        $actual = $this->invokePrivateMethod($exporter, "writeLinePmsiOperation", $operation, $patient);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function writeLinePmsiOperationProvider(): array
    {
        /** @var COperation $operation */
        $operation = $this->getObjectFromFixturesReference(
            COperation::class,
            PSMIExporterFixtures::TAG_PSMI_OPERATION
        );
        $operation->loadRefChir();
        $operation->loadRefSejour()->loadNDA();

        /** @var CPatient $patient */
        $patient = $this->getObjectFromFixturesReference(
            CPatient::class,
            PSMIExporterFixtures::TAG_PSMI_PATIENT
        );

        $data = [
            $operation->_ref_sejour->_NDA,
            $operation->_ref_chir->_view,
            "$patient->nom $patient->prenom",
            CMbDT::date(),
            "00:00:00",
            " - AAFA001",
            "1",
            "?",
        ];

        return [
            "operation 1" => [$operation, $patient, $data],
        ];
    }

    /**
     * @param array $data
     *
     * @return CCSVFile
     */
    public function getCSVExportFileMock(array $data): CCSVFile
    {
        $csv_file = $this->getMockBuilder(CCSVFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $csv_file->expects($this->any())
            ->method('writeLine')
            ->will(
                $this->onConsecutiveCalls(...$data)
            );

        return $csv_file;
    }
}
