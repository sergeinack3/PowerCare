<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Urgences;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Réservation de box dans les urgences
 */
class CRPUReservationBox extends CMbObject {
  public $reservation_id;
  public $rpu_id;
  public $lit_id;

  /** @var CRPU */
  public $_ref_rpu;
  /** @var CLit */
  public $_ref_lit;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec                 = parent::getSpec();
    $spec->table          = "rpu_reservation";
    $spec->key            = "reservation_id";
    $spec->uniques["rpu"] = array("rpu_id");

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props           = parent::getProps();
    $props["rpu_id"] = "ref notNull class|CRPU back|reservation_rpu";
    $props["lit_id"] = "ref notNull class|CLit back|reservation_box";

    return $props;
  }

  /**
   * Charge le RPU
   *
   * @return CRPU
   */
  function loadRefRPU() {
    return $this->_ref_rpu = $this->loadFwdRef("rpu_id", true);
  }

  /**
   * Charge le lit
   *
   * @return CLit
   */
  function loadRefLit() {
    return $this->_ref_lit = $this->loadFwdRef("lit_id", true);
  }

  /**
   * Récupération des réservations présente pour des RPU en cours
   *
   * @param int $rpu_id Identifiant du RPU à exclure
   *
   * @return array
   */
  static function loadCurrentReservations($rpu_id = null) {
    if (!CAppUI::gconf("dPurgences Placement use_reservation_box")) {
      return array();
    }
    $datetime                        = CMbDT::dateTime();
    $ljoin                           = array();
    $ljoin["rpu"]                    = "rpu.rpu_id = rpu_reservation.rpu_id";
    $ljoin["sejour"]                 = "sejour.sejour_id = rpu.sejour_id";
    $where                           = array();
    $where[]                         = "sejour.entree <= '$datetime' AND sejour.sortie >= '$datetime'";
    $where["rpu_reservation.rpu_id"] = "IS NOT NULL";
    $where["sejour.type"]            = CSQLDataSource::prepareIn(CSejour::getTypesSejoursUrgence());
    $where["sejour.annule"]          = " = '0'";
    $where["sejour.group_id"]        = " = '" . CGroups::loadCurrent()->_id . "'";
    if ($rpu_id) {
      $where["rpu.rpu_id"] = " <> '$rpu_id'";
    }
    $reservation  = new self;
    $reservations = $reservation->loadList($where, null, null, "rpu_reservation.reservation_id", $ljoin);
    $lits         = array();
    foreach ($reservations as $_reservation) {
      $lits[$_reservation->lit_id] = $_reservation->lit_id;
    }

    return $lits;
  }
}