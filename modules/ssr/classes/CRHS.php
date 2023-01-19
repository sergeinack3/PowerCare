<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbRange;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\IGroupRelated;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Pmsi\CCIM10;

/**
 * Résumé Hébdomadaire Simplifié
 * Correspond à une cotation d'actes de réadaptation pour une semaine
 */
class CRHS extends CMbObject implements IGroupRelated
{
  static $days = array(
    "1" => "mon",
    "2" => "tue",
    "3" => "wed",
    "4" => "thu",
    "5" => "fri",
    "6" => "sat",
    "7" => "sun",
  );

  // DB Table key
  public $rhs_id;

  // DB Fields
  public $sejour_id;
  public $date_monday;
  public $facture;
  public $FPP;  // Finalité principale de prise en charge
  public $MMP;  // Manifestation morbide principale
  public $AE;   // Affection étiologique
  public $DAS;  // Diagnostics associés significatifs
  public $DAD;  // Diagnostic associé documentaire

  // Form Field
  public $_date_tuesday;
  public $_date_wednesday;
  public $_date_thursday;
  public $_date_friday;
  public $_date_saturday;
  public $_date_sunday;
  public $_week_number;

  // DAS
  public $_added_code_das;
  public $_deleted_code_das;
  public $_codes_das;

  // DAD
  public $_added_code_dad;
  public $_deleted_code_dad;
  public $_codes_dad;

  // Distant fields
  public $_in_bounds;
  public $_in_bounds_mon;
  public $_in_bounds_tue;
  public $_in_bounds_wed;
  public $_in_bounds_thu;
  public $_in_bounds_fri;
  public $_in_bounds_sat;
  public $_in_bounds_sun;

  public $_count_cdarr = 0;
  public $_prestas_ssr;

  // Object References
  /** @var  CSejour */
  public $_ref_sejour;
  /** @var  CDependancesRHS */
  public $_ref_dependances;
  /** @var  DependancesRHSBilan[] */
  public $_ref_dependances_chonology;
  /** @var DependancesRHSBilan */
  public $_ref_dependances_bilan;

  // Distant references
  public $_ref_lignes_activites;
  /** @var CMediusers[] */
  public $_ref_executants;
  /** @var CLigneActivitesRHS[][] */
  public $_ref_lines_by_executant;
  /** @var array */
  public $_ref_lines_by_executant_by_code;
  /** @var CTypeActiviteCdARR */
  public $_ref_types_activite;
  /** @var int[] */
  public $_totaux;
  /** @var int */
  public $_nb_weeks;

  // External objects
  /** @var CCodeCIM10 */
  public $_diagnostic_FPP;
  public $_diagnostic_MMP;
  public $_diagnostic_AE;

  public $_ref_DAS_DAD;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec                 = parent::getSpec();
    $spec->table          = "rhs";
    $spec->key            = "rhs_id";
    $spec->uniques["rhs"] = array("sejour_id", "date_monday");

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();

    // DB Fields
    $props["sejour_id"]   = "ref notNull class|CSejour back|rhss";
    $props["date_monday"] = "date notNull";
    $props["facture"]     = "bool default|0";
    $props["FPP"]         = "str";
    $props["MMP"]         = "str";
    $props["AE"]          = "str";
    $props["DAS"]         = "str";
    $props["DAD"]         = "str";

    // Form Field
    $props["_date_tuesday"]   = "date";
    $props["_date_wednesday"] = "date";
    $props["_date_thursday"]  = "date";
    $props["_date_friday"]    = "date";
    $props["_date_saturday"]  = "date";
    $props["_date_sunday"]    = "date";
    $props["_week_number"]    = "num min|0 max|52";
    $props["_nb_weeks"]       = "num notNull";

