<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CDiscipline;

/**
 * Classe CChambre.
 * @abstract Gère les chambre d'hospitalisation
 * - contient des lits
 */
class CChambre extends CInternalStructure {

  static $_prefixe;

  // DB Table key
  public $chambre_id;

  // DB References
  public $service_id;

  // DB Fields
  public $nom;
  public $caracteristiques;       // côté rue, fenêtre, lit accompagnant, ...
  public $lits_alpha;
  public $annule;
  public $is_waiting_room;        //salle d'attente
  public $is_examination_room;    // salle d'examen
  public $is_sas_dechoc;          // sas de dechoquage
  public $rank;

  // Form Fields
  public $_nb_lits_dispo;
  public $_nb_affectations;
  public $_overbooking;
  public $_ecart_age;
  public $_genres_melanges;
  public $_chambre_seule;
  public $_chambre_double;
  public $_conflits_chirurgiens;
  public $_conflits_pathologies;

  // Object references
  /** @var CService */
  public $_ref_service;

  /** @var CLit[] */
  public $_ref_lits = [];

  /** @var CEmplacement */
  public $_ref_emplacement;

  public function __construct() {
    parent::__construct();
    CChambre::$_prefixe = CAppUI::gconf("dPhospi CChambre prefixe");
  }

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec              = parent::getSpec();
    $spec->table       = 'chambre';
    $spec->key         = 'chambre_id';
    $spec->measureable = true;

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                        = parent::getProps();
    $props["user_id"]            .= " back|chambres";
    $props["service_id"]          = "ref notNull class|CService seekable back|chambres";
    $props["nom"]                 = "str notNull seekable";
    $props["caracteristiques"]    = "text";
    $props["lits_alpha"]          = "bool default|0";
    $props["annule"]              = "bool";
    $props["is_waiting_room"]     = "bool";
    $props["is_examination_room"] = "bool";
    $props["is_sas_dechoc"]       = "bool";
    $props["rank"]                = "num max|999";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_shortview = self::$_prefixe . $this->nom;
    $this->_view      = $this->_shortview;
  }

  /**
   * Load service
   *
   * @return CMbObject|null
   */
  function loadRefService() {
    return $this->_ref_service = $this->loadFwdRef("service_id", true);
  }

  /**
   * @see parent::loadRefsFwd()
   * @deprecated
   */
  function loadRefsFwd() {
    $this->loadRefService();
  }

  /**
   * @see parent::mapEntityTo()
   */
  function mapEntityTo() {
    $this->_name       = $this->nom;
    $this->description = $this->caracteristiques;
  }

  /**
   * @see parent::mapEntityFrom()
   */
  function mapEntityFrom() {
    if ($this->_name != null) {
      $this->nom = $this->_name;
    }
    if ($this->description != null) {
      $this->caracteristiques = $this->description;
    }
  }

  /**
   * Load lits
   *
   * @param bool $annule Annulé
   *
   * @return CLit[]
   */
  function loadRefsLits($annule = false) {

    $where = [];
    if (!$annule) {
      $where["annule"] = " ='0'";
    }

    $order = "ISNULL(lit.rank), lit.rank, ";
    $order .= ($this->lits_alpha) ? "lit.nom ASC" : "lit.nom DESC";


    return $this->_ref_lits = $this->loadBackRefs('lits', $order, null, null, null, null, null, $where, false);
  }

  /**
   * Load emplacements
   *
   * @return CEmplacement
   */
  function loadRefEmplacement() {
      return $this->_ref_emplacement = $this->loadUniqueBackRef('emplacement');
  }

  /**
   * @see parent::loadRefsBack()
   */
  function loadRefsBack() {
    $this->loadRefsLits();
  }

  /**
   * @see parent::getPerm()
   */
  function getPerm($permType) {
    $this->loadRefService();

    return ($this->_ref_service->getPerm($permType));
  }

  /**
   * Check room
   *
   * @param string $date Verification date
   * @param int    $mode With affectations done
   *
   * @return void
   */
  function checkChambre($date = null, $mode = 0) {
    if (!$date) {
      $date = CMbDT::date();
    }

    $today = $date == CMbDT::date();
    $now   = CMbDT::dateTime();

    static $pathos = null;
    if (!$pathos) {
      $pathos = new CDiscipline();
    }

    assert($this->_ref_lits !== null);
    $this->_nb_lits_dispo = count($this->_ref_lits);

    /** @var CAffectation[] $listAff */
    $listAff = array();

    $this->_chambre_seule        = 0;
    $this->_chambre_double       = 0;
    $this->_conflits_pathologies = 0;
    $this->_ecart_age            = 0;
    $this->_genres_melanges      = false;
    $this->_conflits_chirurgiens = 0;

    foreach ($this->_ref_lits as $lit) {
      assert($lit->_ref_affectations !== null);

      // overbooking
      $lit->checkOverBooking();
      $this->_overbooking += $lit->_overbooking;

      // Lits dispo
      if (count($lit->_ref_affectations)) {
        foreach ($lit->_ref_affectations as $_aff) {
          if ($mode == 0 && $today) {
            if ($_aff->sejour_id && !$_aff->_ref_sejour) {
              $_aff->loadRefSejour();
            }
            if ((!$_aff->sejour_id || $_aff->effectue || $_aff->_ref_sejour->sortie_reelle) && $_aff->sortie < $now) {
              continue;
            }
          }

          $this->_nb_lits_dispo--;
          break;
        }
      }

      // Liste des affectations
      foreach ($lit->_ref_affectations as $aff) {
        $listAff[] = $aff;
      }
    }
    $this->_nb_affectations = count($listAff);

    $systeme_presta = CAppUI::gconf("dPhospi prestations systeme_prestations");

    foreach ($listAff as $affectation1) {
      if (!$affectation1->sejour_id) {
        continue;
      }
      $sejour1     = $affectation1->_ref_sejour;
      $patient1    = $sejour1->_ref_patient;
      $chirurgien1 = $sejour1->_ref_praticien;

      if ($systeme_presta == "standard") {
        if ((count($this->_ref_lits) == 1) && $sejour1->chambre_seule == 0) {
          $this->_chambre_double++;
        }

        if ((count($this->_ref_lits) > 1) && $sejour1->chambre_seule == 1) {
          $this->_chambre_seule++;
        }
      }

      foreach ($listAff as $affectation2) {
        if (!$affectation2->sejour_id) {
          continue;
        }

        if ($affectation1->_id == $affectation2->_id) {
          continue;
        }

        if ($affectation1->lit_id == $affectation2->lit_id) {
          continue;
        }

        if (!$affectation1->collide($affectation2)) {
          continue;
        }

        $sejour2     = $affectation2->_ref_sejour;
        $patient2    = $sejour2->_ref_patient;
        $chirurgien2 = $sejour2->_ref_praticien;

        // Conflits de pathologies
        if (!$pathos->isCompat($sejour1->pathologie, $sejour2->pathologie, $sejour1->septique, $sejour2->septique)) {
          $this->_conflits_pathologies++;
        }

        // Ecart d'âge
        $ecart            = max($patient1->_annees, $patient2->_annees) - min($patient1->_annees, $patient2->_annees);
        $this->_ecart_age = max($ecart, $this->_ecart_age);

        // Genres mélangés
        if (($patient1->sexe != $patient2->sexe) && (($patient1->sexe == "m") || ($patient2->sexe == "m"))) {
          $this->_genres_melanges = true;
        }

        // Conflit de chirurgiens
        if (($chirurgien1->user_id != $chirurgien2->user_id) && ($chirurgien1->function_id == $chirurgien2->function_id)) {
          $this->_conflits_chirurgiens++;
        }
      }
    }
    $this->_conflits_pathologies /= 2;
    $this->_conflits_chirurgiens /= 2;
  }

  /**
   * Construit le tag Chambre en fonction des variables de configuration
   *
   * @param string $group_id Permet de charger l'id externe d'une chambre pour un établissement donné si non null
   *
   * @return string|null
   */
  static function getTagChambre($group_id = null) {
    // Pas de tag Chambre
    if (null == $tag_chambre = CAppUI::gconf("dPhospi CChambre tag")) {
      return null;
    }

    // Permettre des id externes en fonction de l'établissement
    $group = CGroups::loadCurrent();
    if (!$group_id) {
      $group_id = $group->_id;
    }

    return str_replace('$g', $group_id, $tag_chambre);
  }

  /**
   * @see parent::getDynamicTag
   */
  function getDynamicTag() {
    return $this->gconf("tag");
  }
}
