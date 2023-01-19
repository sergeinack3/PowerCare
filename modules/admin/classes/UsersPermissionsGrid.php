<?php

/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Prepare la grille des droits
 */
class UsersPermissionsGrid
{
    /**
     * Tableau des droits
     *
     * @var array|string[]
     */
    private const PERMS = [
        PERM_DENY => "CPermModule.permission.0",
        PERM_READ => "CPermModule.permission.1",
        PERM_EDIT => "CPermModule.permission.2",
    ];

    /**
     * Tableau des type de visibilité de menu
     *
     * @var array|string[]
     */
    private const VIEWS = [
        PERM_DENY => "CPermModule.view.0",
        PERM_READ => "CPermModule.view.1",
        PERM_EDIT => "CPermModule.view.2",
    ];

    /**
     * Icônes
     *
     * @var array|string[]
     */
    private const ICONS = [
        PERM_DENY => 'empty',
        PERM_READ => 'read',
        PERM_EDIT => 'edit',
    ];

    /**
     * Liste des fonctions
     *
     * @var array
     */
    private array $list_functions = [];

    /**
     * Liste des profils
     *
     * @var array
     */
    private array $profiles = [];

    /**
     * Users Matrix
     *
     * @var array
     */
    private array $matrix = [];

    /**
     * Profiles matrix
     *
     * @var array
     */
    private array $matrix_profiles = [];


    /**
     * @param CGroups $group
     * @param bool    $only_profil
     * @param bool    $only_user
     * @param array   $profiles_ids
     * @param array   $user_ids
     * @param array   $list_modules
     *
     * @throws Exception
     */
    public function __construct(
        CGroups $group,
        bool $only_profil,
        bool $only_user,
        array $profiles_ids = [],
        array $user_ids = [],
        array $list_modules = []
    ) {
        if (!$only_profil) {
            $this->loadProfilesMatrix($profiles_ids, $list_modules);
        }
        if (!$only_user) {
            $this->loadUsersMatrix($user_ids, $list_modules, $group);
        }
    }

    /**
     * Get profile matrix
     *
     * @param array $profiles_ids
     * @param array $list_modules
     *
     * @return array
     * @throws Exception
     */
    public function loadProfilesMatrix(array $profiles_ids, array $list_modules): void
    {
        $this->matrix_profiles = [];
        $where_profil          = [];
        if (count($profiles_ids)) {
            $where_profil['user_id'] = CSQLDataSource::prepareIn(array_keys($profiles_ids));
        }

        $this->profiles = CUser::getProfiles($where_profil);

        $where         = ['mod_id' => 'IS NOT NULL'];
        $where_general = ['mod_id' => 'IS NULL'];

        foreach ($this->profiles as $profile) {
            foreach ($list_modules as $curr_mod) {
                $this->matrix_profiles[$profile->_id][$curr_mod->_id] = [
                    'text'     => CAppUI::tr(self::PERMS[PERM_DENY]) . '/' . CAppUI::tr(self::VIEWS[PERM_DENY]),
                    'type'     => 'général',
                    'permIcon' => CAppUI::tr(self::ICONS[PERM_DENY]),
                    'viewIcon' => CAppUI::tr(self::ICONS[PERM_DENY]),
                ];
            }
        }

        $profil_perm_module = new CPermModule();

        $where_general['user_id'] = CSQLDataSource::prepareIn(array_keys($this->profiles));
        $where['user_id']         = $where_general['user_id'];
        $list_profil_perm_modules = $profil_perm_module->loadList($where);

        $profil_perm_modules = $profil_perm_module->loadList($where_general);

        foreach ($profil_perm_modules as $perm_module) {
            $profil_perm_general_permission = $perm_module->permission;
            $profil_perm_general_view       = $perm_module->view;
            foreach ($list_modules as $module) {
                $this->matrix_profiles[$perm_module->user_id][$module->_id] = [
                    'text'     => CAppUI::tr(self::PERMS[$profil_perm_general_permission])
                        . '/' . CAppUI::tr(self::VIEWS[$profil_perm_general_view]),
                    'type'     => 'général',
                    'permIcon' => CAppUI::tr(self::ICONS[$profil_perm_general_permission]),
                    'viewIcon' => CAppUI::tr(self::ICONS[$profil_perm_general_view]),
                ];
            }
        }

        foreach ($list_profil_perm_modules as $curr_perm) {
            $this->matrix_profiles[$curr_perm->user_id][$curr_perm->mod_id] = [
                'text'     => CAppUI::tr(self::PERMS[$curr_perm->permission])
                    . '/' . CAppUI::tr(self::VIEWS[$curr_perm->view]),
                'type'     => 'spécifique',
                'permIcon' => CAppUI::tr(self::ICONS[$curr_perm->permission]),
                'viewIcon' => CAppUI::tr(self::ICONS[$curr_perm->view]),
            ];
        }
    }