    // Remote fields
    $props["_in_bounds"]     = "bool";
    $props["_in_bounds_mon"] = "bool";
    $props["_in_bounds_tue"] = "bool";
    $props["_in_bounds_wed"] = "bool";
    $props["_in_bounds_thu"] = "bool";
    $props["_in_bounds_fri"] = "bool";
    $props["_in_bounds_sat"] = "bool";
    $props["_in_bounds_sun"] = "bool";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function store() {
    $this->completeField("DAS");
    $this->completeField("DAD");

    $this->_codes_das = $this->DAS ? explode("|", $this->DAS) : array();
    $this->_codes_dad = $this->DAD ? explode("|", $this->DAD) : array();

    // DAS
    if ($this->_added_code_das) {
      $da = CCodeCIM10::get($this->_added_code_das);
      if (!$da->exist) {
        CAppUI::setMsg("Le code CIM du diagnostic associé significatif saisi n'est pas valide", UI_MSG_WARNING);

        return null;
      }
      $this->_codes_das[] = $this->_added_code_das;
    }

    if ($this->_deleted_code_das) {
      CMbArray::removeValue($this->_deleted_code_das, $this->_codes_das);
    }

    $this->DAS = implode("|", array_unique($this->_codes_das));

    // DAD
    if ($this->_added_code_dad) {
      $da = CCodeCIM10::get($this->_added_code_dad);
      if (!$da->exist) {
        CAppUI::setMsg("Le code CIM du diagnostic associé significatif saisi n'est pas valide", UI_MSG_WARNING);

        return null;
      }
      $this->_codes_dad[] = $this->_added_code_dad;
    }

    if ($this->_deleted_code_dad) {
      CMbArray::removeValue($this->_deleted_code_dad, $this->_codes_dad);
    }

    $this->DAD = implode("|", array_unique($this->_codes_dad));

    return parent::store();
  }

  /**
   * @see parent::check()
   */
  function check() {
    if ($this->date_monday && CMbDT::format($this->date_monday, "%w") != "1") {
      return CAppUI::tr("CRHS-failed-monday", $this->date_monday);
    }

    return parent::check();
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_week_number = CMbDT::format($this->date_monday, "%V");

    $this->_date_tuesday   = CMbDT::date("+1 DAY", $this->date_monday);
    $this->_date_wednesday = CMbDT::date("+2 DAY", $this->date_monday);
    $this->_date_thursday  = CMbDT::date("+3 DAY", $this->date_monday);
    $this->_date_friday    = CMbDT::date("+4 DAY", $this->date_monday);
    $this->_date_saturday  = CMbDT::date("+5 DAY", $this->date_monday);
    $this->_date_sunday    = CMbDT::date("+6 DAY", $this->date_monday);

    $this->_view = CAppUI::tr("Week") . " $this->_week_number";
  }

  /**
   * Charge le séjour et vérifie les dates liées
   *
   * @return CSejour
   */
  function loadRefSejour() {
    /** @var CSejour $sejour */
    $sejour = $this->loadFwdRef("sejour_id", true);
    $sejour->loadRefPatient();

    $entree = CMbDT::date($sejour->entree);
    $sortie = CMbDT::date($sejour->sortie);

    $this->_in_bounds     = CMbRange::collides($this->date_monday, $this->_date_sunday, $entree, $sortie, false);
    $this->_in_bounds_mon = CMbRange::in($this->date_monday, $entree, $sortie);
    $this->_in_bounds_tue = CMbRange::in($this->_date_tuesday, $entree, $sortie);
    $this->_in_bounds_wed = CMbRange::in($this->_date_wednesday, $entree, $sortie);
    $this->_in_bounds_thu = CMbRange::in($this->_date_thursday, $entree, $sortie);
    $this->_in_bounds_fri = CMbRange::in($this->_date_friday, $entree, $sortie);
    $this->_in_bounds_sat = CMbRange::in($this->_date_saturday, $entree, $sortie);
    $this->_in_bounds_sun = CMbRange::in($this->_date_sunday, $entree, $sortie);

    return $this->_ref_sejour = $sejour;
  }

