<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Auth\Checkers;

use Ox\Core\Auth\User;
use Ox\Core\Security\Crypt\Hash;
use Ox\Core\Security\Crypt\Hasher;
use Ox\Mediboard\System\CUserAuthentication;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Will check the credentials against the database.
 */
class StandardCredentialsChecker implements CredentialsCheckerInterface
{
    use CredentialsCheckerTrait;

    /** @var Hasher */
    private $hasher;

    /**
     * @param Hasher $hasher
     */
    public function __construct(Hasher $hasher)
    {
        $this->hasher = $hasher;
        $this->method = CUserAuthentication::AUTH_METHOD_STANDARD;
    }

    /**
     * @inheritDoc
     */
    public function check(string $password, UserInterface $user): bool
    {
        $this->setLogMethod();

        if (!$user instanceof User) {
            return false;
        }

        $ox_user = $user->getOxUser();

        // Only check the weak password spec with standard login
        if ($this->weak_password_badge !== null && $ox_user->checkPasswordWeakness($password)) {
            $this->weak_password_badge->enable();
        }

        return hash_equals(
            $this->hasher->hash(Hash::SHA256(), $ox_user->user_salt . $password),
            $ox_user->user_password,
        );
    }
}
