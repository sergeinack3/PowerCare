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
 * Prestation journalière
 */
class CPrestationJournaliere extends CPrestationExpert {
  public const RESOURCE_TYPE = 'prestation_journaliere';

  // DB Table key
  public $prestation_journaliere_id;

  // DB Fields
  public $desire;

  // Form fields
  public $_ref_items = 0;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "prestation_journaliere";
    $spec->key   = "prestation_journaliere_id";

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props           = parent::getProps();
    $props["group_id"] .= " back|prestations_journalieres fieldset|default";
    $props["desire"] = "bool default|0 fieldset|default";

    return $props;
  }

  /**
   * Charge les prestations journalières de l'établissement
   * pour un éventuel type d'hospitalisation donné
   *
   * @param string $type_hospi Type d'hospitalisation
   * @param string $type_pec   Type de prise en charge
   *
   * @return self[]
   */
  static function loadCurrentList($type_hospi = null, $type_pec = null) {
    $prestation = new self();

    $where = array();
    if ($type_hospi) {
      $where[] = "type_hospi IS NULL OR type_hospi = '$type_hospi'";
    }
    if ($type_pec) {
      $where[] = "(prestation_journaliere.M = '0' AND prestation_journaliere.C = '0' AND prestation_journaliere.O = '0' AND prestation_journaliere.SSR = '0') OR prestation_journaliere.$type_pec = '1'";
    }

    return $prestation->loadGroupList($where, "nom");
  }

  /**
   * Compte les prestations journalières de l'établissement
   * pour un éventuel type d'hospitalisation donné
   *
   * @param string $type_hospi Type d'hospitalisation
   * @param string $type_pec   Type de prise en charge
   *
   * @return int
   */
  static function countCurrentList($type_hospi = null, $type_pec = null) {
    $prestation = new self();
    $where      = array(
      "group_id" => "= '" . CGroups::loadCurrent()->_id . "'",
    );
    if ($type_hospi) {
      $where[] = "type_hospi IS NULL OR type_hospi = '$type_hospi'";
    }
    if ($type_pec) {
      $where[] = "(prestation_journaliere.M = '0' AND prestation_journaliere.C = '0' AND prestation_journaliere.O = '0') OR prestation_journaliere.$type_pec = '1'";
    }

    return $prestation->countList($where, "nom");
  }

  /**
   * Charge les items de la prestation
   *
   * @param array $where Clauses additionnelles
   *
   * @return CItemPrestation[]
   */
  function loadRefsItems($where = array()) {
    return $this->_ref_items = $this->loadBackRefs("items", "rank", null, null, null, null, null, $where);
  }
}
