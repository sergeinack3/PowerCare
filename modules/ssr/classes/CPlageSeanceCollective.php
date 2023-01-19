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
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CElementPrescription;

/**
 * La trame de planning collectif est composée de plages
 */
class CPlageSeanceCollective extends CMbObject {
  // DB Table key
  public $plage_id;

  // DB Fields
  public $element_prescription_id;
  public $trame_id;
  public $user_id;
  public $therapeute2_id;
  public $therapeute3_id;
  public $equipement_id;
  public $nom;
  public $debut;
  public $day_week;
  public $duree;
  public $commentaire;
  public $active;
  public $niveau;

  //Distant Fields
  public $_csarrs;
  public $_prestas_ssr;
  public $_prestas_quantity;
  public $_readonly;
  public $_update_actes = array();
  public $_update_kine = false;
  public $_inactivable;

  // Collections
  /** @var CMediusers */
  public $_ref_user;
  /** @var CMediusers */
  public $_ref_intervenant2;
  /** @var CMediusers */
  public $_ref_intervenant3;
  /** @var CElementPrescription */
  public $_ref_element_prescription;
  /** @var CTrameSeanceCollective */
  public $_ref_trame;
  /** @var CEquipement */
  public $_ref_equipement;
  /** @var array|CSejour[] */
  public $_ref_sejours_affectes;

  /** @var  CActePlage[] */
  public $_ref_actes;
  /** @var  CActePlage[] */
  public $_ref_csarrs = array();
  /** @var  CActePlage[] */
  public $_ref_prestas = array();
  /** @var  CActePlage[] */
  public $_ref_actes_by_type;


  static public $categories_actes = array(
    "CActeCsARR|pl" => "_ref_csarrs",
    "CPrestaSSR"    => "_ref_prestas",
  );

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'plage_collective';
    $spec->key   = 'plage_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                            = parent::getProps();
    $props["trame_id"]                = "ref notNull class|CTrameSeanceCollective back|trame_plage_ssr";
    $props["user_id"]                 = "ref notNull class|CMediusers back|plage_ssr_user";
    $props["therapeute2_id"]          = "ref class|CMediusers back|plage_ssr_user2";
    $props["therapeute3_id"]          = "ref class|CMediusers back|plage_ssr_user3";
    $props["element_prescription_id"] = "ref notNull class|CElementPrescription back|element_plage_ssr";
    $props["equipement_id"]           = "ref class|CEquipement back|eqt_plage_collective";
    $props["nom"]                     = "str";
    $props["day_week"]                = "enum notNull list|monday|tuesday|wednesday|thursday|friday|saturday|sunday";
    $props["debut"]                   = "time notNull";
    $props["duree"]                   = "num notNull min|10";
    $props["commentaire"]             = "text";
    $props["active"]                  = "bool notNull default|1";
    $props["niveau"]                  = "enum list|1|2|3|4|5 notNull default|3";

