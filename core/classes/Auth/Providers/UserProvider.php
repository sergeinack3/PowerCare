<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Auth\Providers;

use Ox\Core\Auth\User;
use Ox\Core\CMbModelNotFoundException;
use Ox\Mediboard\Admin\CUser;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Enable us to get the User during Authentication.
 */
class UserProvider implements UserProviderInterface
{
    /**
     * @inheritDoc
     */
    public function refreshUser(UserInterface $user)
    {
        $this->checkSupport($user);

        return $this->getUser($user->getUserIdentifier());
    }

    /**
     * @inheritDoc
     */
    public function supportsClass(string $class)
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }

    /**
     * @inheritDoc
     */
    public function loadUserByUsername(string $username)
    {
        return $this->loadUserByIdentifier($username);
    }

    /**
     * @inheritDoc
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->getUser($identifier);
    }

    protected function getUser(string $username): UserInterface
    {
        $ox_user = $this->loadOxUserByUsername($username);

        return new User($ox_user);
    }

    /**
     * @param UserInterface $user
     *
     * @return CUser
     */
    public function loadOxUser(UserInterface $user): CUser
    {
        $this->checkSupport($user);

        return $this->loadOxUserByUsername($user->getUserIdentifier());
    }

    /**
     * @param string $username
     *
     * @return CUser
     * @throws UserNotFoundException
     */
    protected function loadOxUserByUsername(string $username): CUser
    {
        $user                = new CUser();
        $user->user_username = $username;
        $user->loadMatchingObjectEsc();

        if (!$username || !$user->_id) {
            throw new UserNotFoundException(sprintf('User %s not found', $username));
        }

        return $user;
    }

    /**
     * @param string $id
     *
     * @return CUser
     * @throws CMbModelNotFoundException
     */
    public function loadOxUserById(string $id): CUser
    {
        return CUser::findOrFail($id);
    }

    protected function checkSupport(UserInterface $user): void
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }
    }
}
