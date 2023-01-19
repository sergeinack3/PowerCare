<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CMbRange;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CPlageConge;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Bilan d'entrée SSR
 */
class CBilanSSR extends CMbObject {
  // DB Table key
  public $bilan_id;

  // DB Fields
  public $sejour_id;
  public $technicien_id;
  public $entree;
  public $sortie;
  public $planification;
  public $brancardage;

  public $demi_journee_1;
  public $demi_journee_2;

  // Form fields
  public $_demi_journees;
  public $_premier_jour;
  public $_dernier_jour;
  public $_encours;

  /** @var CTechnicien */
  public $_ref_technicien;

  /** @var CSejour */
  public $_ref_sejour;

  // Distant Fields
  public $_kine_referent_id;
  public $_kine_journee_id;
  public $_prat_demandeur_id;
  public $_sejour_demandeur_id;

  // Distant references
  /** @var CMediusers */
  public $_ref_kine_referent;

  /** @var CMediusers */
  public $_ref_kine_journee;

  /** @var CMediusers */
  public $_ref_prat_demandeur;

  /** @var CSejour */
  public $_ref_sejour_demandeur;

  static $demi_journees = array(
    "0" => array(
      "0" => "none",
      "1" => "pm",
    ),
    "1" => array(
      "0" => "am",
      "1" => "all",
    ),
  );

  /**
   * Surcharge de la spécification d'objet
   *
   * @return CMbObjectSpec
   */
  function getSpec() {
    $spec                       = parent::getSpec();
    $spec->table                = "bilan_ssr";
    $spec->key                  = "bilan_id";
    $spec->uniques["sejour_id"] = array("sejour_id");
    $spec->events               = array(
      "fiche_autonomie" => array(
        "reference1" => array("CSejour", "sejour_id"),
        "reference2" => array("CPatient", "sejour_id.patient_id"),
      ),
    );

    return $spec;
  }

  /**
   * Surcharge de spécifications de propriétés
   *
   * @return string[]
   */
  function getProps() {
    $props = parent::getProps();

    // DB Fields
    $props["sejour_id"]     = "ref notNull class|CSejour show|0 back|bilan_ssr";
    $props["technicien_id"] = "ref class|CTechnicien back|bilan_ssr";
    $props["entree"]        = "text helped";
    $props["sortie"]        = "text helped";
    $props["planification"] = "bool default|1";
    $props["brancardage"]   = "bool default|0";

    $props["demi_journee_1"] = "bool default|0";
    $props["demi_journee_2"] = "bool default|0";

    // Form fields
    $props["_demi_journees"] = "enum list|none|am|pm|all";
    $props["_premier_jour"]  = "date";
    $props["_dernier_jour"]  = "date";

    // Distant Fields
    $props["_kine_referent_id"]  = "ref class|CMediusers";
    $props["_kine_journee_id"]   = "ref class|CMediusers";
    $props["_prat_demandeur_id"] = "ref class|CMediusers";

    return $props;
  }

  /**
   * @see parent::store()
   */
  function store() {
    // Transférer les événéments de l'ancien référent vers le nouveau
    if ($this->technicien_id && $this->fieldAltered("technicien_id")) {
      $technicien     = $this->loadRefTechnicien();
      $old_technicien = CTechnicien::find($this->_old->technicien_id);
      $evenement                = new CEvenementSSR();
      $evenement->therapeute_id = $old_technicien->kine_id;
      $evenement->sejour_id     = $this->sejour_id;
      foreach ($evenement->loadMatchingList() as $_evenement) {
        /** @var CEvenementSSR $_evenement */
        if (!$_evenement->_traite) {
          $_evenement->therapeute_id = $technicien->kine_id;
          $_evenement->store();
          CAppUI::setMsg("{$_evenement->_class}-msg-modify", UI_MSG_OK);
        }
      }
    }

    return parent::store();
  }

