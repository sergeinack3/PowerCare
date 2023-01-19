<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Exception;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Description
 */
class CCible extends CMbObject {
  /**
   * @var integer Primary key
   */
  public $cible_id;

  // DB Fields
  public $libelle_ATC;
  public $sejour_id;
  public $datetime;
  public $report;

  public $object_class;
  public $object_id;
  public $_ref_object;

  // Distant Fields
  /** @var CTransmissionMedicale[] */
  public $_ref_transmissions;
  /** @var CTransmissionMedicale */
  public $_ref_first_transmission;

  // Form Fields
  public $_count_transmissions;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "cible";
    $spec->key   = "cible_id";

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                 = parent::getProps();
    $props["object_id"]    = "ref class|CMbObject meta|object_class nullify back|cibles";
    $props["object_class"] = "enum list|CPrescriptionLineElement|CPrescriptionLineMedicament|CPrescriptionLineComment|CCategoryPrescription|CAdministration|CPrescriptionLineMix show|0";
    $props["libelle_ATC"]  = "text";
    $props["sejour_id"]    = "ref notNull class|CSejour back|cibles_sejour";
    $props["datetime"]     = "dateTime notNull";
    $props["report"]       = "bool default|1";

    return $props;
  }

  /**
   * Charge les transmissions médicales associées à cette cible
   *
   * @return CTransmissionMedicale[]
   */
  function loadRefsTransmissions() {
    return $this->_ref_transmissions = $this->loadBackRefs("transmissions", "date ASC, transmission_medicale_id ASC");
  }

  /**
   * Compte les transmissions médicales associées à cette cible
   *
   * @return int
   */
  function countTransmissions() {
    return $this->_count_transmissions = $this->countBackRefs("transmissions");
  }

  /**
   * @see parent::loadView()
   */
  function loadView() {
    $first_trans                   = $this->loadFirstBackRef("transmissions", "date ASC, transmission_medicale_id ASC");
    $this->_ref_first_transmission = $first_trans;

    if ($this->libelle_ATC) {
      $this->_view = $this->libelle_ATC;
    }
    else {
      $this->_view = $this->loadTargetObject()->_view;
    }

    $this->_view .= " &ndash; $first_trans->text";
  }

  /**
   * @param CStoredObject $object
   * @deprecated
   * @todo redefine meta raf
   * @return void
   */
  public function setObject(CStoredObject $object) {
    CMbMetaObjectPolyfill::setObject($this, $object);
  }

  /**
   * @param bool $cache
   * @deprecated
   * @todo redefine meta raf
   * @return bool|CStoredObject|CExObject|null
   * @throws Exception
   */
  public function loadTargetObject($cache = true) {
    return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
  }

  /**
   * @inheritDoc
   * @todo remove
   */
  function loadRefsFwd() {
    parent::loadRefsFwd();
    $this->loadTargetObject();
  }
}
