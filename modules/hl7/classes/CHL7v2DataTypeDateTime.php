<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;
use Ox\Core\CValue;

/**
 * HL7 datatime data type
 */
class CHL7v2DataTypeDateTime extends CHL7v2DataType {
  /**
   * @inheritdoc
   */
  function toMB($value, CHL7v2Field $field){
    $parsed = $this->parseHL7($value, $field);
    
    // empty value
    if ($parsed === "") {
      return "";
    }
    
    // invalid value
    if ($parsed === false) {
      return null;
    }

    $datetime = $parsed["year"].
                "-".CValue::read($parsed, "month",  "00").
                "-".CValue::read($parsed, "day",    "00");

    if (isset($parsed["hour"])) {
      $datetime .= " ".CValue::read($parsed, "hour",   "00").
                   ":".CValue::read($parsed, "minute", "00").
                   ":".CValue::read($parsed, "second", "00");
    }

    return $datetime;
  }

  /**
   * @inheritdoc
   */
  function toHL7($value, CHL7v2Field $field) {
    $parsed = $this->parseMB($value, $field);
    
    // empty value
    if ($parsed === "") {
      return "";
    }
    
    // invalid value
    if ($parsed === false) {
      return null;
    }
    
    return  CValue::read($parsed, "year").
           (CValue::read($parsed, "month") === "00" ? "" : CValue::read($parsed, "month")).
           (CValue::read($parsed, "day")   === "00" ? "" : CValue::read($parsed, "day")).
            CValue::read($parsed, "hour").
            CValue::read($parsed, "minute").
            CValue::read($parsed, "second");
  }
}