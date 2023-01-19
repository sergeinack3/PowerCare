<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Network;

use Ox\Core\CMbException;
use Ox\Core\Network\IpRange;
use Ox\Core\Network\IpRangeBuilder;
use Ox\Core\Network\IpRangeMatcher;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class IpRangeMatcherTest extends OxUnitTestCase
{
    public function matchesProvider(): array
    {
        return [
            '1/true 2/false'  => [true, false, true],
            '1/true 2/true'   => [true, true, true],
            '1/false 2/true'  => [false, true, true],
            '1/false 2/false' => [false, false, false],
        ];
    }

    /**
     * @param array  $ranges
     * @param string $exception_class
     *
     * @throws CMbException
     */
    public function testInstantiationWithInvalidRangeThrowsAnException(): void
    {
        $builder = $this->getMockBuilder(IpRangeBuilder::class)
            ->getMock();

        $exception = new CMbException('test case');
        $builder->expects($this->once())->method('build')->willThrowException($exception);

        $this->expectExceptionObject($exception);
        new IpRangeMatcher(['range'], $builder);
    }

    /**
     * @dataProvider matchesProvider
     *
     * @param bool $first_range_return
     * @param bool $second_range_return
     * @param bool $expected_return
     *
     * @throws CMbException
     */
    public function testMatchesIsCorrect(
        bool $first_range_return,
        bool $second_range_return,
        bool $expected_return
    ): void {
        $builder = $this->getMockBuilder(IpRangeBuilder::class)
            ->getMock();

        $first_ip_range = $this->getMockBuilder(IpRange::class)
            ->disableOriginalConstructor()
            ->getMock();
        $first_ip_range->expects($this->once())->method('matches')->willReturn($first_range_return);

        $second_ip_range = $this->getMockBuilder(IpRange::class)
            ->disableOriginalConstructor()
            ->getMock();
        $second_ip_range->expects($this->any())->method('matches')->willReturn($second_range_return);

        $builder->expects($this->any())
            ->method('build')
            ->willReturnOnConsecutiveCalls(
                $first_ip_range,
                $second_ip_range
            );

        $matcher = new IpRangeMatcher(['range 1/', 'range 2/'], $builder);
        $this->assertEquals($expected_return, $matcher->matches('ip'));
    }
}
