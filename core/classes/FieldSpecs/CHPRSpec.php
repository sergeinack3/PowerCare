<?php
/**
 * @package Mediboard\Core\FieldSpecs
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FieldSpecs;

use Ox\Core\CMbString;
use Ox\Interop\Hprim21\CHPrim21Message;

/**
 * HPrim string
 */
class CHPRSpec extends CTextSpec {
  /**
   * @inheritdoc
   */
  function getSpecType() {
    return "hpr";
  }

  /**
   * @inheritdoc
   */
  function getDBSpec() {
    return "MEDIUMTEXT";
  }

  /**
   * @inheritdoc
   */
  function getFormHtmlElement($object, $params, $value, $className){
    return $this->getFormElementTextarea($object, $params, $value, $className);
  }

  /**
   * @inheritdoc
   */
  function getHtmlValue($object, $params = array()) {
    return $this->getValue($object, $params);
  }

  /**
   * @inheritdoc
   */
  function getValue($object, $params = array()) {
    $value = $object->{$this->fieldName};

    if (isset($params["advanced"]) && $params["advanced"]) {
      $message = new CHPrim21Message();
      $message->parse($value);
      return $message->flatten(true);
    }

    return CMbString::highlightCode("er7", $value);
  }

  /**
   * @inheritdoc
   */
  function sample($object, $consistent = true){
    $object->{$this->fieldName} = 
      "H|^~&\|C152203.HPR||111111^MEDIBOARD ATL||ADM|||MDB^MEDIBOARD|LS1||H2.1^C|201210251522\n".
      "P|1|00209272||12411338|NOM^PRENOM^^^M^||19810508|M|||||||||||||||||\n".
      "A|||||||||\n".
      "L|1|||";
  }
}