    /**
     * Get user matrix
     *
     * @param array $users_ids    liste des user id
     * @param array $list_modules liste des modules
     *
     * @return array
     * @throws Exception
     * @throws Exception
     */
    public function loadUsersMatrix(array $users_ids, array $list_modules, CGroups $group): void
    {
        $this->matrix = [];
        $where        = [];
        $ljoin        = [];
        if (count($users_ids) !== 0) {
            $ljoin = ['users_mediboard' => 'users_mediboard.function_id = functions_mediboard.function_id'];

            $where['users_mediboard.user_id'] = CSQLDataSource::prepareIn(array_keys($users_ids));
        }
        // Liste des utilisateurs
        $this->list_functions = CMediusers::loadFonctions(PERM_READ, $group->_id, null, null, $where, $ljoin) ?? [];

        $where   = [
            'actif' => '= "1"',
        ];
        $where[] = CSQLDataSource::get("std")->prepare('fin_activite IS NULL OR fin_activite>= NOW()');
        $where[] = CSQLDataSource::get("std")->prepare('deb_activite IS NULL OR deb_activite <= NOW()');

        if (count($users_ids) !== 0) {
            $where['users_mediboard.user_id'] = CSQLDataSource::prepareIn(array_keys($users_ids));
        }

        $ljoin = ['users' => 'users.user_id = users_mediboard.user_id'];
        $order = 'user_last_name, user_first_name';
        $users = CStoredObject::massLoadBackRefs($this->list_functions, 'users', $order, $where, $ljoin);
        CStoredObject::massLoadFwdRef($users, '_profile_id');

        foreach ($this->list_functions as $curr_function) {
            foreach ($curr_function->loadRefsUsers() as $user) {
                $user->loadRefProfile();
            }
        }

        $this->computeMatrixFromUsers($users, $list_modules);
    }

    /**
     * Getter list_functions
     *
     * @return array
     */
    public function getListFunctions(): array
    {
        return $this->list_functions;
    }

    /**
     * Getter profiles
     *
     * @return array
     */
    public function getProfiles(): array
    {
        return $this->profiles;
    }

    /**
     * Getter matrix
     *
     * @return array
     */
    public function getMatrix(): array
    {
        return $this->matrix;
    }

    /**
     * Getter matrix_profiles
     *
     * @return array
     */
    public function getMatrixProfiles(): array
    {
        return $this->matrix_profiles;
    }

    /**
     * @throws Exception
     */
    private function computeMatrixFromUsers(?array $users, array $list_modules): void
    {
        $mapping_user_profile = [];

        foreach ($users as $user) {
            /** @var $user CUser|CMediusers */
            $mapping_user_profile[$user->_profile_id][] = $user->_id;
        }

        foreach ($users as $curr_user) {
            foreach ($list_modules as $module) {
                $this->matrix[$curr_user->_id][$module->_id] = [
                    'text'     => CAppUI::tr(self::PERMS[PERM_DENY]) . '/' . CAppUI::tr(self::VIEWS[PERM_DENY]),
                    'type'     => 'général',
                    'permIcon' => CAppUI::tr(self::ICONS[PERM_DENY]),
                    'viewIcon' => CAppUI::tr(self::ICONS[PERM_DENY]),
                ];
            }
        }

        $where = [];

        $perm_module = new CPermModule();

        $where['user_id']   = CSQLDataSource::prepareIn(CMbArray::pluck($users, '_id'));
        $list_perms_modules = $perm_module->loadList($where, 'mod_id');

        $where['user_id']          = CSQLDataSource::prepareIn(CMbArray::pluck($users, '_profile_id'));
        $list_perms_modules_profil = $perm_module->loadList($where, 'mod_id');

        foreach ($list_perms_modules_profil as $curr_perm) {
            foreach ($mapping_user_profile[$curr_perm->user_id] as $user_id) {
                $sub_perm = [
                    'text'     => CAppUI::tr(self::PERMS[$curr_perm->permission])
                        . '/' . CAppUI::tr(self::VIEWS[$curr_perm->view]),
                    'type'     => 'profil',
                    'permIcon' => CAppUI::tr(self::ICONS[$curr_perm->permission]),
                    'viewIcon' => CAppUI::tr(self::ICONS[$curr_perm->view]),
                ];
                if ($curr_perm->mod_id) {
                    $this->matrix[$user_id][$curr_perm->mod_id] = $sub_perm;
                    continue;
                }
                if (array_key_exists($user_id, $this->matrix)) {
                    foreach ($this->matrix[$user_id] as $mod_id => $matrice) {
                        $this->matrix[$user_id][$mod_id] = $sub_perm;
                    }
                }
            }
        }
        foreach ($list_perms_modules as $curr_perm) {
            $sub_perm = [
                'text'     => CAppUI::tr(self::PERMS[$curr_perm->permission])
                    . '/' . CAppUI::tr(self::VIEWS[$curr_perm->view]),
                'type'     => 'spécifique',
                'permIcon' => CAppUI::tr(self::ICONS[$curr_perm->permission]),
                'viewIcon' => CAppUI::tr(self::ICONS[$curr_perm->view]),
            ];

            if ($curr_perm->mod_id) {
                $this->matrix[$curr_perm->user_id][$curr_perm->mod_id] = $sub_perm;
                continue;
            }

            if (array_key_exists($curr_perm->user_id, $this->matrix)) {
                foreach ($this->matrix[$curr_perm->user_id] as $mod_id => $matrice) {
                    $this->matrix[$curr_perm->user_id][$mod_id] = $sub_perm;
                }
            }
        }
    }
}
