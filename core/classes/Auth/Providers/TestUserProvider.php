<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Auth\Providers;

use Ox\Core\CMbModelNotFoundException;
use Ox\Mediboard\Admin\CUser;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * UserProvider for testing purposes
 */
class TestUserProvider extends UserProvider
{
    private const CI_USERNAME = 'PHPUnit';

    /**
     * @inheritDoc
     */
    public function loadUserByUsername(string $username)
    {
        return $this->loadUserByIdentifier(self::CI_USERNAME);
    }

    /**
     * @inheritDoc
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->getUser(self::CI_USERNAME);
    }

    /**
     * @param UserInterface $user
     *
     * @return CUser
     */
    public function loadOxUser(UserInterface $user): CUser
    {
        $this->checkSupport($user);

        return $this->loadOxUserByUsername(self::CI_USERNAME);
    }

    /**
     * @param string $id
     *
     * @return CUser
     * @throws CMbModelNotFoundException
     */
    public function loadOxUserById(string $id): CUser
    {
        $user = CUser::findOrFail($id);

        if ($user->user_username !== self::CI_USERNAME) {
            throw new CMbModelNotFoundException('common-error-Object not found');
        }

        return $user;
    }
}
