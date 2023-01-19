<?php

/**
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Etablissement\Tests\Unit;

use Exception;
use Ox\Core\CMbException;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\ExternalFacilitiesImporter;
use Ox\Tests\OxUnitTestCase;

class ExternalFacilitiesImporterTest extends OxUnitTestCase
{
    /**
     * @throws CMbException
     */
    public function testConstructoThrowsException(): void
    {
        $this->expectExceptionMessage("common-error-No file found.");

        new ExternalFacilitiesImporter([]);
    }

    /**
     * @throws Exception
     */
    public function testGetImportResultReturnExpectedValue(): void
    {
        $importer = $this->mockExternalFacilitiesImporter();

        $importer->doImport();

        $actual = $importer->getImportResult();

        $expected = [
            "created" => 1,
            "updated" => 0,
            "error"   => 0,
        ];

        $this->assertEquals($expected, $actual);
    }

    public function mockExternalFacilitiesImporter(): ExternalFacilitiesImporter
    {
        $importer = $this->getMockBuilder(ExternalFacilitiesImporter::class)
            ->onlyMethods(
                [
                    "getFile",
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $importer->expects($this->once())
            ->method("getFile")
            ->willReturn($this->mockCSVTestFile());

        return $importer;
    }

    public function mockCSVTestFile(): CCSVFile
    {
        $data = [
            [
                123456789,
                12312312312312,
                "12345A",
                "Lorem Ipsum",
                "Lorem Ipsum",
                "1 Rue Lorem",
                "12345",
                "Ipsum",
                "0123456789",
                "0987654321",
                1,
                1,
                0,
            ],
            [
                123456789,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
            ],
        ];

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
}
