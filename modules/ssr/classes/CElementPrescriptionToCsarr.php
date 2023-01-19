<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\CAppUI;

/**
 * Classe d'association entre éléments de prescription et codes CsARR
 */
class CElementPrescriptionToCsarr extends CElementPrescriptionToReeducation
{
    // DB Table key
    /** @var integer */
    public $element_prescription_to_csarr_id;

    /** @var string */
    public $modulateurs;
    /** @var string */
    public $code_ext_documentaire;
    /** @var integer */
    public $duree; // in minute
    /** @var string */
    public $type_seance;
    /** @var bool */
    public $default;
    /** @var integer */
    public $rank;
    /** @var string */
    public $_heure_debut;
    /** @var string */
    public $_heure_fin;

    /** @var CActiviteCsARR */
    public $_ref_activite_csarr;
    /** @var integer */
    public $_count_csarr_by_type;

    /**
     * @inheritDoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'element_prescription_to_csarr';
        $spec->key   = 'element_prescription_to_csarr_id';

        return $spec;
    }

    /**
     * @inheritDoc
     */
    function getProps()
    {
        $props                            = parent::getProps();
        $props["element_prescription_id"] .= " back|csarrs";
        $props["code"]                    = "str notNull length|7";
        $props["modulateurs"]             = "str";
        $props["code_ext_documentaire"]   = "str";
        $props["duree"]                   = "num min|0";
        $props["type_seance"]             = "enum list|dediee|non_dediee|collective";
        $props["default"]                 = "bool default|0";
        $props["rank"]                    = "num min|0";

        $props["_heure_debut"] = "time";
        $props["_heure_fin"]   = "time";

        return $props;
    }

    /**
     * @inheritDoc
     */
    function check()
    {
        // Verification du code Csarr saisi
        $code_csarr = CActiviteCsARR::get($this->code);
        if (!$code_csarr->code) {
            return CAppUI::tr("CActiviteCsARR.code_invalide");
        }

        return parent::check();
    }

    /**
     * Charge l'activité CsARR associée
     *
     * @return CActiviteCsARR
     */
    function loadRefActiviteCsarr(): CActiviteCsARR
    {
        $activite = CActiviteCsARR::get($this->code);
        $activite->loadRefHierarchie();

        return $this->_ref_activite_csarr = $activite;
    }

    /**
     * @inheritDoc
     */
    function loadView()
    {
        parent::loadView();
        $this->loadRefActiviteCsarr();
    }
}
