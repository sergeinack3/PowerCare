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
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

/**
 * Class CLieuConsult
 *
 * @package Ox\Mediboard\Cabinet
 */
class CLieuConsult extends CMbObject {
  // DB Table key
  public $lieuconsult_id;

  // DB fields
  public $group_id;
  public $label;
  public $adresse;
  public $cp;
  public $ville;
  public $active;

  // Form fields
  public $_prat_id;

  /** @var CAgendaPraticien[] */
  public $_ref_lieux_consult_prat = [];

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "lieuconsult";
    $spec->key   = "lieuconsult_id";

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    list($min_cp, $max_cp) = CPatient::getLimitCharCP();

    $props             = parent::getProps();
    $props["group_id"] = "ref class|CGroups back|lieux_consult";
    $props["label"]    = "str notNull seekable";
    $props["adresse"]  = "text notNull seekable";
    $props["ville"]    = "str notNull seekable";
    $props["cp"]       = "str notNull minLength|$min_cp maxLength|$max_cp seekable";
    $props["active"]   = "bool default|1";
    $props["_prat_id"] = "ref class|CMediusers";

    return $props;
  }

  /**
   * @inheritDoc
   */
  public function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->label;
  }

  /**
   * @inheritDoc
   */
  public function store() {
    $creation = is_null($this->_id) || $this->_id === '';

    if ($creation) {
      $this->group_id = CGroups::loadCurrent()->_id;
    }

    if ($msg = parent::store()) {
      return $msg;
    }

    $mediuser = CMediusers::get();

    if ($creation && ($mediuser->isProfessionnelDeSante() || $this->_prat_id)) {
      $assoc                 = new CAgendaPraticien();
      $assoc->praticien_id   = $this->_prat_id ?: $mediuser->_id;
      $assoc->lieuconsult_id = $this->_id;

      if ($msg = $assoc->store()) {
        return $msg;
      }
    }

    return null;
  }

  /**
   * @inheritDoc
   */
  public function delete() {
    $assocs = array_values($this->loadRefsAgendasPrat());

    if (count($assocs) === 1) {
      if ($msg = $assocs[0]->delete()) {
        return $msg;
      }
    }

    if ($msg = parent::delete()) {
      return $msg;
    }

    return null;
  }

  /**
   * Charge les agendas des praticiens
   *
   * @return CAgendaPraticien[]|CStoredObject[]
   * @throws Exception
   */
  function loadRefsAgendasPrat() {
    return $this->_ref_lieux_consult_prat = $this->loadBackRefs("agendas_praticien");
  }
}
