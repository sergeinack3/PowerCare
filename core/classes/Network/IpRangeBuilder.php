<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Network;

use Ox\Core\CMbException;

/**
 * Simple IP range builder.
 * Mostly for testability (DI) and maintainability purposes.
 */
class IpRangeBuilder
{
    /**
     * @param string $ip_range
     *
     * @return IpRange
     * @throws CMbException
     */
    public function build(string $ip_range): IpRange
    {
        return new IpRange(trim($ip_range));
    }
}
