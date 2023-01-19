<?php
/**
 * @package Mediboard\Core\FieldSpecs
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FieldSpecs;

use Ox\Core\CMbString;

/**
 * Integer value (zerofilled)
 */
class CNumcharSpec extends CNumSpec {
  public $control;
  public $protected;

  /**
   * @inheritdoc
   */
  function getSpecType() {
    return "numchar";
  }

  /**
   * @inheritdoc
   */
  function getDBSpec(){
    $type_sql = "BIGINT ZEROFILL";
    
    if ($this->maxLength || $this->length) {
      $length = $this->maxLength ? $this->maxLength : $this->length;
      $valeur_max = pow(10, $length);
      
      $type_sql = "TINYINT";
      
      if ($valeur_max > pow(2, 8)) {
        $type_sql = "MEDIUMINT";
      }
      if ($valeur_max > pow(2, 16)) {
        $type_sql = "INT";
      }
      if ($valeur_max > pow(2, 32)) {
        $type_sql = "BIGINT";
      }
      
      $type_sql .= "($length) UNSIGNED ZEROFILL";
    }
    
    return $type_sql;
  }

  /**
   * @inheritdoc
   */
  public function getPHPSpec():string {
    return parent::PHP_TYPE_INT;
  }

  /**
   * @inheritdoc
   */
  function getOptions(){
    return array(
      'control'   => 'str',
      'protected' => 'bool',
    ) + parent::getOptions();
  }

  /**
   * @inheritdoc
   */
  function checkProperty($object){
    $propValue = $object->{$this->fieldName};
        
    // control
    if ($this->control) {
      // Luhn control
      if ($this->control == "luhn" && !CMbString::luhn($propValue)) {
        return "La clé est incorrecte";
      }
    }

    return null;
  }
}
