<?php

/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin;

if (!defined("PERM_DENY")) {
    define("PERM_DENY", 0);
    define("PERM_READ", 1);
    define("PERM_EDIT", 2);
}

use Ox\Core\CMbObject;
use Ox\Core\CRequest;
use Ox\Core\CStoredObject;

/**
 * The CPermObject class
 */
class CPermObject extends CMbObject
{

    // Constants
    const DENY = 0;
    const READ = 1;
    const EDIT = 2;

    // Stored permissions
    static $users_perms = null;    // OLD query system
    // static $users_perms = array(); // NEW query system
    static $users_cache = [];

    // DB Table key
    public $perm_object_id;

    // DB Fields
    public $user_id;
    public $object_id;
    public $object_class;
    public $permission;

    // Distant fields
    public $_owner;

    /** @var CUser */
    public $_ref_db_user;

    /** @var CStoredObject */
    public $_ref_db_object;

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'perm_object';
        $spec->key   = 'perm_object_id';

        $spec->uniques['user_object'] = ['user_id', 'object_class', 'object_id'];

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props                 = parent::getProps();
        $props["user_id"]      = "ref notNull class|CUser cascade back|permissions_objet";
        $props["object_id"]    = "ref class|CMbObject meta|object_class cascade back|permissions";
        $props["object_class"] = "str notNull";
        $props["permission"]   = "enum list|0|1|2";

        $props["_owner"] = "enum list|user|template";

