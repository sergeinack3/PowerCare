<?php
/**
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Etablissement;
use Ox\Core\CMbObjectConfig;

/**
 * Group level configuration
 *
 * @deprecated It should migrate to CConfiguration
 */
class CGroupsConfig extends CMbObjectConfig {
  public $groups_config_id;
  
  public $object_id; // CGroups
  
  // Object configs
  public $max_comp;
  public $max_ambu;
  public $codage_prat;

  public $dPplanningOp_COperation_DHE_mode_simple;
  
  public $ecap_CRPU_notes_creation;
  
  // SIP
  public $sip_notify_all_actors;
  public $sip_idex_generator;
  
  // SMP
  public $smp_notify_all_actors;
  public $smp_idex_generator;
  
  public $dPprescription_CPrescription_show_trash_24h;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = "groups_config";
    $spec->key   = "groups_config_id";
    $spec->uniques["uniques"] = array("object_id");
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["object_id"]          = "ref class|CGroups back|object_configs";
    
    $props["max_comp"]    = "num min|0";
    $props["max_ambu"]    = "num min|0";
    $props["codage_prat"] = "bool default|0";

    $props["dPplanningOp_COperation_DHE_mode_simple"]       = "bool default|0";
    
    $props["ecap_CRPU_notes_creation"] = "bool default|0";
    
    // SIP
    $props["sip_notify_all_actors"] = "bool default|0";
    $props["sip_idex_generator"]    = "bool default|0";
    
    // SMP
    $props["smp_notify_all_actors"] = "bool default|0";
    $props["smp_idex_generator"]    = "bool default|0";
    
    $props["dPprescription_CPrescription_show_trash_24h"] = "bool default|0";
    
    return $props;
  }
}
