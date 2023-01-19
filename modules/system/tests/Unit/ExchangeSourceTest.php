<?php

namespace Ox\Mediboard\System\Tests\Unit;

use Ox\Interop\Ftp\CSourceFTP;
use Ox\Mediboard\System\CExchangeSourceAdvanced;
use Ox\Mediboard\System\CExchangeSourceStatistic;
use Ox\Tests\OxUnitTestCase;

class ExchangeSourceTest extends OxUnitTestCase
{
    /** @var CSourceFTP */
    protected static $source;

    /** @var CExchangeSourceStatistic */
    protected static $stat;

    public function providerGetBlockedStatusWhenSourceAvailable(): array
    {
        return [
            'test 1 - max_retry = 20 > failure = 0' => [
                "retry_strategy" => '1|5 5|60 10|120 20|',
                "failures"       => 0,
            ],
            'test 2 - max_retry = 20 > failure = 1' => [
                "retry_strategy" => '1|5 5|60 10|120 20|',
                "failures"       => 1,
            ],
            'test 3 - max_retry = 20 > failure = 5' => [
                "retry_strategy" => '1|5 5|60 10|120 20|',
                "failures"       => 5,
            ],
            'test 4 - max_retry = 20 > failure = 19' => [
                "retry_strategy" => '1|5 5|60 10|120 20|',
                "failures"       => 19,
            ],
        ];
    }

    /**
     * @dataProvider providerGetBlockedStatusWhenSourceAvailable
     *
     * @return void
     */
    public function testGetBlockedStatusWhenSourceAvailable(string $retry_strategy, int $failures): void
    {
        $source                 = $this->getMockBuilder(CExchangeSourceAdvanced::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadRefLastStatistic'])
            ->getMock();
        $source->retry_strategy = $retry_strategy;


        $stat            = new CExchangeSourceStatistic();
        $stat->failures  = $failures;

        $source->method('loadRefLastStatistic')->willReturn($stat);

        $blockedStatus = $source->getBlockedStatus();
        $this->assertFalse($blockedStatus);
    }

    public function providerGetBlockedStatusWhenSourceBlocked(): array
    {
        return [
            'test 1 - max_retry = 20 > failure = 20'  => [
                "retry_strategy" => '1|5 5|60 10|120 20|',
                "failures"       => 20,
            ],
            'test 2 - max_retry = 20 > failure = 21'  => [
                "retry_strategy" => '1|5 5|60 10|120 20|',
                "failures"       => 21,
            ],
            'test 3 - max_retry = 20 > failure = 30'  => [
                "retry_strategy" => '1|5 5|60 10|120 20|',
                "failures"       => 30,
            ],
            'test 4 - max_retry = 89 > failure = 100' => [
                "retry_strategy" => '1|5 5|60 10|120 89|',
                "failures"       => 100,
            ],
        ];
    }

    /**
     * @dataProvider providerGetBlockedStatusWhenSourceBlocked
     *
     * @return void
     */
    public function testGetBlockedStatusWhenSourceBlocked(string $retry_strategy, int $failures): void
    {
        $source                 = $this->getMockBuilder(CExchangeSourceAdvanced::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadRefLastStatistic'])
            ->getMock();
        $source->retry_strategy = $retry_strategy;


        $stat            = new CExchangeSourceStatistic();
        $stat->failures  = $failures;

        $source->method('loadRefLastStatistic')->willReturn($stat);

        $blockedStatus = $source->getBlockedStatus();
        $this->assertTrue($blockedStatus);
    }
}
