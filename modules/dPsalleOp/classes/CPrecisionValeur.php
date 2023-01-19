<?php

/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp;

use Exception;
use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Valeur des précisions pour les gestes opératoires
 */
class CPrecisionValeur extends CMbObject
{
    /** @var int */
    public $precision_valeur_id;

    /** @var int */
    public $group_id;
    /** @var int */
    public $geste_perop_precision_id;
    /** @var string */
    public $valeur;
    /** @var boolean */
    public $actif;

    /** @var boolean */
    public $_edit = true;

    /** @var CGroups */
    public $_ref_group;
    /** @var CGestePeropPrecision */
    public $_ref_precision;
    /** @var CAnesthPerop[] */
    public $_ref_anesth_perops;

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'precision_valeur';
        $spec->key   = 'precision_valeur_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props                             = parent::getProps();
        $props["group_id"]                 = "ref notNull class|CGroups back|precision_valeurs";
        $props["geste_perop_precision_id"] = "ref class|CGestePeropPrecision back|precision_valeurs";
        $props["valeur"]                   = "str notNull";
        $props["actif"]                    = "bool default|1";

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view = $this->valeur;
    }

    /**
     * @inheritDoc
     */
    public function check()
    {
        if ($msg = parent::check()) {
            return $msg;
        }

        $this->loadRefAnesthPerops();

        if (!$this->_edit) {
            return 'CPrecisionValeur-msg-The value is associated with a perop gesture and can not be changed';
        }

        return null;
    }

    /**
     * Load group forward reference
     *
     * @return CGroups
     * @throws Exception
     */
    public function loadRefGroup(): CGroups
    {
        return $this->_ref_group = $this->loadFwdRef("group_id", true);
    }

    /**
     * Load precision forward reference
     *
     * @return CGestePeropPrecision
     * @throws Exception
     */
    public function loadRefPrecision(): CGestePeropPrecision
    {
        return $this->_ref_precision = $this->loadFwdRef("geste_perop_precision_id", true);
    }

    /**
     * Load anesth perop back references
     *
     * @return CAnesthPerop[]
     * @throws Exception
     */
    public function loadRefAnesthPerops(): array
    {
        $anesth_perops = $this->loadBackRefs("anesth_perops");
        $this->_edit   = count($anesth_perops) ? false : true;

        return $this->_ref_anesth_perops = $anesth_perops;
    }
}