  /**
   * Get all possible and existing RHS for given sejour, by date as keys
   *
   * @param CSejour $sejour       Sejour
   * @param string  $first_monday Optionnal first monday
   *
   * @return self[],null Null if not applyable
   */
  static function getAllRHSsFor(CSejour $sejour, $first_monday = null) {
    if (!$sejour->_id || $sejour->type != "ssr") {
      return null;
    }

    $rhss = array();
    /** @var self $_rhs */
    foreach ($sejour->loadBackRefs("rhss") as $_rhs) {
      $rhss[$_rhs->date_monday] = $_rhs;
    }

    foreach (self::getAllMondays($first_monday ?: $sejour->entree, $sejour->sortie) as $date_monday) {
      if (!isset($rhss[$date_monday])) {
        $rhs              = new self();
        $rhs->sejour_id   = $sejour->_id;
        $rhs->date_monday = $date_monday;
        $rhs->updateFormFields();
        $rhss[$date_monday] = $rhs;
      }
    }

    ksort($rhss);

    return $rhss;
  }

  /**
   * Return all mondays between two dates
   *
   * @param string $date_min Beginning date
   * @param string $date_max Ending date
   *
   * @return array
   */
  static function getAllMondays($date_min, $date_max) {
    $mondays = array();
    for (
      $date_monday = CMbDT::date("last sunday + 1 day", $date_min);
      $date_monday <= $date_max;
      $date_monday = CMbDT::date("+1 week", $date_monday)
    ) {
      $mondays[] = $date_monday;
    }

    return $mondays;
  }

  /**
   * Charge le relevé de dépendances
   *
   * @return CDependancesRHS
   */
  function loadRefDependances() {
    return $this->_ref_dependances = $this->loadUniqueBackRef("dependances");
  }

  /**
   * Charge la chronologie de relevés de dépendances autout du RHS
   *
   * @return DependancesRHSBilan[]
   */
  function loadDependancesChronology() {
    $sejour  = $this->loadRefSejour();
    $all_rhs = self::getAllRHSsFor($sejour);

    $empty   = new DependancesRHSBilan(0,0,0,0,0,0);

    $chrono = array(
      "-2" => $empty,
      "-1" => $empty,
      "+0" => $empty,
      "+1" => $empty,
      "+2" => $empty,
    );

    foreach ($chrono as $ref => &$dep) {
      $date = CMbDT::date("$ref WEEKS", $this->date_monday);

      if (array_key_exists($date, $all_rhs)) {
        $_rhs = $all_rhs[$date];
        $_rhs->loadRefDependances();
        $dep = $_rhs->_ref_dependances->loadRefBilanRHS();
      }
    }

    return $this->_ref_dependances_chonology = $chrono;
  }

  /**
   * Charge les lignes d'activité qui composent le RHS
   *
   * @param array $where Optional conditions
   *
   * @return CLigneActivitesRHS[]
   */
  function loadRefLignesActivites($where = array()) {
    return $this->_ref_lignes_activites = $this->loadBackRefs("lines", null, null, null, null, null, "", $where);
  }

  /**
   * Calcul les totaux par type d'activité CdARR
   *
   * @return int[]
   */
  function countTypeActivite() {
    $totaux = array();

    $type_activite = new CTypeActiviteCdARR();

    /** @var CTypeActiviteCdARR $types_activite */
    $types_activite = $type_activite->loadList();
    foreach ($types_activite as $_type) {
      $totaux[$_type->code] = 0;
    }

    $this->loadRefLignesActivites();
    $lines = $this->_ref_lignes_activites;
    foreach ($lines as $_line) {
      if ($_line->code_activite_cdarr) {
        $_line->loadRefActiviteCdARR();
        $_line->_ref_activite_cdarr->loadRefTypeActivite();
        $type_activite                = $_line->_ref_activite_cdarr->_ref_type_activite;
        $totaux[$type_activite->code] += $_line->_qty_total;
      }
    }

    return $totaux;
  }