        return $props;
    }

    /**
     * Load referenced object
     *
     * @return CStoredObject
     */
    function loadRefDBObject()
    {
        return $this->_ref_db_object = $this->loadFwdRef("object_id");
    }

    /**
     * Load the user
     *
     * @return CUser
     */
    function loadRefDBUser()
    {
        return $this->_ref_db_user = $this->loadFwdRef("user_id");
    }

    /**
     * @see parent::loadRefsFwd()
     */
    function loadRefsFwd()
    {
        $this->loadRefDBObject();
        $this->loadRefDBUser();
    }

    /**
     * Chargement des droits du user
     *
     * @param string $user_id User ID
     *
     * @return self[]
     */
    static function loadExactPermsObject($user_id = null)
    {
        $perm  = new self();
        $where = [
            "user_id" => "= '$user_id'",
        ];

        return $perm->loadList($where);
    }

    /**
     * Build the class object permission tree for given user
     * Cache the result as static member
     *
     * @param int $user_id The concerned user, connected user if null
     *
     * @return void
     */
    static function buildUser($user_id = null)
    {
        $user = CUser::get($user_id);

        // Never reload permissions for a given user
        if (isset(self::$users_perms[$user->_id])) {
            return;
        }

        $perm = new self();

        // Profile specific permissions
        $perms["prof"] = [];
        if ($user->profile_id) {
            $perm->user_id = $user->profile_id;
            $perms["prof"] = $perm->loadMatchingList();
        }

        // User specific permissions
        $perm->user_id = $user->_id;
        $perms["user"] = $perm->loadMatchingList();

        // Build final tree
        foreach ($perms as $_perms) {
            foreach ($_perms as $_perm) {
                self::$users_perms[$user->_id][$_perm->object_class][$_perm->object_id ? $_perm->object_id : "all"] = $_perm->permission;
            }
        }
    }

    /**
     * Load user permissions
     *
     * @param int $user_id The user's ID
     *
     * @return void FIXME Everything is put into global $userPermsObjects
     */
    static function loadUserPerms($user_id = null)
    {
        global $userPermsObjects;

        // Déclaration du user
        $user = CUser::get($user_id);

        /** @var self[] $permsObjectFinal */
        $permsObjectFinal = [];

        /** @var self[] $tabObjectProfil */
        $tabObjectProfil = [];

        /** @var self[] $tabObjectSelf */
        $tabObjectSelf = [];

        // Chargement des droits
        $permsObjectSelf = CPermObject::loadExactPermsObject($user->user_id);

        // Creation du tableau de droits du user
        foreach ($permsObjectSelf as $value) {
            $tabObjectSelf["obj_$value->object_id$value->object_class"] = $value;
        }

        // Creation du tableau de droits du profil
        $permsObjectProfil = CPermObject::loadExactPermsObject($user->profile_id);
        foreach ($permsObjectProfil as $value) {
            $tabObjectProfil["obj_$value->object_id$value->object_class"] = $value;
        }

        // Fusion des deux tableaux de droits
        $tabObjectFinal = array_merge($tabObjectProfil, $tabObjectSelf);

        // Creation du tableau de fusion des droits
        foreach ($tabObjectFinal as $value) {
            $permsObjectFinal[$value->perm_object_id] = $value;
        }

        // Tri du tableau de droit final en fonction des cle (perm_module_id)
        ksort($permsObjectFinal);

        $userPermsObjects = [];
        foreach ($permsObjectFinal as $perm_obj) {
            if (!$perm_obj->object_id) {
                $userPermsObjects[$perm_obj->object_class][0] = $perm_obj;
            } else {
                $userPermsObjects[$perm_obj->object_class][$perm_obj->object_id] = $perm_obj;
            }
        }
    }

    /**
     * Gets the permission on the module
     *
     * @param CStoredObject $object        Object to load the permissions of
     * @param int           $permType      Permission level
     * @param CStoredObject $defaultObject Default object to load the permissions from
     * @param int           $user_id       User ID
     *
     * @return bool
     */
    static function getPermObject(CStoredObject $object, $permType, $defaultObject = null, $user_id = null)
    {
        $user = CUser::get($user_id);

        // Shorteners
        $class = $object->_class;
        $id    = $object->_id;

        // Use permission query cache when available
        if (isset(self::$users_cache[$user->_id][$class][$id])) {
            return self::$users_cache[$user->_id][$class][$id] >= $permType;
        }

        // New cached permissions system : DO NOT REMOVE
        if (is_array(self::$users_perms)) {
            self::buildUser($user->_id);
            $perms = self::$users_perms[$user->_id];

            // Object specific, or Class specific, or Module generic
            $perm =
                (isset($perms[$class][$id]) ? $perms[$class][$id] :
                    (isset($perms[$class]["all"]) ? $perms[$class]["all"] : "module"));

            // In case of module check, first build module cache, then get value from cache
            if ($perm == "module") {
                $mod_id = $object->_ref_module->_id;
                CPermModule::getPermModule($mod_id, $permType, $user->_id);
                $perm = CPermModule::$users_cache[$user->_id][$mod_id]["permission"];
            }

            self::$users_cache[$user->_id][$class][$id] = $perm;

            return $perm >= $permType;
        }

        global $userPermsObjects;

        $object_class = $object->_class;
        $object_id    = $object->_id;

        if (isset($userPermsObjects[$object_class][$object_id])) {
            return $userPermsObjects[$object_class][$object_id]->permission >= $permType;
        }

        if (isset($userPermsObjects[$object_class][0])) {
            return $userPermsObjects[$object_class][0]->permission >= $permType;
        }

        return $defaultObject != null ?
            $defaultObject->getPerm($permType) :
            $object->_ref_module->getPerm($permType);
    }

    /**
     * @see parent::check()
     */
    function check()
    {
        $msg = null;
        $ds  = $this->_spec->ds;

        if (!$this->perm_object_id) {
            $where                 = [];
            $where["user_id"]      = $ds->prepare("= %", $this->user_id);
            $where["object_class"] = $ds->prepare("= %", $this->object_class);
            if ($this->object_id) {
                $where["object_id"] = $ds->prepare("= %", $this->object_id);
            } else {
                $where["object_id"] = "IS NULL";
            }

            $query = new CRequest();
            $query->addSelect("count(perm_object_id)");
            $query->addTable("perm_object");
            $query->addWhere($where);

            $nb_result = $ds->loadResult($query->makeSelect());

            if ($nb_result) {
                $msg .= "Une permission sur cet objet existe déjà.<br />";
            }
        }

        return $msg . parent::check();
    }
}

//// Now load in main.php
//if (is_null(CPermModule::$users_perms)) {
// CPermObject::loadUserPerms();
//}
