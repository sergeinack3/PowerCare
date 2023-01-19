<?php
/**
 * @package Mediboard\\core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FieldSpecs;

use Ox\Core\CClassMap;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;

class CRefChecker {

  /** @var CRefSpec $ref_spec */
  private $ref_spec;

  /** @var CRefSpec $ref_spec */
  private $meta_spec;

  /**
   * CRefChecker constructor.
   *
   * @param CRefSpec          $ref_spec
   * @param CMbFieldSpec|null $meta_spec
   */
  public function __construct(CRefSpec $ref_spec, CMbFieldSpec $meta_spec = null) {
    $this->ref_spec  = $ref_spec;
    $this->meta_spec = $meta_spec;
  }

  /**
   * @return bool|void
   * @throws CRefCheckerException
   */
  public function check() {
    $this->checkCommon();

    if ($this->ref_spec->meta) {
      return $this->checkMeta();
    }

    return $this->checkClassique();
  }

  /**
   * @return bool
   * @throws CRefCheckerException
   */
  private function checkCommon() {
    if (is_null($this->ref_spec->class)) {
      return true;
    }

    if (!class_exists($this->ref_spec->class)) {
      // todo check only on mono-repo
      // throw new CRefCheckerException('Invalid reference on none exists class', 0, $this->ref_spec);
    }

    if ($this->ref_spec->class !== 'CStoredObject' && !is_subclass_of($this->ref_spec->class, CStoredObject::class)) {
      throw new CRefCheckerException('Invalid reference on none storable class', 1, $this->ref_spec);
    }

    return true;
  }

  /**
   * @return bool
   * @throws CRefCheckerException
   */
  private function checkClassique() {
    if ($this->ref_spec->class === CClassMap::getSN(CMbObject::class)) {
      // todo ref when can check if owner is abstract class
      //throw new CRefCheckerException('Invalid direct reference on CMbObject', 10, $this->ref_spec);
    }

    if ($this->ref_spec->class === CClassMap::getSN(CStoredObject::class)) {
      throw new CRefCheckerException('Invalid direct reference on CStoredObject', 11, $this->ref_spec);
    }

    if (!class_exists($this->ref_spec->class)) {
      // todo check only on mono-repo
      //throw new CRefCheckerException('Invalid reference on undefined class', 12, $this->ref_spec);
    }

    /** @var CStoredObject $instance_ref_class */
    $instance_ref_class = new $this->ref_spec->class;
    if (!$instance_ref_class instanceof CStoredObject) {
      throw new CRefCheckerException('Invalid reference on none storable object', 13, $this->ref_spec);
    }


    if ($instance_ref_class->isModelObjectAbstract()) {
      // todo ref when can check if owner is abstract class
      //throw new CRefCheckerException('Invalid reference on model object abstract class', 14, $this->ref_spec);
    }

    return true;
  }

  /**
   * @return bool
   * @throws CRefCheckerException
   */
  private function checkMeta() {
    if ($this->ref_spec->class) {
      // parent class defined on ref
      /** @var CStoredObject $instance_ref_class */
      $instance_ref_class = new $this->ref_spec->class;

      if (!$instance_ref_class instanceof CStoredObject) {
        throw new CRefCheckerException('Invalid reference on none storable object', 20, $this->ref_spec);
      }

      if ($instance_ref_class->isModelObjectAbstract() && !in_array($this->ref_spec->class, ['CMbObject', 'CStoredObject'], true)) {
        if (!$this->meta_spec instanceof CEnumSpec) {
          // todo ref when can check if owner is abstract class
          //throw new CRefCheckerException('Invalid meta reference on abstract object', 21, $this->ref_spec);
        }
      }

      if ($this->meta_spec instanceof CEnumSpec) {
        foreach ($this->meta_spec->_list as $enum_class) {
          // TODO Voir pour gérer le cas des modules non installés
          if ($enum_class !== $this->ref_spec->class && !is_subclass_of($enum_class, $this->ref_spec->class)) {
            throw new CRefCheckerException('Invalid meta reference not a subclass of abstract object', 22, $this->ref_spec);
          }
        }
      }
    }
    else {
      // no parent class defined on ref
      if (!$this->meta_spec instanceof CEnumSpec) {
        throw new CRefCheckerException('Invalid meta reference need enum class', 23, $this->ref_spec);
      }

      foreach ($this->meta_spec->_list as $enum_class) {
        if (!is_subclass_of($enum_class, CStoredObject::class)) {
          throw new CRefCheckerException('Invalid meta reference not a subclass of CStoredObject', 24, $this->ref_spec);
        }
      }
    }

    return true;
  }
}