  /**
   * Recalcul le RHS à partir des événements validés
   *
   * @return void|string
   */
  function recalculate() {
    if (!$this->_id) {
      return null;
    }
    if ($this->facture) {
      return "$this->_class-failed-rhs-facture";
    }
    // Suppression des lignes d'activités du RHS
    /** @var CLigneActivitesRHS $_line */
    foreach ($this->loadBackRefs("lines") as $_line) {
      if ($_line->auto) {
        $_line->delete();
      }
    }
    $this->loadBackRefs("lines");

    // Chargement du séjour
    $sejour = $this->loadRefSejour();

    // Ajout des lignes d'activités 
    $evenementSSR            = new CEvenementSSR();
    $evenementSSR->sejour_id = $sejour->_id;
    $evenementSSR->realise   = 1;

    /** @var CEvenementSSR[] $evenements */
    $evenements = $evenementSSR->loadMatchingList();

    foreach ($evenements as $_evenement) {
      $evenementRhs = $_evenement->getRHS();
      if ($evenementRhs->_id != $this->_id || !$evenementRhs->_id) {
        continue;
      }

      $therapeute = $_evenement->loadRefTherapeute();
      if ($_evenement->seance_collective_id) {
        $therapeute = $_evenement->loadRefSeanceCollective()->loadRefTherapeute();
      }
      $intervenant            = $therapeute->loadRefIntervenantCdARR();
      $code_intervenant_cdarr = $intervenant->code;

      // Actes CdARRs
      $actes_cdarr = $_evenement->loadRefsActesCdARR();
      foreach ($actes_cdarr as $_acte_cdarr) {
        $ligne                         = new CLigneActivitesRHS();
        $ligne->rhs_id                 = $this->_id;
        $ligne->executant_id           = $therapeute->_id;
        $ligne->code_activite_cdarr    = $_acte_cdarr->code;
        $ligne->code_intervenant_cdarr = $code_intervenant_cdarr;
        $ligne->loadMatchingObject();
        $ligne->crementDay($_evenement->debut, $_evenement->realise ? "inc" : "dec");
        $ligne->auto = "1";
        $ligne->store();
      }

      // Actes CsARRs
      $actes_csarr = $_evenement->loadRefsActesCsARR();
      foreach ($actes_csarr as $_acte_csarr) {
        $ligne                         = new CLigneActivitesRHS();
        $ligne->rhs_id                 = $this->_id;
        $ligne->executant_id           = $therapeute->_id;
        $ligne->code_activite_csarr    = $_acte_csarr->code;
        $ligne->code_intervenant_cdarr = $code_intervenant_cdarr;
        $ligne->modulateurs            = $_acte_csarr->modulateurs;
        $ligne->phases                 = $_acte_csarr->phases;
        $ligne->commentaire            = $_acte_csarr->commentaire;
        $ligne->extension              = $_acte_csarr->extension;
        $ligne->nb_patient_seance      = $_evenement->nb_patient_seance;
        $ligne->nb_intervenant_seance  = $_evenement->nb_intervenant_seance;
        $ligne->loadMatchingObject(null, "ligne_id");
        $ligne->crementDay($_evenement->debut, $_evenement->realise ? "inc" : "dec", $_acte_csarr->quantite);
        $ligne->auto = "1";
        $ligne->store();
      }

      $presta_ch               = array();
      $presta_ch["presta_ssr"] = $_evenement->loadRefsActesPrestationsSSR();
      foreach ($presta_ch as $type => $_prestas) {
        foreach ($_prestas as $_presta) {
          $ligne                = new CLigneActivitesRHS();
          $ligne->rhs_id        = $this->_id;
          $ligne->executant_id  = $therapeute->_id;
          $ligne->code_activite = $_presta->code;
          $ligne->type_activite = $_presta->type;
          $ligne->loadMatchingObject();
          $ligne->crementDay($_evenement->debut, $_evenement->realise ? "inc" : "dec", $_presta->quantite);
          $ligne->auto = "1";
          $ligne->store();
        }
      }
    }

    // Gestion des administrations
    /** @var CActeCdARR $_acte_cdarr_adm */
    foreach ($sejour->loadBackRefs("actes_cdarr") as $_acte_cdarr_adm) {
      $administration         = $_acte_cdarr_adm->loadRefAdministration();
      $therapeute             = $administration->loadRefAdministrateur();
      $intervenant            = $therapeute->loadRefIntervenantCdARR();
      $code_intervenant_cdarr = $intervenant->code;

      $ligne                         = new CLigneActivitesRHS();
      $ligne->rhs_id                 = $this->_id;
      $ligne->executant_id           = $therapeute->_id;
      $ligne->code_activite_cdarr    = $_acte_cdarr_adm->code;
      $ligne->code_intervenant_cdarr = $code_intervenant_cdarr;
      $ligne->loadMatchingObject();
      $ligne->crementDay($administration->dateTime, "inc", $_acte_cdarr_adm->quantite);
      $ligne->auto = "1";
      $ligne->store();
    }
  }

