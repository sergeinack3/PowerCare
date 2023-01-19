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
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Class CAgendaPraticien
 *
 * @package Ox\Mediboard\Cabinet
 */
class CAgendaPraticien extends CMbObject {
  // DB Table key
  public $agenda_praticien_id;

  // DB References
  public $praticien_id;
  public $lieuconsult_id;

  // DB fields
  public $active;
  public $sync;

  // Form fields
  /** @var CMediusers */
  public $_ref_praticien;

  /** @var CLieuConsult */
  public $_ref_lieu;

  /** @var CPlageconsult[] */
  public $_refs_plages_consult;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "agenda_praticien";
    $spec->key   = "agenda_praticien_id";
    $spec->uniques['practitioner'] = array('praticien_id', 'lieuconsult_id', 'active', 'sync');

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props            = parent::getProps();
    $props["praticien_id"]   = "ref notNull class|CMediusers back|agendas_praticien";
    $props["lieuconsult_id"] = "ref notNull class|CLieuConsult back|agendas_praticien";
    $props["active"]         = "bool default|1";
    $props["sync"]           = "bool notNull default|0";
    return $props;
  }

  /**
   * @param string[] $where
   * @return CStoredObject[]|null
   * @throws Exception
   */
  function loadRefsPlagesConsult($where = null) {
    return $this->_refs_plages_consult = $this->loadBackRefs("plagesconsult", null, null, null, null, null, null, $where);
  }

  /**
   * Charge le praticien associé
   *
   * @param bool $cache Utiliser le cache
   *
   * @return CMediusers
   * @throws Exception
   */
  function loadRefPraticien($cache = true) {
    /** @var CMediusers $praticien */
    $praticien            = $this->loadFwdRef("praticien_id", $cache);
    $praticien->loadRefFunction();

    return $this->_ref_praticien = $praticien;
  }

  /**
   * Charge le lieu associé
   *
   * @return CLieuConsult|CStoredObject
   * @throws Exception
   */
  function loadRefLieu() {
    return $this->_ref_lieu = $this->loadFwdRef("lieuconsult_id", true);
  }
}