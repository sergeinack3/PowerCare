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
use Ox\Core\Auth\Exception\CredentialsCheckException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Interface for check a given user's credentials
 */
interface CredentialsCheckerInterface
{
    /**
     * @param string        $password
     * @param UserInterface $user
     *
     * @return bool
     * @throws CredentialsCheckException
     */
    public function check(string $password, UserInterface $user): bool;

    /**
     * @param LogAuthBadge $badge
     *
     * @return $this
     */
    public function setLogAuthBadge(LogAuthBadge $badge): CredentialsCheckerInterface;

    /**
     * @param IncrementLoginAttemptsBadge $badge
     *
     * @return $this
     */
    public function setIncrementLogAttemptsBadge(IncrementLoginAttemptsBadge $badge): CredentialsCheckerInterface;

    /**
     * @param WeakPasswordBadge $badge
     *
     * @return $this
     */
    public function setWeakPasswordBadge(WeakPasswordBadge $badge): CredentialsCheckerInterface;
}
