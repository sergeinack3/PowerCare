<?php
/**
 * @package Mediboard\Core\FieldSpecs
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FieldSpecs;

use Ox\Core\CMbFieldSpec;
use Ox\Core\CStoredObject;
use Ox\Mediboard\System\Forms\CExObject;


/**
 * GUID reference to a CStoredObject
 */
class CGuidSpec extends CMbFieldSpec {
  public $class;

  /**
   * @inheritdoc
   */
  function getSpecType() {
    return "guid";
  }

  /**
   * @inheritdoc
   */
  function getDBSpec(){
    return "VARCHAR(255)";
  }

  /**
   * @inheritdoc
   */
  function getOptions(){
    return array(
      'class' => 'class',
    ) + parent::getOptions();
  }

  /**
   * @inheritdoc
   */
  function checkProperty($object){
    if ($this->notNull) {
      return "Sp�cifications de propri�t� incoh�rentes 'notNull'";
    }

    $propValue = $object->{$this->fieldName};
    list($class, $id) = explode('-', $propValue);

    if ($this->class) {
      if (!is_subclass_of($this->class, CStoredObject::class)) {
        return "La classe '$this->class' n'est pas une classe d'objet enregistr�e";
      }
      if ($class != $this->class && !is_subclass_of($class, $this->class)) {
        return "Objet r�f�renc� '$class' n'est pas du type '$this->class'";
      }
    }

    $must_load = $propValue != 0 || ($propValue == 0 && empty($this->options["allow_zero"]));

    // Gestion des objets �tendus ayant une pseudo-classe
    $ex_object = CExObject::getValidObject($class);
    if ($ex_object) {
      if ($must_load && !$ex_object->load($id)) {
        return "Objet r�f�renc� de type '$class' introuvable";
      }
    }
    else {
      if (!is_subclass_of($class, CStoredObject::class)) {
        return "La classe '$class' n'est pas une classe d'objet enregistr�e";
      }

      /** @var CStoredObject $ref */
      $ref = new $class;
      if ($must_load && !$ref->idExists($id)) {
        return "Objet r�f�renc� de type '$class' introuvable";
      }
    }

    return null;
  }

}
