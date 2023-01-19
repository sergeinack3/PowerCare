<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite;

use DateTime;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;

/**
 * Gestion du des grossesses antérieures à une grossesse en cours.
 */
class CGrossesseAnt extends CMbObject
{
    /** @var int DB Table key */
    public $grossesse_ant_id;

    /** @var int */
    public $grossesse_id;

    /** @var string */
    public $issue_grossesse;
    /** @var string */
    public $date;
    /** @var string */
    public $lieu;
    /** @var int */
    public $ag;
    /** @var bool */
    public $grossesse_apres_amp;
    /** @var string */
    public $complic_grossesse;
    /** @var bool */
    public $transfert_in_utero;
    /** @var bool */
    public $mode_debut_travail;
    /** @var string */
    public $mode_accouchement;
    /** @var string */
    public $anesthesie;
    /** @var string */
    public $perinee;
    /** @var string */
    public $delivrance;
    /** @var string */
    public $suite_couches;
    /** @var string */
    public $vecu_grossesse;
    /** @var string */
    public $remarques;

    /** @var bool */
    public $grossesse_multiple;
    /** @var int */
    public $nombre_enfants;
    /** @var string */
    public $sexe_enfant1;
    /** @var string */
    public $sexe_enfant2;
    /** @var string */
    public $sexe_enfant3;
    /** @var int */
    public $poids_naissance_enfant1;
    /** @var int */
    public $poids_naissance_enfant2;
    /** @var int */
    public $poids_naissance_enfant3;
    /** @var string */
    public $etat_nouveau_ne_enfant1;
    /** @var string */
    public $etat_nouveau_ne_enfant2;
    /** @var string */
    public $etat_nouveau_ne_enfant3;
    /** @var bool */
    public $allaitement_enfant1;
    /** @var string */
    public $allaitement_enfant1_desc;
    /** @var bool */
    public $allaitement_enfant2;
    /** @var string */
    public $allaitement_enfant2_desc;
    /** @var bool */
    public $allaitement_enfant3;
    /** @var string */
    public $allaitement_enfant3_desc;
    /** @var string */
    public $malformation_enfant1;
    /** @var string */
    public $malformation_enfant2;
    /** @var string */
    public $malformation_enfant3;
    /** @var string */
    public $maladie_hered_enfant1;
    /** @var string */
    public $maladie_hered_enfant2;
    /** @var string */
    public $maladie_hered_enfant3;
    /** @var string */
    public $pathologie_enfant1;
    /** @var string */
    public $pathologie_enfant2;
    /** @var string */
    public $pathologie_enfant3;
    /** @var string */
    public $transf_mut_enfant1;
    /** @var string */
    public $transf_mut_enfant2;
    /** @var string */
    public $transf_mut_enfant3;
    /** @var bool */
    public $deces_enfant1;
    /** @var bool */
    public $deces_enfant2;
    /** @var bool */
    public $deces_enfant3;
    /** @var int */
    public $age_deces_enfant1;
    /** @var int */
    public $age_deces_enfant2;
    /** @var int */
    public $age_deces_enfant3;

    // References
    /** @var CDossierPerinat */
    public $_ref_dossier_perinat;
    /** @var CGrossesse */
    public $_ref_grossesse;

    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = 'grossesse_ant';
        $spec->key   = 'grossesse_ant_id';

        return $spec;
    }

    public function getProps(): array
    {
        $props                 = parent::getProps();
        $props["grossesse_id"] = "ref notNull class|CGrossesse back|grossesses_ant";

        $props["issue_grossesse"]     = "str";
        $props["date"]                = "date progressive notNull";
        $props["lieu"]                = "str show|0";
        $props["ag"]                  = "num";
        $props["grossesse_apres_amp"] = "bool show|0";
        $props["complic_grossesse"]   = "str show|0";
        $props["transfert_in_utero"]  = "bool show|0";
        $props["mode_debut_travail"]  = "enum list|spon|decl|cesar";
        $props["mode_accouchement"]   = "str";
        $props["anesthesie"]          = "str";
        $props["perinee"]             = "str";
        $props["delivrance"]          = "str";
        $props["suite_couches"]       = "str show|0";
        $props["vecu_grossesse"]      = "str show|0";
        $props["remarques"]           = "text helped show|0";

        $props["grossesse_multiple"]       = "bool show|0";
        $props["nombre_enfants"]           = "num min|1 max|3 show|0";
        $props["sexe_enfant1"]             = "enum list|m|f";
        $props["sexe_enfant2"]             = "enum list|m|f";
        $props["sexe_enfant3"]             = "enum list|m|f";
        $props["poids_naissance_enfant1"]  = "num"; // En grammes
        $props["poids_naissance_enfant2"]  = "num";
        $props["poids_naissance_enfant3"]  = "num";
        $props["etat_nouveau_ne_enfant1"]  = "str show|0";
        $props["etat_nouveau_ne_enfant2"]  = "str show|0";
        $props["etat_nouveau_ne_enfant3"]  = "str show|0";
        $props["allaitement_enfant1"]      = "bool";
        $props["allaitement_enfant1_desc"] = "str show|0";
        $props["allaitement_enfant2"]      = "bool";
        $props["allaitement_enfant2_desc"] = "str show|0";
        $props["allaitement_enfant3"]      = "bool";
        $props["allaitement_enfant3_desc"] = "str show|0";
        $props["malformation_enfant1"]     = "str show|0";
        $props["malformation_enfant2"]     = "str show|0";
        $props["malformation_enfant3"]     = "str show|0";
        $props["maladie_hered_enfant1"]    = "str show|0";
        $props["maladie_hered_enfant2"]    = "str show|0";
        $props["maladie_hered_enfant3"]    = "str show|0";
        $props["pathologie_enfant1"]       = "str show|0";
        $props["pathologie_enfant2"]       = "str show|0";
        $props["pathologie_enfant3"]       = "str show|0";
        $props["transf_mut_enfant1"]       = "str show|0";
        $props["transf_mut_enfant2"]       = "str show|0";
        $props["transf_mut_enfant3"]       = "str show|0";
        $props["deces_enfant1"]            = "bool show|0";
        $props["deces_enfant2"]            = "bool show|0";
        $props["deces_enfant3"]            = "bool show|0";
        $props["age_deces_enfant1"]        = "num show|0"; // En jours
        $props["age_deces_enfant2"]        = "num show|0";
        $props["age_deces_enfant3"]        = "num show|0";

        return $props;
    }

    public function updateFormFields(): void
    {
        parent::updateFormFields();

        $this->loadView();
    }

    public function loadView(): void
    {
        parent::loadView();

        $this->_view = CAppUI::tr('CGrossesseAnt-Atcd of year %s', (new DateTime($this->date))->format('Y'));
    }

    public function loadRefDossierPerinat(): ?CDossierPerinat
    {
        return $this->_ref_dossier_perinat = $this->loadFwdRef("dossier_perinat_id", true);
    }

    public function store(): ?string
    {
        // Increment du nombre de grossesses anterieures
        if (!$this->_id) {
            $grossesse = $this->loadRefGrossesse();
            $grossesse->nb_grossesses_ant++;
            if ($msg = $grossesse->store()) {
                return $msg;
            }
        }

        return parent::store();
    }

    public function loadRefGrossesse(): ?CGrossesse
    {
        return $this->_ref_grossesse = $this->loadFwdRef("grossesse_id", true);
    }
}
