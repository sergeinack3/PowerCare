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
 * Précision pour les gestes opératoires
 */
class CGestePeropPrecision extends CMbObject
{
    /** @var int */
    public $geste_perop_precision_id;

    /** @var int */
    public $group_id;
    /** @var int */
    public $geste_perop_id;
    /** @var string */
    public $libelle;
    /** @var string */
    public $description;
    /** @var boolean */
    public $actif;

    /** @var CGroups */
    public $_ref_group;
    /** @var CGestePerop */
    public $_ref_geste_perop;
    /** @var CPrecisionValeur[] */
    public $_ref_valeurs;

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'geste_perop_precision';
        $spec->key   = 'geste_perop_precision_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props                   = parent::getProps();
        $props["group_id"]       = "ref notNull class|CGroups back|geste_perop_precisions";
        $props["geste_perop_id"] = "ref class|CGestePerop back|geste_perop_precisions";
        $props["libelle"]        = "str notNull";
        $props["description"]    = "text";
        $props["actif"]          = "bool default|1";

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view = $this->libelle;
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
     * Load gesture perop forward reference
     *
     * @return CGestePerop
     * @throws Exception
     */
    public function loadRefGestePerop(): CGestePerop
    {
        return $this->_ref_geste_perop = $this->loadFwdRef("geste_perop_id", true);
    }

    /**
     * Get the values of precision
     *
     * @return CPrecisionValeur[]
     * @throws Exception
     */
    public function loadRefValeurs(): array
    {
        return $this->_ref_valeurs = $this->loadBackRefs("precision_valeurs", "valeur");
    }
}
