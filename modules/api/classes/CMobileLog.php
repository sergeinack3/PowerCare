<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 */

namespace Ox\Api;

use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;

/**
 * Description
 */
class CMobileLog extends CMbObject {
  /**
   * @var integer Primary key
   */
  public $mobile_log_id;

  // DB fields
  public $url;
  public $input;
  public $output;
  public $device_uuid;
  public $device_platform;
  public $device_platform_version;
  public $device_model;
  public $level;
  public $description;
  public $log_datetime;
  public $origin;
  public $object;
  public $code;
  public $internet_connection_type;
  public $execution_time;
  public $application_name;

  public $_date_min;
  public $_date_max;

  /**
   * Initialize the class specifications
   *
   * @return CMbObjectSpec
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "mobile_log";
    $spec->key   = "mobile_log_id";

    return $spec;
  }


  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();

    $props["url"]                      = "str";
    $props["input"]                    = "php";
    $props["output"]                   = "php";
    $props["device_uuid"]              = "str";
    $props["device_platform"]          = "str";
    $props["device_platform_version"]  = "str";
    $props["device_model"]             = "str";
    $props["level"]                    = "str";
    $props["description"]              = "str notNull";
    $props["log_datetime"]             = "dateTime notNull";
    $props["origin"]                   = "str";
    $props["object"]                   = "php";
    $props["code"]                     = "str";
    $props["internet_connection_type"] = "str";
    $props["execution_time"]           = "num";
    $props["application_name"]         = "str notNull";

    $props["_date_min"] = "dateTime";
    $props["_date_max"] = "dateTime";

    return $props;
  }
}
