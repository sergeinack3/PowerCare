<?php
/**
 * @package Mediboard\Core\FieldSpecs
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FieldSpecs;

use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbString;

/**
 * PHP code
 */
class CPhpSpec extends CMbFieldSpec {
  /**
   * @inheritdoc
   */
  function getSpecType() {
    return "php";
  }

  /**
   * @inheritdoc
   */
  function getDBSpec() {
    return "LONGTEXT";
  }

  /**
   * @inheritdoc
   */
  function getValue($object, $params = array()) {
    $propValue = $object->{$this->fieldName};
    $propValue = (!empty($params['export']) ? var_export($propValue, true) : $propValue);
    
    return CMbString::highlightCode("php", $propValue);
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
  function sample($object, $consistent = true){
    $object->{$this->fieldName} = '<?php
$file = fopen("welcome.txt", "r") or exit("Unable to open file!");
//Output a line of the file until the end is reached
while(!feof($file)) {
  echo fgets($file). "<br />";
}
fclose($file);
?>';
  }

  /**
   * @inheritdoc
   */
  public function getLitteralDescription(): string
  {
    return "Texte contenant du code PHP. " . parent::getLitteralDescription();
  }
}
