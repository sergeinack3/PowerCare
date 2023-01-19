<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Auth;

use InvalidArgumentException;
use Ox\Mediboard\Admin\CUser;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Acts as a bridge between UserInterface and the legacy CUser class
 */
class User implements UserInterface
{
    private const USER_ROLE     = 'ROLE_USER';
    private const USER_API_ROLE = 'ROLE_API_USER';

    /** @var string */
    private $username;

    /** @var array */
    private $roles = [];

    /** @var string The hashed password */
    private $password;

    /** @var CUser */
    private $ox_user;

    /**
     * @param CUser $user
     *
     * @throws InvalidArgumentException
     */
    public function __construct(CUser $user)
    {
        if (!$user->_id || !$user->user_username) {
            throw new InvalidArgumentException('Invalid user provided');
        }

        $this->username = $user->user_username;
        $this->ox_user  = $user;
    }

    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = self::USER_ROLE;
        $roles[] = self::USER_API_ROLE;

        return array_unique($roles);
    }

    /**
     * @inheritDoc
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->getUserIdentifier();
    }

    /**
     * @inheritDoc
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getOxUser(): CUser
    {
        return $this->ox_user;
    }

    public function equals(self $other): bool
    {
        return $this->username === $other->username
            && $this->password === $other->password
            && $this->getRoles() === $other->getRoles()
            && $this->ox_user->_id === $other->ox_user->_id;
    }
}
