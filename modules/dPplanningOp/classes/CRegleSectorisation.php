<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

class CRegleSectorisation extends CMbObject {
  public $regle_id;

  public $priority;
  public $service_id;
  public $function_id;
  public $praticien_id;
  public $duree_min;
  public $duree_max;
  public $date_min;
  public $date_max;
  public $type_admission;
  public $type_pec;
  public $group_id;

  /** @var integer Minimal patient age */
  public $age_min;

  /** @var integer Maximal patient age */
  public $age_max;

  /** @var boolean Is the patient invalid? */
  public $handicap;

  //form field
  public $_ref_service;
  public $_ref_function;
  public $_ref_praticien;
  public $_ref_group;
  public $_inactive;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = "regle_sectorisation";
    $spec->key    = "regle_id";
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $sejour = new CSejour();
    $types_admission  = $sejour->_specs["type"]->_list;
    $types_pec        = $sejour->_specs["type_pec"]->_list;

    $props = parent::getProps();
    $props["priority"]          = "num default|0 notNull";
    $props["service_id"]        = "ref class|CService seekable notNull back|regle_sectorisation_service";
    $props["function_id"]       = "ref class|CFunctions back|regle_sectorisation_function";
    $props["praticien_id"]      = "ref class|CMediusers back|regles_sectorisation_mediuser";
    $props["duree_min"]         = "num";
    $props["duree_max"]         = "num moreEquals|duree_min";
    $props["date_min"]          = "dateTime";
    $props["date_max"]          = "dateTime moreEquals|date_min";
    $props["age_min"]           = "num";
    $props["age_max"]           = "num moreEquals|age_min";
    $props["handicap"]          = "bool";
    $props["type_admission"]    = "enum list|".implode("|", $types_admission);
    $props["type_pec"]          = "enum list|".implode("|", $types_pec);
    $props["group_id"]          = "ref class|CGroups notNull back|regle_sectorisation_group";
    return $props;
  }

  /**
   * check if $this is an older rule
   *
   * @return bool
   */
  function checkOlder() {
    $now = CMbDT::dateTime();

    if ($this->date_min && $now < $this->date_min) {
      return $this->_inactive = true;
    }

    if ($this->date_max && $now > $this->date_max) {
      return $this->_inactive = true;
    }

    return $this->_inactive = false;

  }

  /**
   * Load the praticien by his _id
   *
   * @return CMediusers
   */
  function loadRefPraticien() {
    return $this->_ref_praticien = $this->loadFwdRef("praticien_id", true);
  }

  /**
   * load service by id
   *
   * @return CService
   */
  function loadRefService() {
    return $this->_ref_service = $this->loadFwdRef("service_id", true);
  }

  /**
   * load function by id
   *
   * @return CFunctions
   */
  function loadRefFunction() {
    return $this->_ref_function = $this->loadFwdRef("function_id", true);
  }

  /**
   * load group
   *
   * @return CGroups
   */
  function loadRefGroup() {
    return $this->_ref_group = $this->loadFwdRef("group_id", true);
  }

  /**
   * @see parent::store()
   */
  function store() {
    if (!$this->_id) {
      $this->group_id = CGroups::loadCurrent()->_id;
    }

    return parent::store();
  }
}