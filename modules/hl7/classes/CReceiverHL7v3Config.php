<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObjectConfig;

/**
 * HL7v3 Config
 */
class CReceiverHL7v3Config extends CMbObjectConfig {
  /** @var integer Primary key */
  public $receiver_hl7v3_config_id;

  public $object_id;

  public $use_receiver_oid;

  public $_categories = array(
    "build" => array(
      "use_receiver_oid"
    ),
  );

  /**
   * Initialize the class specifications
   *
   * @return CMbFieldSpec
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table              = "receiver_hl7v3_config";
    $spec->key                = "receiver_hl7v3_config_id";
    $spec->uniques["uniques"] = array("object_id");

    return $spec;  
  }
  
  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();

    $props["object_id"]        = "ref class|CReceiverHL7v3 back|object_configs";

    //build
    $props["use_receiver_oid"] = "bool default|0";

    return $props;
  }
}
