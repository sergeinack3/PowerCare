<?php

/**
 * @package Mediboard\Admin\Tests
 * @author  SARL OpenXtrem <dev@openxtrem.com>
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

namespace Ox\Mediboard\Admin\Tests\Fixtures;

use Ox\Core\CMbObject;
use Ox\Core\CModelObjectException;
use Ox\Core\CStoredObject;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CPermModule;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;

/**
 * Fixtures pour la grille des droits utilisateurs
 */
class UsersModulesPermissionsFixtures extends Fixtures
{
    /** @var string */
    public const REF_USER_PERMISSIONS       = "user_permissions";

    /**
     * @inheritDoc
     * @throws FixturesException
     * @throws CModelObjectException
     */
    public function load()
    {
        $module = CModule::getInstalled("system");
        $profil = $this->createProfil();
        $user   = $this->createUser($profil);
        $this->createFonction($user);
        $this->createPermission($user, new CModule());
        $this->createPermission($profil, new CModule());
        $this->createPermission($user, $module);
        $this->createPermission($profil, $module);
    }

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     */
    public function createProfil(): CUser
    {
        /** @var CUser $profil */
        $profil            = CStoredObject::getSampleObject(CUser::class);
        $profil->user_type = 2;
        $profil->template  = 1;
        $this->store($profil);

        return $profil;
    }

    /**
     * @param CUser $profil
     *
     * @return CMediusers
     * @throws FixturesException
     */
    private function createUser(CUser $profil): CMediusers
    {
        $user              = $this->getUser(false);
        $user->_profile_id = $profil->_id;
        $this->store($user, self::REF_USER_PERMISSIONS);

        return $user;
    }

    /**
     * @throws FixturesException
     */
    public function createFonction(CMediusers $user): void
    {
        $function = $user->loadRefFunction();
        $this->addReference($function);
        $this->addReference($function->loadRefGroup());
    }

    /**
     * @throws FixturesException
     */
    private function createPermission(CMbObject $user, CModule $module): void
    {
        $permMod             = new CPermModule();
        $permMod->permission = 0;
        $permMod->view       = 0;
        $permMod->user_id    = $user->_id;
        $permMod->mod_id     = $module->_id;
        $this->store($permMod);
    }
}
