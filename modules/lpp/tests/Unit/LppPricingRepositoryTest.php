<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Lpp\Tests\Unit;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CPDOMySQLDataSource;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Lpp\CLPPDatedPricing;
use Ox\Mediboard\Lpp\Exceptions\LppDatabaseException;
use Ox\Mediboard\Lpp\Repository\LppPricingRepository;
use Ox\Tests\OxUnitTestCase;

class LppPricingRepositoryTest extends OxUnitTestCase
{
    public function getPricingFromDateQueryProvider(): array
    {
        return [
            ['2140455', '2020-01-01'],
            ['2140455', null],
        ];
    }

    /**
     * @dataProvider getPricingFromDateQueryProvider
     */
    public function testGetPricingFromDateQuery(string $code, ?string $date): void
    {
        LppPricingRepository::setDatasource(CSQLDataSource::get('std'));
        $query = LppPricingRepository::getInstance()->getPricingFromDateQuery($code, $date);

        if (!$date) {
            $date = CMbDT::date();
        }

        $expected = "SELECT *
FROM `histo`
WHERE (`CODE_TIPS` = '{$code}')
AND (`DEBUTVALID` <= '{$date}')
AND (`FINHISTO` >= '{$date}' OR `FINHISTO` IS NULL)";

        $this->assertEquals($expected, $query->makeSelect());
    }

    public function testGetPricingsForCodeQuery(): void
    {
        $code = '2140455';

        LppPricingRepository::setDatasource(CSQLDataSource::get('std'));
        $query = LppPricingRepository::getInstance()->getPricingsForCodeQuery($code);

        $expected = "SELECT *
FROM `histo`
WHERE (`CODE_TIPS` = '{$code}')
ORDER BY `DEBUTVALID` DESC";

        $this->assertEquals($expected, $query->makeSelect());
    }

    public function getLastPricingForCodeQueryProvider(): array
    {
        return [
            [
                '1449860',
                '2020-01-01',
                "SELECT *
FROM `histo`
WHERE (`CODE_TIPS` = '1449860')
AND (`DEBUTVALID` <= '2020-01-01')
AND (`FINHISTO` >= '2020-01-01' OR `FINHISTO` IS NULL)
ORDER BY `DEBUTVALID` DESC
LIMIT 0, 1"
            ],
            [
                '1449860',
                null,
                "SELECT *
FROM `histo`
WHERE (`CODE_TIPS` = '1449860')
ORDER BY `DEBUTVALID` DESC
LIMIT 0, 1"
            ],
        ];
    }

    /**
     * @dataProvider getLastPricingForCodeQueryProvider
     */
    public function testGetLastPricingForCodeQuery(string $code, ?string $date, string $expected): void
    {
        LppPricingRepository::setDatasource(CSQLDataSource::get('std'));
        $query = LppPricingRepository::getInstance()->getLastPricingForCodeQuery($code, $date);

        $this->assertEquals($expected, $query->makeSelect());
    }

    public function testLoadFromDateDatabaseError(): void
    {
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadHash', 'prepare'])
            ->getMock();

        $ds->method('loadHash')->willThrowException(new Exception());
        $ds->method('prepare')->willReturn('');

        $this->expectException(LppDatabaseException::class);
        $this->expectErrorMessageMatches('/LppDatabaseException-error-database_error/');

        LppPricingRepository::setDatasource($ds);

        LppPricingRepository::getInstance()->loadFromDate('2140455', CMbDT::date());
    }

    public function testLoadFromDateInvalidResult(): void
    {
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadHash', 'prepare'])
            ->getMock();

        $ds->method('loadHash')->willReturn(false);
        $ds->method('prepare')->willReturn('');

        $this->expectException(LppDatabaseException::class);
        $this->expectErrorMessageMatches('/LppDatabaseException-error-invalid_request_result/');

        LppPricingRepository::setDatasource($ds);

        LppPricingRepository::getInstance()->loadFromDate('2140455', CMbDT::date());
    }

    public function testLoadFromDateDatabase(): void
    {
        $pricing_data = [
            'CODE_TIPS' => '4222803',
            'DEBUTVALID' => '2003-09-08',
            'FINHISTO' => '2021-06-30',
            'NAT_PREST' => 'VEH',
            'ENTENTE' => 'N',
            'ARRETE' => '2003-06-26',
            'JO' => '2003-09-06',
            'PUDEVIS' => '0',
            'TARIF' => '631.17',
            'MAJO_DOM1' => '1.3',
            'MAJO_DOM2' => '1.15',
            'MAJO_DOM3' => '9.999',
            'MAJO_DOM4' => '1.4',
            'QTE_MAX' => '0',
            'MT_MAX' => '0',
            'PUREGLEMEN' => '0',
            'PECP01' => '00',
            'PECP02' => '00',
            'PECP03' => '00',
            'MAJO_DOM5' => '1',
            'MAJO_DOM6' => '1.36',
        ];

        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadHash', 'prepare'])
            ->getMock();

        $ds->method('loadHash')->willReturn($pricing_data);
        $ds->method('prepare')->willReturn('');

        LppPricingRepository::setDatasource($ds);

        $pricing = LppPricingRepository::getInstance()->loadFromDate('4222803');

        $this->assertEquals(new CLPPDatedPricing($pricing_data), $pricing);
    }

    public function testLoadListFromCodeDatabaseError(): void
    {
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadList', 'prepare'])
            ->getMock();

        $ds->method('loadList')->willThrowException(new Exception());
        $ds->method('prepare')->willReturn('');

        $this->expectException(LppDatabaseException::class);
        $this->expectErrorMessageMatches('/LppDatabaseException-error-database_error/');

        LppPricingRepository::setDatasource($ds);

        LppPricingRepository::getInstance()->loadListFromCode('2140455');
    }

    public function testLoadListFromCodeInvalidResult(): void
    {
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadList', 'prepare'])
            ->getMock();

        $ds->method('loadList')->willReturn(false);
        $ds->method('prepare')->willReturn('');

        $this->expectException(LppDatabaseException::class);
        $this->expectErrorMessageMatches('/LppDatabaseException-error-invalid_request_result/');

        LppPricingRepository::setDatasource($ds);

        LppPricingRepository::getInstance()->loadListFromCode('2140455');
    }

    public function testLoadLastPricingForCodeDatabaseError(): void
    {
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadHash', 'prepare'])
            ->getMock();

        $ds->method('loadHash')->willThrowException(new Exception());
        $ds->method('prepare')->willReturn('');

        $this->expectException(LppDatabaseException::class);
        $this->expectErrorMessageMatches('/LppDatabaseException-error-database_error/');

        LppPricingRepository::setDatasource($ds);

        LppPricingRepository::getInstance()->loadLastPricingForCode('2140455', CMbDT::date());
    }

    public function testloadLastPricingForCodeInvalidResult(): void
    {
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadHash', 'prepare'])
            ->getMock();

        $ds->method('loadHash')->willReturn(false);
        $ds->method('prepare')->willReturn('');

        $this->expectException(LppDatabaseException::class);
        $this->expectErrorMessageMatches('/LppDatabaseException-error-invalid_request_result/');

        LppPricingRepository::setDatasource($ds);

        LppPricingRepository::getInstance()->loadLastPricingForCode('2140455', CMbDT::date());
    }
}
