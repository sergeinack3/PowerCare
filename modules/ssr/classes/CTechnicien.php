<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CPlageConge;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Technicien de SSR, association entre un plateau technique et un utilisateur
 */
class CTechnicien extends CMbObject {
  // DB Table key
  public $technicien_id;

  // References fields
  public $plateau_id;
  public $kine_id;

  // DB Fields
  public $actif;

  // Form fields
  public $_transfer_id;
  public $_count_sejours_date;

  // References
  /** @var CMediusers */
  public $_ref_kine;
  /** @var CPlateauTechnique */
  public $_ref_plateau;

  // Distant references
  /** @var CPlageConge */
  public $_ref_conge_date;
  /** @var CSejour[] */
  public $_ref_sejours_date;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'technicien';
    $spec->key   = 'technicien_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props               = parent::getProps();
    $props["plateau_id"] = "ref notNull class|CPlateauTechnique back|techniciens";
    $props["kine_id"]    = "ref notNull class|CMediusers back|techniciens";
    $props["actif"]      = "bool notNull default|1";

    $props["_transfer_id"]        = "ref class|CTechnicien";
    $props["_count_sejours_date"] = "num";

    return $props;
  }

  /**
   * @see parent::store()
   */
  function store() {
    // Transfert de séjours vers un autre technicien
    if ($this->_transfer_id) {
      foreach ($this->loadRefsSejours(CMbDT::date()) as $_sejour) {
        $bilan                = $_sejour->loadRefBilanSSR();
        $bilan->technicien_id = $this->_transfer_id;
        if ($msg = $bilan->store()) {
          return $msg;
        }
      }
    }

    return parent::store();
  }

  /**
   * Update view under certain changes
   *
   * @return void
   */
  function updateView() {
    $parts = array();
    if ($this->_ref_kine && $this->_ref_kine->_id) {
      $parts[] = $this->_ref_kine->_view;
    }

    if ($this->_ref_plateau && $this->_ref_plateau->_id) {
      $parts[] = $this->_ref_plateau->_view;
    }

    $this->_view = implode(" &ndash; ", $parts);
  }

  /**
   * Charge le plateau technique
   *
   * @return CPlateauTechnique
   */
  function loadRefPlateau() {
    $plateau = $this->loadFwdRef("plateau_id", true);
    $this->updateView();

    return $this->_ref_plateau = $plateau;
  }


  /**
   * Charge le kiné technicien
   *
   * @return CMediusers
   */
  function loadRefKine() {
    /** @var CMediusers $kine */
    $kine = $this->loadFwdRef("kine_id", true);
    $kine->loadRefFunction();
    $this->updateView();

    return $this->_ref_kine = $kine;
  }

  /**
   * Charge la plage de congés pour un technicien à une date donnée
   *
   * @param string $date Date de référence
   *
   * @return CPlageConge
   */
  function loadRefCongeDate($date) {
    $this->_ref_conge_date = new CPlageConge();
    $this->_ref_conge_date->loadFor($this->kine_id, $date);

    return $this->_ref_conge_date;
  }

  /**
   * Retourne les contraintes de requête sur le chargement des séjours
   *
   * @param string $type Type de séjour
   *
   * @return array
   */
  function getWhereSejoursDate($type = "ssr") {
    $group                            = CGroups::loadCurrent();
    $where["type"]                    = "= '$type'";
    $where["group_id"]                = "= '$group->_id'";
    $where["annule"]                  = "= '0'";
    $where["bilan_ssr.technicien_id"] = "= '$this->_id'";

    return $where;
  }

  /**
   * Compte les séjours pour le technicien à une date de référence
   *
   * @param string $date Date de référence
   * @param string $type Type de séjour concerné
   *
   * @return int
   */
  function countSejoursDate($date, $type = "ssr") {
    $where = $this->getWhereSejoursDate($type);

    $leftjoin = [
      "bilan_ssr" => "bilan_ssr.sejour_id = sejour.sejour_id"
    ];

    return $this->_count_sejours_date = CSejour::countForDate($date, $where, $leftjoin);
  }

  /**
   * Charge les séjours pour ce technicien en tant que référent à une date donnée
   *
   * @param string $date Date de reference
   * @param string $type Type de séjour concerné
   *
   * @return CSejour[]
   */
  function loadRefsSejours($date, $type = "ssr") {
    $where = $this->getWhereSejoursDate($type);

    $leftjoin = [
      "bilan_ssr" => "bilan_ssr.sejour_id = sejour.sejour_id"
    ];

    return $this->_ref_sejours_date = CSejour::loadListForDate($date, $where, null, null, null, $leftjoin);
  }
}
