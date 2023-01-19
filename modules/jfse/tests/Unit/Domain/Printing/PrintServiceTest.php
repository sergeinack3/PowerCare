<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Domain\Printing;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\ApiClients\PrintingClient;
use Ox\Mediboard\Jfse\Domain\Printing\PrintingCerfaConf;
use Ox\Mediboard\Jfse\Domain\Printing\PrintingService;
use Ox\Mediboard\Jfse\Domain\Printing\PrintingSlipConf;
use Ox\Mediboard\Jfse\Domain\Printing\PrintSlipModeEnum;
use Ox\Mediboard\Jfse\Exceptions\Printing\PrintingException;
use Ox\Mediboard\Jfse\Mappers\PrintingMapper;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class PrintServiceTest extends UnitTestJfse
{
    /**
     * @dataProvider slipModeOneProvider
     */
    public function testPrintSlipModeOne(PrintingSlipConf $conf): void
    {
        $mapper = $this->getMockBuilder(PrintingMapper::class)->setMethods(['slipConfToArray'])->getMock();
        $mapper->method('slipConfToArray')->willReturn([]);

        $json_response = '{"method": {"output": {"bordereau": "base64string"}}}';
        $client        = $this->makeClientFromGuzzleResponses([$this->makeJsonGuzzleResponse(200, $json_response)]);

        $printing_service = new PrintingService(new PrintingClient($client, $mapper));

        $expected = "base64string";

        $this->assertEquals($expected, $printing_service->getTransmissionSlip($conf));
    }

    public function slipModeOneProvider(): array
    {
        $conf1 = new PrintingSlipConf(PrintSlipModeEnum::MODE_ONE_PRINT()->getValue(), 0);
        $conf1->setDateMin(new DateTimeImmutable("2020-11-01"));
        $conf1->setDateMax(new DateTimeImmutable("2020-11-12"));
        $conf1->setBatch([1, 2]);
        $conf1->setFiles([3, 4]);

        $conf2 = new PrintingSlipConf(PrintSlipModeEnum::MODE_PRINT_DATE_BOUNDS()->getValue(), 0);
        $conf2->setDateMin(new DateTimeImmutable("2020-11-01"));
        $conf2->setDateMax(new DateTimeImmutable("2020-11-12"));
        $conf2->setBatch([1, 2]);

        $conf3 = new PrintingSlipConf(PrintSlipModeEnum::MODE_ONE_OR_SEVERAL_FILES()->getValue(), 0);
        $conf3->setFiles([1, 2]);

        return [[$conf1], [$conf2], [$conf3]];
    }

    public function testPrintSlipModeOneTwoWithoutBatch(): void
    {
        $conf = new PrintingSlipConf(PrintSlipModeEnum::MODE_ONE_PRINT()->getValue(), 0);

        $this->expectException(PrintingException::class);
        (self::serviceForExceptionTests())->getTransmissionSlip($conf);
    }

    public function testPrintSlipModeThreeWithoutBatch(): void
    {
        $conf = new PrintingSlipConf(PrintSlipModeEnum::MODE_PRINT_DATE_BOUNDS()->getValue(), 0);

        $this->expectException(PrintingException::class);
        (self::serviceForExceptionTests())->getTransmissionSlip($conf);
    }

    public function testPrintSlipModeFourWithoutBatch(): void
    {
        $conf = new PrintingSlipConf(PrintSlipModeEnum::MODE_ONE_OR_SEVERAL_FILES()->getValue(), 0);

        $this->expectException(PrintingException::class);
        (self::serviceForExceptionTests())->getTransmissionSlip($conf);
    }

    public function testPrintSlipUnknownMode(): void
    {
        $this->expectException(PrintingException::class);
        (self::serviceForExceptionTests())->getTransmissionSlip(new PrintingSlipConf(55, 0));
    }

    public function testPrintCerfa(): void
    {
        $json_response = '{"method": {"output": {"cerfa": "base64string"}}}';
        $client        = $this->makeClientFromGuzzleResponses([$this->makeJsonGuzzleResponse(200, $json_response)]);

        $mapper = $this->getMockBuilder(PrintingMapper::class)->setMethods(['cerfaConfToArray'])->getMock();
        $mapper->method('cerfaConfToArray')->willReturn([]);

        $service = new PrintingService(new PrintingClient($client, $mapper));

        $printing_cerfa_conf = new PrintingCerfaConf(true, true, true);
        $printing_cerfa_conf->setInvoiceId(123456789);

        $this->assertEquals("base64string", $service->getCerfa($printing_cerfa_conf));
    }

    public function testPrintCerfaWithoutInvoiceIdentifiers(): void
    {
        $this->expectException(PrintingException::class);
        (self::serviceForExceptionTests())->getCerfa(new PrintingCerfaConf(true, true, true));
    }

    public function testPrintInvoiceInformationWithInvoiceNumber(): void
    {
        $json_response = '{"method": {"output": {"infosFSE": "base64string"}}}';
        $client        = $this->makeClientFromGuzzleResponses([$this->makeJsonGuzzleResponse(200, $json_response)]);
        $service       = new PrintingService(new PrintingClient($client));

        $this->assertEquals("base64string", $service->getInvoiceInformation(123456789, null));
    }

    public function testPrintInvoiceInformationWithInvoiceId(): void
    {
        $json_response = '{"method": {"output": {"infosFSE": "base64string"}}}';
        $client        = $this->makeClientFromGuzzleResponses([$this->makeJsonGuzzleResponse(200, $json_response)]);
        $service       = new PrintingService(new PrintingClient($client));

        $this->assertEquals("base64string", $service->getInvoiceInformation(null, 987654321));
    }

    public function testPrintInvoiceInformationWithoutInvoiceIdentifiers(): void
    {
        $this->expectException(PrintingException::class);
        (self::serviceForExceptionTests())->getInvoiceInformation(null, null);
    }

    private function serviceForExceptionTests(): PrintingService
    {
        $client = $this->getMockBuilder(PrintingClient::class)->disableOriginalConstructor()->getMock();
        $mapper = $this->getMockBuilder(PrintingMapper::class)->getMock();

        return new PrintingService($client, $mapper);
    }
}
