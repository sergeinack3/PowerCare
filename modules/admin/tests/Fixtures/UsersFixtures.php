<?php
/**
 * @package Mediboard\Admin\Tests
 * @author  SARL OpenXtrem <dev@openxtrem.com>
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

namespace Ox\Mediboard\Admin\Tests\Fixtures;

use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * @description Simple users fixtures
 */
class UsersFixtures extends Fixtures implements GroupFixturesInterface
{
    /** @var string */
    const REF_USER_LOREM_IPSUM  = 'lorem_ipsum';
    const REF_FIXTURES_FUNCTION = 'fixtures_function';
    const REF_FIXTURES_GROUP    = 'fixtures_group';

    const REF_USER_CHIR       = 'user_chir';
    const REF_USER_ANESTH     = 'user_anesth';
    const REF_USER_INFIRMIER  = 'user_infirmier';
    const REF_USER_SECRETAIRE = 'user_secretaire';
    const REF_USER_MEDECIN    = 'user_medecin';

    /**
     * @inheritDoc
     * @throws FixturesException
     */
    public function load()
    {
        $user                  = new CUser();
        $user->user_username   = 'lorem_ipsum';
        $user->user_first_name = 'lorem';
        $user->user_last_name  = 'ipsum';

        $this->store($user, static::REF_USER_LOREM_IPSUM);

        $user = $this->getUser(false);
        $this->store($user, self::REF_USER_LOREM_IPSUM);

        $function = $user->loadRefFunction();
        $this->addReference($function, static::REF_FIXTURES_FUNCTION);
        $this->addReference($function->loadRefGroup(), static::REF_FIXTURES_GROUP);

        // Generate different types of users
        $users = $this->getUsers(5, false);

        // Create chir user
        $chir             = array_pop($users);
        $chir->_user_type = 3;
        $this->store($chir, self::REF_USER_CHIR);

        $anesth             = array_pop($users);
        $anesth->_user_type = 4;
        $this->store($anesth, self::REF_USER_ANESTH);

        $inf             = array_pop($users);
        $inf->_user_type = 7;
        $this->store($inf, self::REF_USER_INFIRMIER);

        $secretaire             = array_pop($users);
        $secretaire->_user_type = 10;
        $this->store($secretaire, self::REF_USER_SECRETAIRE);

        $medecin             = array_pop($users);
        $medecin->_user_type = 13;
        $this->store($medecin, self::REF_USER_MEDECIN);
    }

    /**
     * @throws FixturesException
     */
    public function purge(): void
    {
        parent::purge();

        $user     = new CUser();
        $users_id = $user->loadIds(['user_last_name' => "like 'lorem%'"]);
        if ($msg = $user->deleteAll($users_id)) {
            throw new FixturesException($msg);
        }
    }

    /**
     * @return string[]
     */
    public static function getGroup(): array
    {
        return ['admin_user', 100];
    }
}
