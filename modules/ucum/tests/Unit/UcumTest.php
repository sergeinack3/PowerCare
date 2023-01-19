<?php

/**
 * @package Mediboard\Ucum
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ucum\Tests\Unit;

use Exception;
use Ox\Components\Cache\Exceptions\CouldNotGetCache;
use Ox\Core\CHTTPClient;
use Ox\Mediboard\Ucum\Ucum;
use Ox\Tests\OxUnitTestCase;
use Psr\SimpleCache\InvalidArgumentException;
use stdClass;

class UcumTest extends OxUnitTestCase
{
    /**
     * @throws Exception
     */
    private function mockUcumReturnWrongUnit(): Ucum
    {
        $http_client_mock = $this->getMockBuilder(CHTTPClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();
        $http_client_mock->expects($this->once())->method('get')->willReturn('mod-ucum-wrong_unit');

        $ucum_mock = $this->getMockBuilder(Ucum::class)
            ->onlyMethods(['callClient'])
            ->getMock();
        $ucum_mock->expects($this->any())
            ->method('callClient')
            ->willReturn($http_client_mock->get());

        return $ucum_mock;
    }

    /**
     * @throws Exception
     */
    private function mockUcumReturnException(): Ucum
    {
        $http_client_mock = $this->getMockBuilder(CHTTPClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();
        $http_client_mock->expects($this->any())->method('get')->willReturn(false);

        $ucum_mock = $this->getMockBuilder(Ucum::class)
            ->onlyMethods(['callClient'])
            ->getMock();
        $ucum_mock->expects($this->any())
            ->method('callClient')
            ->willThrowException(new Exception("exception"));

        return $ucum_mock;
    }

    /**
     * @throws Exception
     */
    private function mockUcumReturnObject(): Ucum
    {
        $http_client_mock                         = $this->getMockBuilder(CHTTPClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();
        $result                                   = new stdClass();
        $response                                 = new stdClass();
        $response->ResultQuantity                 = 100;
        $response->ResultBaseUnits                = "m";
        $result->UCUMWebServiceResponse           = new stdClass();
        $result->UCUMWebServiceResponse->Response = $response;

        $http_client_mock->expects($this->any())->method('get')->willReturn($result);

        $ucum_mock = $this->getMockBuilder(Ucum::class)
            ->onlyMethods(['callClient'])
            ->getMock();
        $ucum_mock->expects($this->any())
            ->method('callClient')
            ->willReturn($http_client_mock->get());

        return $ucum_mock;
    }

    /**
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function testCallValidationReturnObject(): void
    {
        $ucum = $this->mockUcumReturnObject();

        $actual = $ucum->callValidation('lorem', true);

        $this->assertNotNull($actual);
    }

    /**
     * @throws InvalidArgumentException
     * @throws CouldNotGetCache
     * @throws Exception
     */
    public function testCallValidationReturnFalse(): void
    {
        $ucum = $this->mockUcumReturnException();

        $actual = $ucum->callValidation('', true);

        $this->assertFalse($actual);
    }

    /**
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function testCallConversionReturnFalse(): void
    {
        $ucum = $this->mockUcumReturnException();

        $actual = $ucum->callConversion(1, "toto", "titi", true);

        $this->assertFalse($actual);
    }

    /**
     * @throws InvalidArgumentException
     * @throws CouldNotGetCache
     * @throws Exception
     */
    public function testCallBaseReturnFalse(): void
    {
        $ucum = $this->mockUcumReturnException();

        $actual = $ucum->callToBase("", true);

        $this->assertFalse($actual);
    }

    /**
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function testCallConversionReturnWrongUnitMessage(): void
    {
        $ucum = $this->mockUcumReturnWrongUnit();

        $actual = $ucum->callConversion('1', '', '', false);

        $this->assertEquals("mod-ucum-wrong_unit", $actual);
    }

    /**
     * @param string $qty
     * @param string $from
     * @param string $to
     * @param string $expected
     *
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     * @throws Exception
     * @dataProvider callConversionProvider
     */
    public function testCallConversionReturnExpectedValue(string $qty, string $from, string $to, string $expected): void
    {
        $ucum = $this->mockUcumReturnObject();

        $actual = $ucum->callConversion($qty, $from, $to, false);

        $this->assertEquals($expected, $actual);
    }

    public function callConversionProvider(): array
    {
        return [
            "100cm"  => ["1", "m", "cm", "100"],
            "no qty" => ["", "m", "cm", "100"],
        ];
    }

    /**
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testCallToBaseReturnWrongUnit(): void
    {
        $ucum = $this->mockUcumReturnWrongUnit();

        $actual = $ucum->callToBase("lorem", false);

        $this->assertEquals("mod-ucum-wrong_unit", $actual);
    }

    /**
     * @param string|array $units
     *
     * @throws InvalidArgumentException
     * @throws CouldNotGetCache
     * @throws Exception
     * @dataProvider callToBaseProvider
     * @config       ucum general path_to_base /toBaseUnits
     */
    public function testCallToBaseReturnExpectedData($units, string $expected): void
    {
        $ucum = $this->mockUcumReturnObject();

        $actual = $ucum->callToBase($units, false);

        $this->assertEquals($expected, $actual);
    }

    public function callToBaseProvider(): array
    {
        return [
            "metre" => ["cm", "m"],
            "array" => [["cm"], "m"],
        ];
    }
}
