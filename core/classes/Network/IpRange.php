<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Network;

use Ox\Core\CMbException;

/**
 * IPv4 subnet matching class.
 */
class IpRange
{
    private const LOOPBACK = [
        '::1',
    ];

    private const MAX_SUBNET_MASK = 32;

    /** @var string */
    private $ip_range;

    /** @var int */
    private $subnet;

    /** @var int */
    private $subnet_mask;

    /**
     * @param string $ip_range A dotted notation IP subnet (with CIDR mask).
     *
     * @throws CMbException
     */
    public function __construct(string $ip_range)
    {
        if (strpos($ip_range, '/') === false) {
            $subnet      = $ip_range;
            $subnet_mask = self::MAX_SUBNET_MASK;
        } else {
            [$subnet, $subnet_mask] = explode('/', $ip_range, 2);
        }

        $subnet = self::convertToLongIp($subnet);

        if (!is_numeric($subnet_mask) || ($subnet_mask < 0 || $subnet_mask > self::MAX_SUBNET_MASK)) {
            throw new CMbException('IpRange-error-Invalid subnet mask provided: %s', $subnet_mask);
        }

        $this->ip_range    = $ip_range;
        $this->subnet      = $subnet;
        $this->subnet_mask = (int)$subnet_mask;
    }

    /**
     * Tell whether a IP address matches the IP range.
     *
     * @param string $ip Dotted notation IP address.
     *
     * @return bool
     * @throws CMbException
     *
     * @see https://stackoverflow.com/a/594134/1537229
     */
    public function matches(string $ip): bool
    {
        if (in_array($ip, self::LOOPBACK, true)) {
            $ip = '127.0.0.1';
        }

        $ip = self::convertToLongIp($ip);

        $mask = -1 << (self::MAX_SUBNET_MASK - $this->subnet_mask);

        return (($ip & $mask) === $this->subnet);
    }

    /**
     * Convert an IP address to integer.
     *
     * @param string $ip The dotted notation IP address to check.
     *
     * @return int
     * @throws CMbException
     */
    private static function convertToLongIp(string $ip): int
    {
        $ipv4 = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);

        // IPv6 is not handled.
        if ($ipv4 === false) {
            throw new CMbException('IpRange-error-Invalid provided IP: %s', $ip);
        }

        return ip2long($ip);
    }
}