    $props["_csarrs"]      = "text";
    $props["_prestas_ssr"] = "text";
    $props["_prestas_quantity"] = "text";
    $props["_readonly"]    = "bool default|0";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->loadRefUser();
    $this->loadRefTrame();
    $this->_view = $this->_ref_trame->nom . " - ";
    $this->_view .= CAppUI::tr('CPlageSeanceCollective.day_week.' . $this->day_week) . " " . CMbDT::format($this->debut, "%Hh%M");
  }

  /**
   * @see parent::loadView()
   */
  function loadView() {
    parent::loadView();
    $this->loadRefsSejoursAffectes(false);
    $this->loadRefsActes();
  }

  function store() {
    $this->completeField("user_id", "therapeute2_id", "therapeute3_id");

    if (!$this->_readonly) {
      $this->_update_actes = array();
      if ($this->_csarrs) {
        foreach ($this->_csarrs as $_csarr) {
          $this->_update_actes["csarr"][] = $_csarr."-1";
        }
      }
      if ($this->_prestas_ssr) {
        foreach ($this->_prestas_ssr as $key_presta => $_presta) {
          $this->_update_actes["presta"][] = $_presta."-".$this->_prestas_quantity[$key_presta];
        }
      }
      if ($this->_id) {
        $actes = $this->loadRefsActes();
        foreach ($actes as $_acte) {
          if ($msg = $_acte->delete()) {
            return $msg;
          }
        }
      }
    }
    if ($this->_id && ($this->fieldModified("user_id")
        || $this->fieldModified("therapeute2_id")
        || $this->fieldModified("therapeute3_id"))
    ) {
      $this->_update_kine = true;
    }

    if ($msg = parent::store()) {
      return $msg;
    }

    if (count($this->_update_actes)) {
      foreach ($this->_update_actes as $type_acte => $_actes) {
        foreach ($_actes as $_acte) {
          list($_code, $_quantite) = explode("-", $_acte);
          $acte           = new CActePlage();
          $acte->plage_id = $this->_id;
          $acte->type     = $type_acte;
          $acte->code     = $_code;
          $acte->quantite = $_quantite;
          if ($msg = $acte->store()) {
            return $msg;
          }
        }
      }
    }

    if ($this->_update_kine) {
      $evts_collectifs = $this->loadRefsEvenements();
      $now             = CMbDT::dateTime();
      foreach ($evts_collectifs as $_evenement) {
        if ($_evenement->realise || $_evenement->annule || $_evenement->debut < $now) {
          continue;
        }
        $_evenement->therapeute_id  = $this->user_id;
        $_evenement->therapeute2_id = $this->therapeute2_id;
        $_evenement->therapeute3_id = $this->therapeute3_id;
        if ($msg = $_evenement->store()) {
          return $msg;
        }
      }
    }

    return null;
  }

  /**
   * Charge l'utilisateur
   *
   * @return CMediusers
   */
  function loadRefUser() {
    return $this->_ref_user = $this->loadFwdRef("user_id");
  }

  /**
   * Charge les autres intervenants
   *
   * @return void
   */
  function loadRefsAllIntervenant() {
    $this->_ref_intervenant2 = $this->loadFwdRef("therapeute2_id");
    $this->_ref_intervenant3 = $this->loadFwdRef("therapeute3_id");
  }

  /**
   * Charge l'équipement
   *
   * @return CEquipement
   */
  function loadRefEquipement() {
    return $this->_ref_equipement = $this->loadFwdRef("equipement_id");
  }

  /**
   * Charge l'élément de prescription
   *
   * @return CElementPrescription
   */
  function loadRefElementPrescription() {
    return $this->_ref_element_prescription = $this->loadFwdRef("element_prescription_id");
  }

  /**
   * Charge la trame
   *
   * @return CTrameSeanceCollective
   */
  function loadRefTrame() {
    return $this->_ref_trame = $this->loadFwdRef("trame_id");
  }

  /**
   * Charge les événements de la plage collective
   *
   * @return CEvenementSSR[]
   */
  function loadRefsEvenements() {
    return $this->_ref_evenements = $this->loadBackRefs("plage_evt_ssr");
  }

  /**
   * Récupère la liste des séjours affectés à la plage
   *
   * @return array|CSejour[]
   */
  function loadRefsSejoursAffectes($only_ids = true) {
    $ljoin                                       = array();
    $ljoin["evenement_ssr"]                      = "evenement_ssr.sejour_id = sejour.sejour_id";
    $where                                       = array();
    $where["sejour.annule"]                      = " = '0'";
    $where["evenement_ssr.plage_id"]             = " = '$this->_id'";
    $where["evenement_ssr.seance_collective_id"] = " IS NOT NULL";
    $where[]                                     = "DAYNAME(evenement_ssr.debut) = '" . $this->day_week . "'";
    $where[]                                     = "TIME(evenement_ssr.debut) BETWEEN '" . CMbDT::time($this->debut)
      . "' AND '" . CMbDT::time("+$this->duree minutes", $this->debut) . "'";
    $where[]                                     = "DATE(debut) >= '" . CMbDT::date() . "'";
    $sejour                                      = new CSejour();
    if ($only_ids) {
      return $this->_ref_sejours_affectes = $sejour->loadIds($where, null, null, "sejour.sejour_id", $ljoin);
    }
    else {
      $this->_ref_sejours_affectes = $sejour->loadList($where, null, null, "sejour.sejour_id", $ljoin);
      foreach ($this->_ref_sejours_affectes as $_sejour) {
        $_sejour->loadRefPatient();
      }

      return $this->_ref_sejours_affectes;
    }
  }

  /**
   * Charge tous les actes
   *
   * @return CActePlage[] Actes classés par type
   */
  function loadRefsActes() {
    $this->_ref_actes = $this->loadBackRefs("actes_plage", "type, code");
    foreach ($this->_ref_actes as $_acte) {
      switch ($_acte->type) {
        case "csarr":
          $this->_ref_csarrs[$_acte->_id] = $_acte;
          break;
        case "presta":
          $this->_ref_prestas[$_acte->_id] = $_acte;
          break;

        default:
      }
    }
    $this->_ref_actes_by_type = array(
      "csarr"  => $this->_ref_csarrs,
      "presta" => $this->_ref_prestas,
    );

    return $this->_ref_actes;
  }

  function rangeActesOther() {
    $this->_ref_actes_other = $this->_ref_actes_by_type;
    if ($this->element_prescription_id) {
      foreach (array("csarr" => "_ref_csarrs", "presta" => "_refs_presta_ssr") as $type => $_prop) {
        foreach ($this->_ref_element_prescription->$_prop as $_acte_prescription) {
          $_acte_prescription->_checked = 0;
          $_acte_prescription->_quantite = 0;
          foreach ($this->_ref_actes_other[$type] as $acte_plage) {
            if ($acte_plage->code == $_acte_prescription->code) {
              $_acte_prescription->_checked = 1;
              $_acte_prescription->_quantite += $acte_plage->quantite;
              unset($this->_ref_actes_other[$type][$acte_plage->_id]);
              break;
            }
          }
          if ($_acte_prescription->_checked == 0) {
            $_acte_prescription->_quantite = $_acte_prescription->quantite;
          }
        }
      }
    }
  }

  /**
   * Teste si la plage peut être inactivée
   *
   * @return bool
   */
  function isInactivable() {
    $evenement_ssr  = new CEvenementSSR();
    $where          = array(
      "plage_id" => "= '$this->_id'",
      "debut"    => "> '" . CMbDT::dateTime() . "'",
    );
    $evenements_ssr = $evenement_ssr->countList($where);

    return $this->_inactivable = $evenements_ssr === "0";
  }
}
