<?php

/**
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Personnel;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Admin\CPermObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Class CPersonnel
 */
class CPersonnel extends CMbObject
{
    // DB Table key
    /** @var string */
    public const TYPE_OP = 'op';

    // DB references
    /** @var string */
    public const TYPE_OP_PANSEUR = 'op_panseuse';
    /** @var string */
    public const TYPE_REVEIL = 'reveil';

    // DB fields
    /** @var string */
    public const TYPE_SERVICE = 'service';
    /** @var string */
    public const TYPE_IADE = 'iade';

    // Form Field
    /** @var string */
    public const TYPE_BRANCARDIER = 'brancardier';
    /** @var string */
    public const TYPE_SAGEFEMME = 'sagefemme';
    /** @var string */
    public const TYPE_MANIPULATEUR = 'manipulateur';
    /** @var string */
    public const TYPE_AUX_PUERICULTURE = 'aux_puericulture';
    /** @var string */
    public const TYPE_INSTRUMENTISTE = 'instrumentiste';
    /** @var string */
    public const TYPE_CIRCULANTE = 'circulante';
    /** @var string */
    public const TYPE_AIDE_SOIGNANT = 'aide_soignant';
    /** @var array The list of types */
    public static $_types = [
        self::TYPE_OP,
        self::TYPE_OP_PANSEUR,
        self::TYPE_REVEIL,
        self::TYPE_SERVICE,
        self::TYPE_IADE,
        self::TYPE_BRANCARDIER,
        self::TYPE_SAGEFEMME,
        self::TYPE_MANIPULATEUR,
        self::TYPE_AUX_PUERICULTURE,
        self::TYPE_INSTRUMENTISTE,
        self::TYPE_CIRCULANTE,
        self::TYPE_AIDE_SOIGNANT,
    ];
    public $personnel_id;
    public $user_id;
    public $_ref_user;
    public $emplacement;
    public $actif;
    public $_user_last_name;
    public $_user_first_name;
    public $_emplacements;

    /**
     * Charge le personnel pour l'établissement courant
     *
     * @param string $emplacement Emplacement du personnel
     * @param bool   $actif       Seulement les actifs
     * @param bool   $groupby     Grouper par utilisateur
     *
     * @return self[]
     */
    static function loadListPers($emplacement, $actif = true, $groupby = false)
    {
        $personnel = new self();

        $where = [];

        if (is_array($emplacement)) {
            $where["emplacement"] = CSQLDataSource::prepareIn($emplacement);
        } else {
            $where["emplacement"] = "= '$emplacement'";
        }

        // Could have been ambiguous with CMediusers.actif
        if ($actif) {
            $where["personnel.actif"]       = "= '1'";
            $where["users_mediboard.actif"] = "= '1'";
        }

        $ljoin["users"]           = "personnel.user_id = users.user_id";
        $ljoin["users_mediboard"] = "users_mediboard.user_id = users.user_id";

        $order = "users.user_last_name";

        $group = "personnel" . ($groupby ? ".user_id" : ".personnel_id");

        /** @var self[] $personnels */
        $personnels = $personnel->loadGroupList($where, $order, null, $group, $ljoin);
        $users      = CStoredObject::massLoadFwdRef($personnels, "user_id");
        CStoredObject::massLoadFwdRef($users, "function_id");

        self::massLoadListEmplacement($personnels);

        foreach ($personnels as $_personnel) {
            $_personnel->loadRefUser()->loadRefFunction();
        }

        return $personnels;
    }

