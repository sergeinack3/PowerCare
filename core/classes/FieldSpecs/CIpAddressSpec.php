<?php
/**
 * @package Mediboard\Core\FieldSpecs
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FieldSpecs;

use Ox\Core\CMbFieldSpec;

/**
 * IP address
 */
class CIpAddressSpec extends CMbFieldSpec {
  /**
   * @inheritdoc
   */
  function getSpecType() {
    return "ipAddress";
  }

  /**
   * @inheritdoc
   */
  function getDBSpec(){
    return "VARBINARY(16)";
  }

  /**
   * @inheritdoc
   */
  function getValue($object, $params = array()) {
    $propValue = $object->{$this->fieldName};
    return $propValue ? inet_ntop($propValue) : "";
  }

  /**
   * @inheritdoc
   */
  function checkProperty($object){
    return null;
  }

  /**
   * @inheritdoc
   */
  function filter($value){
    return $value;
  }

  /**
   * @inheritdoc
   */
  function sample($object, $consistent = true){
    parent::sample($object, $consistent);
    $object->{$this->fieldName} = inet_pton("127.0.0.1");
  }

  /**
   * @inheritdoc
   */
  public function getLitteralDescription(): string {
    return "Adresse IP au format binaire. " . parent::getLitteralDescription();
  }
}