  /**
   * Constuit les totaux
   *
   * @return int[]
   */
  function buildTotaux() {
    // Initialisation des totaux
    $totaux         = array();
    $types_activite = array();

    // Comptage et classement par executants
    $executants         = array();
    $lines_by_executant = array();
    $lines_by_executant_by_code = array();

    /** @var CLigneActivitesRHS $_line */
    foreach ($this->loadBackRefs("lines") as $_line) {
      // Cas des actes CdARR
      if ($_line->code_activite_cdarr) {
        $activite                   = $_line->loadRefActiviteCdARR();
        $type                       = $activite->loadRefTypeActivite();
        $types_activite[$type->_id] = $type;
        @$totaux[$type->code] += $_line->_qty_total;
        $this->_count_cdarr += 1;
      }

      // Cas des actes CsARR
      if ($_line->code_activite_csarr) {
        $activite = $_line->loadRefActiviteCsARR();
        $activite->loadRefHierarchie();
      }

      $_line->loadRefIntervenantCdARR();
      /** @var CMediusers $executant */
      $executant = $_line->loadFwdRef("executant_id", true);
      $executant->loadRefsFwd();
      $executant->loadRefIntervenantCdARR();

      // Use guids for keys instead of ids to prevent key corruption by multisorting
      $executants[$executant->_guid]           = $executant;
      $lines_by_executant[$executant->_guid][] = $_line;
    }

    // Sort by executants then by code
      $order_view = CMbArray::pluck($executants, "_view");
    array_multisort($order_view, SORT_ASC, $lines_by_executant);
    foreach ($lines_by_executant as $executant_guid => &$_lines) {
        if (!in_array(null, CMbArray::pluck($_lines, "code_activite_cdarr"))) {
            $order = CMbArray::pluck($_lines, "code_activite_cdarr");
            array_multisort($order, SORT_ASC, $_lines);
        }
        elseif (!in_array(null, CMbArray::pluck($_lines, "code_activite_csarr"))) {
            $order = CMbArray::pluck($_lines, "code_activite_csarr");
            array_multisort($order, SORT_ASC, $_lines);
        }
    }

    // For the light view in RHS
    foreach ($lines_by_executant as $executant_guid => $lines) {
        foreach ($lines as $_line) {
            // Cas des actes CdARR
            if ($_line->code_activite_cdarr) {
                $activite                   = $_line->loadRefActiviteCdARR();
                $code = $activite->_ref_type_activite->code;
                $code_key = $_line->code_activite_cdarr;
            }

            // Cas des actes CsARR
            if ($_line->code_activite_csarr) {
                $activite = $_line->loadRefActiviteCsARR();
                $activite->loadRefHierarchie();

                $code = $activite->_ref_hierarchie->code;
                $code_key = $_line->code_activite_csarr;
            }

            if (!isset($lines_by_executant_by_code[$executant_guid][$code_key])) {
                $new_line = new CLigneActivitesRHS();
            }

            $lines_by_executant_by_code[$executant_guid][$code_key]["activite"] = $activite;
            $lines_by_executant_by_code[$executant_guid][$code_key]["code"] = $code;
            $lines_by_executant_by_code[$executant_guid][$code_key]["modulateurs"] = $_line->modulateurs;
            $lines_by_executant_by_code[$executant_guid][$code_key]["phases"] = $_line->phases;
            $lines_by_executant_by_code[$executant_guid][$code_key]["nb_patient_seance"] = $_line->nb_patient_seance;
            $lines_by_executant_by_code[$executant_guid][$code_key]["nb_intervenant_seance"] = $_line->nb_intervenant_seance;
            $lines_by_executant_by_code[$executant_guid][$code_key]["extension"] = $_line->extension;
            $lines_by_executant_by_code[$executant_guid][$code_key]["commentaire"] = $_line->commentaire;
            $lines_by_executant_by_code[$executant_guid][$code_key]["libelle"] = $activite->libelle;

            foreach (CRHS::$days as $_day) {
                $new_line->{"qty_$_day"} += (int)$_line->{"qty_$_day"};
            }

            $new_line->auto = $_line->auto;

            $lines_by_executant_by_code[$executant_guid][$code_key]["line"] = $new_line;
        }
    }

    $this->_ref_lines_by_executant = $lines_by_executant;
    $this->_ref_executants         = $executants;
    $this->_ref_types_activite     = $types_activite;
    $this->_ref_lines_by_executant_by_code = $lines_by_executant_by_code;

    return $this->_totaux = $totaux;
  }

