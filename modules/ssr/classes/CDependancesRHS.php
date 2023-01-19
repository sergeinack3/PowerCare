<?php

/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Exception;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;

/**
 * Ligne de dépendance RHS
 */
class CDependancesRHS extends CMbObject
{

    // DB Table key
    /** @var int */
    public $dependances_id;

    // DB Fields
    /** @var int */
    public $rhs_id;
    /** @var int */
    public $habillage_haut;
    /** @var int */
    public $habillage_bas;
    /** @var int */
    public $deplacement_transfert_lit_chaise;
    /** @var int */
    public $deplacement_transfert_toilette;
    /** @var int */
    public $deplacement_transfert_baignoire;
    /** @var int */
    public $deplacement_locomotion;
    /** @var int */
    public $deplacement_escalier;
    /** @var int */
    public $alimentation_utilisations_ustensile;
    /** @var int */
    public $alimentation_mastication;
    /** @var int */
    public $alimentation_deglutition;
    /** @var int */
    public $continence_controle_miction;
    /** @var int */
    public $continence_controle_defecation;
    /** @var int */
    public $relation_comprehension_communication;
    /** @var int */
    public $relation_expression_claire;
    /** @var int */
    public $comportement;

    /** @var DependancesRHSBilan */
    public $_ref_dependances_rhs_bilan;
    // References
    /** @var CRHS */
    public $_ref_rhs;

    /**
     * @see parent::getSpec()
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = 'dependances_rhs';
        $spec->key   = 'dependances_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        // DB Fields
        $props["rhs_id"] = "ref notNull class|CRHS back|dependances";

        $degre                                         = "enum list|1|2|3|4";
        $props["habillage_haut"]                       = $degre;
        $props["habillage_bas"]                        = $degre;
        $props["deplacement_transfert_lit_chaise"]     = $degre;
        $props["deplacement_transfert_toilette"]       = $degre;
        $props["deplacement_transfert_baignoire"]      = $degre;
        $props["deplacement_locomotion"]               = $degre;
        $props["deplacement_escalier"]                 = $degre;
        $props["alimentation_utilisations_ustensile"]  = $degre;
        $props["alimentation_mastication"]             = $degre;
        $props["alimentation_deglutition"]             = $degre;
        $props["continence_controle_miction"]          = $degre;
        $props["continence_controle_defecation"]       = $degre;
        $props["comportement"]                         = $degre;
        $props["relation_comprehension_communication"] = $degre;
        $props["relation_expression_claire"]           = $degre;

        return $props;
    }

    /**
     * Charge le RHS parent
     *
     * @return CRHS
     */
    public function loadRefRHS(): CRHS
    {
        return $this->_ref_rhs = $this->loadFwdRef("rhs_id");
    }

    /**
     * @throws Exception
     */
    public function loadRefBilanRHS(): DependancesRHSBilan
    {
        return $this->_ref_dependances_rhs_bilan = DependancesRHSBilan::createFromDependancesRHS($this);
    }

}