    /**
     * Load list overlay for current group
     *
     * @param array $where   where
     * @param array $order   order
     * @param int   $limit   limit
     * @param array $groupby groupby
     * @param array $ljoin   ljoin
     *
     * @return self[]
     */
    function loadGroupList($where = [], $order = null, $limit = null, $groupby = null, $ljoin = [])
    {
        $ljoin["users_mediboard"]     = "users_mediboard.user_id = personnel.user_id";
        $ljoin["functions_mediboard"] = "functions_mediboard.function_id = users_mediboard.function_id";
        $ljoin["secondary_function"]  = "secondary_function.user_id = users_mediboard.user_id";
        $ljoin[]                      = "functions_mediboard secondary_function_B ON secondary_function_B.function_id = secondary_function.function_id";
        // Filtre sur l'établissement
        $g       = CGroups::loadCurrent();
        $where[] = "functions_mediboard.group_id = '$g->_id' OR secondary_function_B.group_id = '$g->_id'";

        $list = $this->loadList($where, $order, $limit, $groupby, $ljoin);

        /* The load list can return null if the dPpersonnel module is not installed */
        if (!is_array($list)) {
            $list = [];
        }

        return $list;
    }

    static function massLoadListEmplacement($personnels = [])
    {
        if (!count($personnels)) {
            return;
        }

        $ds = CSQLDataSource::get("std");

        $query        = "SELECT user_id, GROUP_CONCAT(DISTINCT emplacement) AS emplacements
      FROM personnel
      WHERE user_id " . CSQLDataSource::prepareIn(CMbArray::pluck($personnels, "user_id")) . "
      AND actif = '1'
      GROUP BY user_id;";
        $emplacements = $ds->loadHashAssoc($query);

        foreach ($emplacements as $user_id => $_emplacement) {
            $_emplacements = explode(",", $_emplacement["emplacements"]);

            foreach ($personnels as $_personnel_id => $_personnel) {
                if ($_personnel->user_id != $user_id) {
                    continue;
                }

                $personnels[$_personnel_id]->_emplacements = array_combine($_emplacements, $_emplacements);
            }
        }
    }

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "personnel";
        $spec->key   = "personnel_id";

        $spec->uniques['personnel'] = ['user_id', 'emplacement'];
        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props                = parent::getProps();
        $props["user_id"]     = "ref notNull class|CMediusers back|personnels";
        $props["emplacement"] = "enum notNull list|" . implode('|', self::$_types) . " default|op";
        $props["actif"]       = "bool notNull default|1";

        $props["_user_last_name"]  = "str";
        $props["_user_first_name"] = "str";

        return $props;
    }

    /**
     * @see parent::loadRefsFwd()
     */
    function loadRefsFwd()
    {
        parent::loadRefsFwd();
        $this->loadRefUser();
    }

    /**
     * Load User
     *
     * @return CMediusers|null
     * @throws Exception
     */
    function loadRefUser()
    {
        $this->_ref_user = $this->loadFwdRef("user_id", true);
        $this->_view     = $this->getFormattedValue("emplacement") . ": " . $this->_ref_user->_view;

        return $this->_ref_user;
    }

    /**
     * @see parent::updateFormFields()
     */
    function updateFormFields()
    {
        parent::updateFormFields();
        $this->_view = $this->getFormattedValue("emplacement") . ": " . $this->user_id;
    }

    /**
     * Recherche de l'ensemble des emplacements de l'utilisateur
     *
     * @return array
     */
    function loadListEmplacement()
    {
        $ds           = $this->getDS();
        $query        = "SELECT DISTINCT emplacement
      FROM personnel
      WHERE user_id = '$this->user_id'
      AND actif = '1';";
        $emplacements = $ds->loadList($query);

        $list_emplacements = [];
        foreach ($emplacements as $_emplacement) {
            $list_emplacements[$_emplacement["emplacement"]] = $_emplacement["emplacement"];
        }

        return $this->_emplacements = $list_emplacements;
    }

    public function store(): ?string
    {
        //Check if the user have the right to be create in this etablishement
        $group = CGroups::loadCurrent();
        CPermObject::loadUserPerms($this->user_id);
        $cand_red_etab = CPermObject::getPermObject($group, PERM_READ, null, $this->user_id);
        if (!$cand_red_etab) {
            return CAppUI::tr(
                "CPersonnel-msg-This person does not have the rights to be created in this establishment"
            );
        }

        return parent::store();
    }
}