  /**
   * Charge les diagnostics CIM FPP , MMP, AE, DAS et DAD
   *
   * @return void
   */
  function loadRefDiagnostics() {
    $this->_diagnostic_FPP = $this->FPP ? CCodeCIM10::get($this->FPP) : null;
    $this->_diagnostic_MMP = $this->MMP ? CCodeCIM10::get($this->MMP) : null;
    $this->_diagnostic_AE  = $this->AE ? CCodeCIM10::get($this->AE) : null;
    $this->loadRefDASAndDAD();
  }

  /**
   * Charge les diagnostics associés significatifs et à visée documentaire
   *
   * @return array
   */
  function loadRefDASAndDAD() {
    $codes_DA = array('DAS' => array(), 'DAD' => array());

    if ($this->DAS) {
      foreach (explode("|", $this->DAS) as $_das) {
        /** @var CCIM10 $code */
        $code_DAS = CCIM10::get($_das);
        if ($code_DAS->exist) {
          $codes_DA['DAS'][] = $code_DAS;
        }
      }
    }

    if ($this->DAD) {
      foreach (explode("|", $this->DAD) as $_dad) {
        /** @var CCIM10 $code */
        $code_DAD = CCIM10::get($_dad);
        if ($code_DAD->exist) {
          $codes_DA['DAD'][] = $code_DAD;
        }
      }
    }

    return $this->_ref_DAS_DAD = $codes_DA;
  }

  /**
   * Charge les actes de prestations des lignes d'activité qui composent le RHS
   *
   * @return CLigneActivitesRHS[]
   */
  function loadRefActesPrestationSSR() {
    $where_presta       = array();
    $where_presta[]     = "type_activite = 'presta_ssr'";
    $this->_prestas_ssr = $this->loadRefLignesActivites($where_presta);

    foreach ($this->_prestas_ssr as $_line) {
      $_line->loadRefExecutant();
      $_line->loadRefPrestationSSR();
    }

    return $this->_prestas_ssr;
  }

    /**
     * @return CGroups
     */
    public function loadRelGroup(): CGroups
    {
        return $this->loadRefSejour()->loadRelGroup();
    }
}
