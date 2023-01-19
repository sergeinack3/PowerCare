<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Auth\Checkers;

use Ox\Core\Auth\Badges\IncrementLoginAttemptsBadge;
use Ox\Core\Auth\Badges\LogAuthBadge;
use Ox\Core\Auth\Badges\WeakPasswordBadge;

/**
 * Helper for some CredentialsCheckerInterface
 */
trait CredentialsCheckerTrait
{
    /** @var string */
    protected $method;

    /** @var LogAuthBadge|null */
    private $log_auth_badge;

    /** @var IncrementLoginAttemptsBadge|null */
    private $increment_badge;

    /** @var WeakPasswordBadge|null */
    private $weak_password_badge;

    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * @param LogAuthBadge $badge
     *
     * @return $this
     */
    public function setLogAuthBadge(LogAuthBadge $badge): CredentialsCheckerInterface
    {
        $this->log_auth_badge = $badge;

        return $this;
    }

    /**
     * @param IncrementLoginAttemptsBadge $badge
     *
     * @return $this
     */
    public function setIncrementLogAttemptsBadge(IncrementLoginAttemptsBadge $badge): CredentialsCheckerInterface
    {
        $this->increment_badge = $badge;

        return $this;
    }

    /**
     * @param WeakPasswordBadge $badge
     *
     * @return $this
     */
    public function setWeakPasswordBadge(WeakPasswordBadge $badge): CredentialsCheckerInterface
    {
        $this->weak_password_badge = $badge;

        return $this;
    }

    private function setLogMethod(): void
    {
        if ($this->log_auth_badge === null || $this->method === null) {
            return;
        }

        $this->log_auth_badge->setMethod($this->method);
    }
}
