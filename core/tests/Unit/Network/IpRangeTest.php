<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Network;

use Ox\Core\CMbException;
use Ox\Core\Network\IpRange;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class IpRangeTest extends OxUnitTestCase
{
    public function validSubnetDataProvider(): array
    {
        return [
            'valid subnet'                 => ['127.0.0.0/8'],
            'low boundary subnet mask'     => ['127.0.0.0/0'],
            'high boundary subnet mask'    => ['127.0.0.0/32'],
            'missing subnet (assuming 32)' => ['127.0.0.0'],
        ];
    }

    public function invalidSubnetDataProvider(): array
    {
        return [
            'invalid ip'                     => ['127.0', CMbException::class],
            'invalid ip syntax'              => ['subnet', CMbException::class],
            'out of range ip'                => ['999.999.999.999', CMbException::class],
            'empty ip'                       => ['', CMbException::class],
            'plain text'                     => ['localhost', CMbException::class],
            'invalid subnet mask'            => ['127.0.0.0/mask', CMbException::class],
            'out or range (up) subnet mask'  => ['127.0.0.0/64', CMbException::class],
            'out or range (low) subnet mask' => ['127.0.0.0/-1', CMbException::class],
            'invalid subnet syntax 1'        => ['127.0.0.0@8', CMbException::class],
            'invalid subnet syntax 2'        => ['127.0.0.0//8', CMbException::class],
            'invalid subnet syntax 3'        => ['127.0.0.0/8/8', CMbException::class],
            'invalid subnet syntax 4'        => ['8/127.0.0.0', CMbException::class],
            'ipv6 address'                   => ['2001:0db8:85a3:0000:0000:8a2e:0370:7334', CMbException::class],
            'ipv6 subnet'                    => ['2001:0db8:85a3:0000:0000:8a2e:0370:7334/64', CMbException::class],
            'ipv6 loopback'                  => ['::1', CMbException::class],
        ];
    }

    public function validIpRangeMatches(): array
    {
        return [
            'ip is a valid subnet host'  => ['127.0.0.0/8', '127.0.0.1'],
            'low boundary ip'            => ['127.0.0.0/8', '127.0.0.0'],
            'medium boundary ip'         => ['127.0.0.0/8', '127.127.127.127'],
            'high boundary ip'           => ['127.0.0.0/8', '127.255.255.255'],
            '::1 special case'           => ['127.0.0.0/8', '::1'],
            '::1 is 127.0.0.1'           => ['127.0.0.1/32', '::1'],
            'low boundary custom range'  => ['127.0.0.24/29', '127.0.0.24'],
            'high boundary custom range' => ['127.0.0.24/29', '127.0.0.31'],
            '0.0.0.0/0'                  => ['0.0.0.0/0', '1.2.3.4'],
        ];
    }

    public function invalidIpRangeMatches(): array
    {
        return [
            'ip is not a valid subnet host' => ['127.0.0.0/8', '192.168.0.1'],
            'out of range (low) ip'         => ['127.0.0.0/8', '126.255.255.255'],
            'out of range (high) ip'        => ['127.0.0.0/8', '128.0.0.0'],
            '::1 special case'              => ['192.168.0.0/16', '::1'],
            '::1 is 127.0.0.1 (low)'        => ['127.0.0.0/32', '::1'],
            '::1 is 127.0.0.1 (high)'       => ['127.0.0.2/32', '::1'],
            'low boundary custom range'     => ['127.0.0.24/29', '127.0.0.23'],
            'high boundary custom range'    => ['127.0.0.24/29', '127.0.0.32'],
        ];
    }

    public function invalidIpDataProvider(): array
    {
        return [
            'invalid ip'      => ['127.0', CMbException::class],
            'string'          => ['ip', CMbException::class],
            'out of range ip' => ['999.999.999.999', CMbException::class],
            'empty ip'        => ['', CMbException::class],
            'plain text'      => ['localhost', CMbException::class],
            'subnet'          => ['127.0.0.1/32', CMbException::class],
            'ipv6 address'    => ['2001:0db8:85a3:0000:0000:8a2e:0370:7334', CMbException::class],
            'ipv6 subnet'     => ['2001:0db8:85a3:0000:0000:8a2e:0370:7334/64', CMbException::class],
        ];
    }

    /**
     * @dataProvider invalidSubnetDataProvider
     *
     * @param string      $ip_range
     * @param string|null $exception_class
     *
     * @throws CMbException
     */
    public function testInstantiationWithInvalidIpThrowAnException(string $ip_range, string $exception_class): void
    {
        $this->expectException($exception_class);

        new IpRange($ip_range);
    }

    /**
     * @dataProvider validSubnetDataProvider
     *
     * @param string $ip_range
     *
     * @throws CMbException
     */
    public function testConstruct(string $ip_range): void
    {
        $this->expectNotToPerformAssertions();

        new IpRange($ip_range);
    }

    /**
     * @dataProvider validIpRangeMatches
     *
     * @param string $subnet
     * @param string $ip
     *
     * @throws CMbException
     */
    public function testMatchesIsCorrect(string $subnet, string $ip): void
    {
        $ip_range = new IpRange($subnet);

        $this->assertTrue($ip_range->matches($ip));
    }

    /**
     * @dataProvider invalidIpRangeMatches
     *
     * @param string $subnet
     * @param string $ip
     *
     * @throws CMbException
     */
    public function testMatchesIsIncorrect(string $subnet, string $ip): void
    {
        $ip_range = new IpRange($subnet);

        $this->assertFalse($ip_range->matches($ip));
    }

    /**
     * @dataProvider invalidIpDataProvider
     *
     * @param string $ip
     * @param string $exception_class
     *
     * @throws CMbException
     */
    public function testMatchesWithInvalidIpThrowAnException(string $ip, string $exception_class): void
    {
        $this->expectException($exception_class);

        $ip_range = new IpRange('127.0.0.0/8');
        $ip_range->matches($ip);
    }
}
