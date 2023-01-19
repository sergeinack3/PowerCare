<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Ox\Mediboard\Etablissement\CGroups;

/**
 * Prestation ponctuelle
 */
class CPrestationPonctuelle extends CPrestationExpert {
  public const RESOURCE_TYPE = 'prestation_ponctuelle';

  // DB Table key
  public $prestation_ponctuelle_id;

  // DB Fields
  public $show_admission;
  public $forfait;

  // Form fields
  public $_count_items = 0;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "prestation_ponctuelle";
    $spec->key   = "prestation_ponctuelle_id";

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                   = parent::getProps();
    $props["group_id"]      .= " back|prestations_ponctuelles";
    $props["show_admission"] = "bool default|0";
    $props["forfait"]        = "bool default|0";

    return $props;
  }

  static function loadCurrentList() {
    $prestation           = new self();
    $prestation->group_id = CGroups::loadCurrent()->_id;
    $prestation->forfait  = 0;

    return $prestation->loadMatchingList("nom");
  }

  static function countCurrentList() {
    $prestation           = new self();
    $prestation->group_id = CGroups::loadCurrent()->_id;
    $prestation->forfait  = 0;

    return $prestation->countMatchingList();
  }

  static function loadCurrentListForfait($type_hospi = null, $type_pec = null) {
    $prestation = new self();
    $where      = array(
      "group_id" => "= '" . CGroups::loadCurrent()->_id . "'",
      "forfait"  => "= '1'"
    );
    if ($type_hospi) {
      $where[] = "type_hospi IS NULL OR type_hospi = '$type_hospi'";
    }
    if ($type_pec) {
      $where[] = "(prestation_ponctuelle.M = '0' AND prestation_ponctuelle.C = '0' AND prestation_ponctuelle.O = '0' AND prestation_ponctuelle.SSR = '0') OR prestation_ponctuelle.$type_pec = '1'";
    }

    return $prestation->loadList($where, "nom");
  }
}
