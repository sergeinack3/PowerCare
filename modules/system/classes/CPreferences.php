<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * User preferences
 */
class CPreferences extends CMbObject
{
    public const RESOURCE_TYPE = "preference";

    static $modules = [];

    public $pref_id;

    public $user_id;
    public $key;
    public $value;
    public $restricted;

    /**
     * Load preferences files from each module
     *
     * @return void
     */
    static function loadModules($restricted = false)
    {
        $filename = ($restricted) ? "functional_perms" : "preferences";

        foreach (glob("./modules/*/{$filename}.php") as $file) {
            include_once $file;
        }
    }

    /**
     * Loads preferences from a specific module
     *
     * @param string $module     Module preferences to load
     * @param bool   $restricted If yes, loads functional permissions
     *
     * @return null
     */
    public static function loadModule($module, $restricted = false)
    {
        $filename = ($restricted) ? 'functional_perms' : 'preferences';
        $module   = preg_replace('/[^a-z0-9]/i', '', $module);

        if ($module) {
            if ($module === 'common') {
                $module = 'system';
            }

            $pref_file = dirname(__DIR__, 3) . "/modules/{$module}/{$filename}.php";
            if (file_exists($pref_file)) {
                include_once $pref_file;
            }
        }
    }

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec                     = parent::getSpec();
        $spec->table              = "user_preferences";
        $spec->key                = "pref_id";
        $spec->uniques["uniques"] = ["user_id", "key"];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props               = parent::getProps();
        $props["user_id"]    = "ref class|CUser cascade back|preferences";
        $props["key"]        = "str notNull maxLength|255 fieldset|default";
        $props["value"]      = "str fieldset|default";
        $props["restricted"] = "bool notNull default|0";