  /**
   * Chargement du séjour
   * Calcul les premier et dernier jours ouvrés de rééducation
   *
   * @return CSejour sejour
   */
  function loadRefSejour() {
    /** @var CSejour $sejour */
    $sejour = $this->loadFwdRef("sejour_id", true);

    // Premier et dernier jour ouvré (exclusion des week-end)
    $premier_jour = CMbDT::date($sejour->entree);
    $dernier_jour = CMbDT::date($sejour->sortie);
    if ($sejour->hospit_de_jour) {
      $this->_demi_journees = self::$demi_journees[$this->demi_journee_1][$this->demi_journee_2];
    }
    else {
      $numero_premier_decalage = CMbDT::format($premier_jour, "%w");
      $numero_dernier_decalage = CMbDT::format($dernier_jour, "%w");
      $premier_jour            = CMbDT::date(in_array($numero_premier_decalage, array(5, 6)) ? "next monday" : "+1 day", $premier_jour);
      $dernier_jour            = CMbDT::date(in_array($numero_dernier_decalage, array(1, 7)) ? "last friday" : "-1 day", $dernier_jour);
    }

    $this->_premier_jour = $premier_jour;
    $this->_dernier_jour = $dernier_jour;

    return $this->_ref_sejour = $sejour;
  }

  /**
   * Chargement du technicien
   *
   * @return CTechnicien technicien
   */
  function loadRefTechnicien() {
    return $this->_ref_technicien = $this->loadFwdRef("technicien_id", true);
  }

  /**
   * Chargement du kiné référent
   *
   * @return CMediusers Kiné référent
   */
  function loadRefKineReferent() {
    $this->loadRefTechnicien();
    $technicien =& $this->_ref_technicien;
    $technicien->loadRefKine();
    $this->_ref_kine_referent = $technicien->_ref_kine;
    $this->_kine_referent_id  = $this->_ref_kine_referent->_id;

    return $this->_ref_kine_referent;
  }

  /**
   * Chargement du kiné référent et kiné journée pour une date donnée
   *
   * @param string $date Date courante if null;
   *
   * @return CMediusers Kiné journée
   */
  function loadRefKineJournee($date = null) {
    $this->loadRefKineReferent();
    $this->_ref_kine_journee = $this->_ref_kine_referent;

    // Recherche d'un remplacement
    $sejour = $this->loadRefSejour();
    foreach ($sejour->loadRefReplacements() as $_replacement) {
      if ($_replacement->_id) {
        $_replacement->loadRefConge();
        $conge = $_replacement->_ref_conge;
        if (CMbRange::in(CValue::first($date, CMbDT::date()), $conge->date_debut, $conge->date_fin)) {
          $replacer = $_replacement->loadRefReplacer();
          $replacer->loadRefFunction();
          $this->_ref_kine_journee = $_replacement->_ref_replacer;
          break;
        }
      }
    }

    $this->_kine_journee_id = $this->_ref_kine_journee->_id;

    return $this->_ref_kine_journee;
  }

  /**
   * Chargement du séjour probablement demandeur du séjour SSR
   * (dont la sortie est proche de l'entree du séjour SSR)
   *
   * @return CSejour
   */
  function loadRefSejourDemandeur() {
    // Effet de cache
    if ($this->_ref_sejour_demandeur) {
      return $this->_ref_sejour_demandeur;
    }

    // Requête
    $group_id                 = CGroups::loadCurrent()->_id;
    $sejour_ssr               = $this->loadRefSejour();
    $tolerance                = CAppUI::conf("ssr CBilanSSR tolerance_sejour_demandeur");
    $date_min                 = CMbDT::date("- $tolerance DAYS", $sejour_ssr->entree);
    $date_max                 = CMbDT::date("+ $tolerance DAYS", $sejour_ssr->entree);
    $where["sortie"]          = "BETWEEN '$date_min' AND '$date_max'";
    $where["patient_id"]      = "= '$sejour_ssr->patient_id'";
    $where["annule"]          = " = '0'";
    $where["sejour.group_id"] = " = '$group_id'";
    $where["type"]            = "NOT IN ('ssr', 'psy')";

    // Chargement
    $sejour = new CSejour;
    $sejour->loadObject($where);

    return $this->_ref_sejour_demandeur = $sejour;
  }

