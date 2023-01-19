<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Network;

use Ox\Core\CMbException;

/**
 * IP range (subnet) matcher class.
 * Take an array of IPv4 subnets and tell if given IP address is validated against at least one of them.
 */
class IpRangeMatcher
{
    /** @var IpRange[] */
    private $ip_ranges;

    /**
     * @param array               $ip_ranges
     * @param IpRangeBuilder|null $builder
     *
     * @throws CMbException
     */
    public function __construct(array $ip_ranges, ?IpRangeBuilder $builder = null)
    {
        $builder = $builder ?? new IpRangeBuilder();

        foreach ($ip_ranges as $_ip_range) {
            $this->ip_ranges[] = $builder->build($_ip_range);
        }
    }

    /**
     * @param string $ip
     *
     * @return bool
     * @throws CMbException
     */
    public function matches(string $ip): bool
    {
        foreach ($this->ip_ranges as $_range) {
            if ($_range->matches($ip)) {
                return true;
            }
        }

        return false;
    }
}
