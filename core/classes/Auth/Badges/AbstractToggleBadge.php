<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Auth\Badges;

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

/**
 * Describes badge which can to toggle on/off.
 */
abstract class AbstractToggleBadge implements BadgeInterface
{
    /** @var bool */
    protected $enabled = true;

    /**
     * Enables badge.
     *
     * @return $this
     */
    public function enable(): self
    {
        $this->enabled = true;

        return $this;
    }

    /**
     * Disables badge.
     *
     * @return $this
     */
    public function disable(): self
    {
        $this->enabled = false;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isResolved(): bool
    {
        // Does not need to be explicitly resolved
        return true;
    }
}