  /**
   * Charge le praticien demandeur sur la base du séjour demandeur
   *
   * @return CMediusers
   */
  function loadRefPraticienDemandeur() {
    $sejour    = $this->loadRefSejourDemandeur();
    $praticien = $sejour->loadRefPraticien(1);

    $this->_prat_demandeur_id = $praticien->_id;

    return $this->_ref_prat_demandeur = $praticien;
  }


  /**
   * Load Sejour for technicien at a date
   *
   * @param int    $technicien_id           Le technicien concerné
   * @param string $date                    La date de référence
   * @param bool   $show_cancelled_services Afficher ou non les services inactifs
   *
   * @return CSejour[]
   */
  static function loadSejoursSSRfor($technicien_id, $date, $show_cancelled_services = true) {
    global $m;

    $group = CGroups::loadCurrent();

    // Masquer les services inactifs
    if (!$show_cancelled_services) {
      $service            = new CService;
      $service->group_id  = $group->_id;
      $service->cancelled = "1";
      $services           = $service->loadMatchingList();
      $where[]            = "sejour.service_id IS NULL OR sejour.service_id " . CSQLDataSource::prepareNotIn(array_keys($services));
    }

    $where["type"]                    = "= '$m'";
    $where["group_id"]                = "= '$group->_id'";
    $where["annule"]                  = "= '0'";
    $where["bilan_ssr.technicien_id"] = $technicien_id ? "= '$technicien_id'" : "IS NULL";
    $leftjoin["bilan_ssr"]            = "bilan_ssr.sejour_id = sejour.sejour_id";

    return CSejour::loadListForDate($date, $where, "entree_reelle", null, null, $leftjoin);
  }

  /**
   * Calcule si la réeducation est en cours au jour donné au regard des jours ouvrés
   *
   * @param string $date Date de référence
   *
   * @return bool
   */
  function getDateEnCours($date) {
    $this->loadRefSejour();

    return $this->_encours = CMbRange::in($date, $this->_premier_jour, $this->_dernier_jour);
  }

  /**
   * Calcul si la réeducation est en cours au jour donné au regard des jours ouvrés
   *
   * @param string $date_min Date minimale
   * @param string $date_max Date maximale
   *
   * @return bool
   */
  function getDatesEnCours($date_min, $date_max) {
    $this->loadRefSejour();

    return $this->_encours = CMbRange::collides($date_min, $date_max, $this->_premier_jour, $this->_dernier_jour);
  }

  /**
   * Charge les séjours sur le congés entre deux dates
   *
   * @param CPlageConge $plage    Plage de congés concernée
   * @param string      $date_min Date minimale
   * @param string      $date_max Date maximale
   *
   * @return CSejour[]
   */
  static function loadSejoursSurConges(CPlageConge $plage, $date_min, $date_max) {
    global $m;

    $date_min = max($date_min, CMbDT::date($plage->date_debut));
    $date_max = min($date_max, CMbDT::date($plage->date_fin));
    $date_max = CMbDT::date("+1 DAY", $date_max);

    $sejour              = new CSejour();
    $ljoin["bilan_ssr"]  = "bilan_ssr.sejour_id     = sejour.sejour_id";
    $ljoin["technicien"] = "bilan_ssr.technicien_id = technicien.technicien_id";

    $where                       = array();
    $where["type"]               = "= '$m'";
    $where["sejour.annule"]      = "!= '1'";
    $where["sejour.entree"]      = "<= '$date_max'";
    $where["sejour.sortie"]      = ">= '$date_min'";
    $where["technicien.kine_id"] = " = '$plage->user_id'";

    return $sejour->loadGroupList($where, null, null, null, $ljoin);
  }
}
