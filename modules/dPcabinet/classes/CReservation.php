<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Exception;
use Ox\Core\CMbObject;
use Ox\Mediboard\Patients\CPatient;

/**
 * Description
 */
class CReservation extends CMbObject {
  /** @var integer Primary key */
  public $reservation_id;

  // DB fields
  public $plage_ressource_cab_id;
  public $patient_id;
  public $date;
  public $heure;
  public $duree;
  public $motif;

  // References
  /** @var CPatient */
  public $_ref_patient;
  /** @var CPlageRessourceCab */
  public $_ref_plage_ressource;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "reservation";
    $spec->key   = "reservation_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                           = parent::getProps();
    $props["plage_ressource_cab_id"] = "ref class|CPlageRessourceCab notNull back|reservations";
    $props["patient_id"]             = "ref class|CPatient back|reservations";
    $props["date"]                   = "date notNull";
    $props["heure"]                  = "time notNull";
    $props["duree"]                  = "num min|1 notNull";
    $props["motif"]                  = "text";

    return $props;
  }

  /**
   * Chargement de la consultation
   *
   * @return CConsultation
   */
  function loadRefPatient() {
    return $this->_ref_patient = $this->loadFwdRef("patient_id", true);
  }

  /**
   * @return CPlageRessourceCab|null
   * @throws Exception
   */
  function loadRefPlageRessource() {
    return $this->_ref_plage_ressource = $this->loadFwdRef("plage_ressource_cab_id");
  }
}