        return $props;
    }

    /**
     * Load user preferences as an associative array
     *
     * @param int  $user_id    The user to load the preferences from
     * @param bool $restricted Get functional permission
     *
     * @return array
     */
    public static function get(int $user_id = null, bool $restricted = false): array
    {
        $where["user_id"] = "IS NULL";
        if ($user_id) {
            $where["user_id"] = "= '$user_id'";
            $where["value"]   = "IS NOT NULL";
        }

        if ($restricted) {
            $where["restricted"] = "= '1'";
        }

        $preferences = [];
        $pref        = new self;

        /** @var self[] $list */
        $list = $pref->loadList($where);

        foreach ($list as $_pref) {
            $preferences[$_pref->key] = $_pref->value;
        }

        return $preferences;
    }

    /**
     * @inheritdoc
     */
    function loadRefsFwd()
    {
        $this->loadRefUser();
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();
        $this->_view = '[Pref] ' . CAppUI::tr("pref-$this->key");
    }

    /**
     * Load ref user
     *
     * @return CUser
     */
    function loadRefUser()
    {
        return $this->loadFwdRef("user_id", true);
    }

    /**
     * Load user preferences
     *
     * @param int  $user_id    The user to load the preferences from
     * @param bool $restricted Get functional permission
     *
     * @return array
     */
    public static function getAllPrefs(int $user_id, bool $restricted = false): array
    {
        // Default
        $preferences = CPreferences::get(null, $restricted);

        // Profile
        $user = new CUser();
        $user->load($user_id);
        if ($user->profile_id) {
            $preferences = array_merge($preferences, CPreferences::get($user->profile_id, $restricted));
        }

        // User
        $preferences = array_merge($preferences, CPreferences::get($user->_id, $restricted));

        return $preferences;
    }

    public static function getAllPrefsForList(CUser $user, array $pref_names, bool $restricted = false): array
    {
        // Default
        $preferences = CPreferences::getPrefValuesForList($pref_names, null, $restricted);

        // Profile
        if ($user->profile_id) {
            $preferences
                = array_merge($preferences, CPreferences::getPrefValuesForList($pref_names, $user->profile_id, $restricted));
        }

        // User
        return array_merge($preferences, CPreferences::getPrefValuesForList($pref_names, $user->_id, $restricted));
    }

    /**
     * Load user preferences
     *
     * @param CUser[]|CMediusers[] $users The user to load the preferences
     *
     * @return array
     */
    static function getAllPrefsUsers($users)
    {
        $preferences_users = [];
        // Default
        $preferences_default = CPreferences::get();
        $preferences_profile = [];

        foreach ($users as $_user) {
            if ($_user instanceof CMediusers) {
                $_user = $_user->_ref_user;
            }

            $preferences_user = $preferences_default;
            // Profile
            if ($_user->profile_id) {
                if (!isset($preferences_profile[$_user->profile_id])) {
                    $preferences_profile[$_user->profile_id] = array_merge(
                        $preferences_user,
                        CPreferences::get($_user->profile_id)
                    );
                }
                $preferences_user = $preferences_profile[$_user->profile_id];
            }

            // User
            $preferences_user               = array_merge($preferences_user, CPreferences::get($_user->_id));
            $preferences_users[$_user->_id] = $preferences_user;
        }

        return $preferences_users;
    }

    /**
     * Loads the given preference for one user
     *
     * @param string  $name    Preference name
     * @param integer $user_id User ID
     *
     * @return array|null
     */
    static function getPref($name, $user_id = null)
    {
        $preference = [
            'default' => null,
            'profile' => null,
            'user'    => null,
            'used'    => null,
        ];

        if (!$name) {
            return $preference;
        }

        $ds = CSQLDataSource::get('std');

        if ($user_id != 'default') {
            $user = CUser::get($user_id);

            if (!$user || !$user->_id) {
                return $preference;
            }
        }

        $pref  = new self();
        $where = [
            'key' => $ds->prepare('= ?', $name),
        ];

        $clauses = [
            'default' => 'IS NULL',

        ];

        if ($user_id != 'default') {
            $clauses['profile'] = $ds->prepare('= ?', $user->profile_id);
            $clauses['user']    = $ds->prepare('= ?', $user->_id);
        }

        foreach ($clauses as $_type => $_clause) {
            if ($_type == 'profile' && !$user->profile_id) {
                continue;
            }

            $where['user_id'] = $_clause;

            if ($pref->loadObject($where)) {
                $preference[$_type] = $pref->value;

                if (!is_null($pref->value)) {
                    $preference['used'] = $pref->value;
                }
            }
        }

        return $preference;
    }

    /**
     * Create or overwrites preference with value matching key and user_id
     *
     * @param string $key        Preference key
     * @param int    $user_id    User identifier
     * @param string $value      Value
     * @param bool   $restricted Allow to fetch and edit restricted preferences
     * @param bool   $rebuild    Rebuild prefs after storing (prefer false when mass setting)
     *
     * @return void
     * @throws Exception
     */
    static function setPref($key, $user_id, $value, $restricted = false, $rebuild = true)
    {
        $preference = new CPreferences();
        $ds         = $preference->getDS();

        $where = [
            'key'        => $ds->prepare('= ?', $key),
            'user_id'    => ($user_id) ? $ds->prepare('= ?', $user_id) : 'IS NULL',
            'restricted' => $ds->prepare('= ?', ($restricted ? 1 : 0)),
        ];

        // Do not use loadMatching in case the user_id is null
        $preference->loadObject($where);

        if (!$preference || !$preference->_id) {
            $preference->key        = $key;
            $preference->user_id    = $user_id;
            $preference->restricted = $restricted;
        }

        // Clean duplicates for defaults prefs
        if (!$user_id && $preference->countList($where) > 1) {
            $preference->removeDuplicatePrefs($preference->loadList($where));
        }

        $new_value         = stripslashes($value);
        $pref_changed      = ($preference->_id && $preference->value != $new_value);
        $preference->value = $new_value;

        if (!$preference->_id || $pref_changed) {
            if ($msg = $preference->store()) {
                CAppUI::setMsg($msg, UI_MSG_WARNING);
            } else {
                CAppUI::setMsg("{$preference->_class}-msg-modify", UI_MSG_OK);
                if ($rebuild) {
                    CAppUI::buildPrefs();
                }
            }
        }
    }

    public function removeDuplicatePrefs(array $duplicate_prefs): void
    {
        foreach ($duplicate_prefs as $_duplicate) {
            if ($_duplicate->_id !== $this->_id) {
                $_duplicate->delete();
            }
        }
    }

    static function getUserIDs($key, $strict_key = true, $restricted = false)
    {
        $ds = CSQLDataSource::get('std');

        $ljoin = [
            'users'           => 'user_preferences.user_id = users.user_id',
            'users_mediboard' => 'user_preferences.user_id = users_mediboard.user_id',
        ];

        $where = [
            'user_preferences.key'        => ($strict_key) ? $ds->prepare('= ?', $key) : $ds->prepareLike("%{$key}%"),
            'user_preferences.restricted' => ($restricted) ? "= '1'" : "= '0'",
            'user_preferences.value'      => 'IS NOT NULL',
            'users_mediboard.actif'       => "= '1'",
        ];

        // Sélection des préférences, peu importe leur valeur
        $request = new CRequest();
        $request->addSelect('user_preferences.user_id, users.template, user_preferences.value');
        $request->addTable('user_preferences');
        $request->addWhere($where);
        $request->addLJoin($ljoin);

        return $user_ids = $ds->loadList($request->makeSelect());
    }

    /**
     * Get users ID which have a given preference to a given value
     *
     * @param string $key          Preference key
     * @param string $value        Preference value
     * @param bool   $strict_key   Key strict comparison
     * @param bool   $strict_value Value strict comparison
     * @param bool   $restricted   Get functional permissions instead of preferences
     *
     * @return array
     */
    static function getUserIDsFromValue($key, $value, $strict_value = true, $strict_key = true, $restricted = false)
    {
        $ds = CSQLDataSource::get('std');

        $user_ids = static::getUserIDs($key, $strict_key, $restricted);

        if (!$user_ids) {
            return [];
        }

        $utilisateurs_exclus = [];
        $utilisateurs_inclus = [];
        $profils_exclus      = [];
        $profils_inclus      = [];
        $default_pref        = false;

        foreach ($user_ids as $_user) {
            $_user_id  = $_user['user_id'];
            $_template = $_user['template'];
            $_value    = $_user['value'];

            $pattern = "/{$value}($|\|)/";
            if ($strict_value) {
                $pattern = "/^{$value}$/";
            }

            /**
             * Regroupement des utilisateurs en quatre groupes :
             *
             * 1. Utilisateurs ayant spécifiquement la préférence
             * 2. Profils ayant spécifiquement la préférence
             *
             * 3. Utilisateurs ayant spécifiquement une autre préférence
             * 4. Profils ayant spécifiquement une autre préférence
             *
             * + L'on détermine si la préférence par défaut (user_id NULL) correspond à la valeur souhaitée
             */
            if (preg_match($pattern, $_value)) {
                if ($_user_id) {
                    if ($_template) {
                        $profils_inclus[$_user_id] = $_user_id;
                    } else {
                        $utilisateurs_inclus[$_user_id] = $_user_id;
                    }
                } else {
                    $default_pref = true;
                }
            } else {
                if ($_user_id) {
                    if ($_template) {
                        $profils_exclus[$_user_id] = $_user_id;
                    } else {
                        $utilisateurs_exclus[$_user_id] = $_user_id;
                    }
                }
            }
        }

        /**
         * Si des profils excluent spécifiquement la préférence,
         * l'on charge leurs utilisateurs associés et, après vérification qu'ils ne sont pas spécifiquement inclus, on les exclut
         */
        if ($profils_exclus) {
            $profil  = new CUser();
            $profils = $profil->loadList(
                [
                    'user_id' => $ds->prepareIn($profils_exclus),
                ]
            );

            foreach ($profils as $_profil) {
                $utilisateurs_profil = $_profil->loadRefProfiledUsers();

                if (!$utilisateurs_profil) {
                    continue;
                }

                $ids = CMbArray::pluck($utilisateurs_profil, '_id');

                foreach ($ids as $_id) {
                    if (!isset($utilisateurs_inclus[$_id])) {
                        $utilisateurs_exclus[$_id] = $_id;
                    }
                }
            }
        }

        /**
         * Si des profils incluent spécifiquement la préférence,
         * l'on charge leurs utilisateurs associés et, après vérification qu'ils ne sont pas spécifiquement exclus, on les inclut
         */
        if ($profils_inclus) {
            $profil  = new CUser();
            $profils = $profil->loadList(
                [
                    'user_id' => $ds->prepareIn($profils_inclus),
                ]
            );

            foreach ($profils as $_profil) {
                $utilisateurs_profil = $_profil->loadRefProfiledUsers();

                if (!$utilisateurs_profil) {
                    continue;
                }

                $ids = CMbArray::pluck($utilisateurs_profil, '_id');

                foreach ($ids as $_id) {
                    if (!isset($utilisateurs_exclus[$_id])) {
                        $utilisateurs_inclus[$_id] = $_id;
                    }
                }
            }
        }

        /**
         * Si la préférence par défaut correspond à la bonne valeur,
         * l'on récupère l'ensemble de tous les utilisateurs moins le sous-ensemble des utilisateurs exclus
         */
        if ($default_pref) {
            $request = new CRequest();
            $request->addSelect('user_id');
            $request->addTable('users_mediboard');
            $request->addOrder('user_id ASC');

            if ($all_ids = $ds->loadColumn($request->makeSelect())) {
                $utilisateurs_inclus = array_unique($utilisateurs_inclus + array_diff($all_ids, $utilisateurs_exclus));
            }
        }

        return $utilisateurs_inclus;
    }

    public static function getPrefValuesForList(
        array $pref_names,
        ?int  $user_id = null,
        bool  $restricted = false
    ): array {
        $pref = new self();
        $ds   = $pref->getDS();

        $where = [
            'user_id'    => 'IS NULL',
            'restricted' => ($restricted) ? "= '1'" : "= '0'",
            'key'        => $ds->prepareIn($pref_names),
        ];

        if ($user_id) {
            $where['user_id'] = $ds->prepare("= ?", $user_id);
            $where['value']   = "IS NOT NULL";
        }

        /** @var self[] $list */
        $list = $pref->loadList($where);

        $preferences = [];
        foreach ($list as $_pref) {
            $preferences[$_pref->key] = $_pref->value;
        }

        return $preferences;
    }
}
