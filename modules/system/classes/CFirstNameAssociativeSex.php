<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;
use Ox\Core\CStoredObject;

class CFirstNameAssociativeSex extends CStoredObject {
  public $first_name_id;
  public $firstname;
  public $sex;
  public $language;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table       = "firstname_to_gender";
    $spec->key         = "first_name_id";
    $spec->loggable = false;
    return $spec;
  }

  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->firstname;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["firstname"] = "str notNull";
    $props["sex"]       = "enum list|f|m|u notNull default|u";
    $props["language"]  = "str";
    return $props;
  }

  /**
   * return the sex if found for firstname, else return null
   *
   * @param string $firstname the firstname to have
   *
   * @return string|null sex (u = undefined, f = female, m = male, null = not in base)
   */
  static function getSexFor($firstname) {
    $prenom_exploded = preg_split('/[-_ ]+/', $firstname);   // get the first firstname of composed one
    $first_first_name = addslashes(trim(reset($prenom_exploded)));

    $object = new self();
    $object->firstname = $first_first_name;
    $nb_objects = $object->countMatchingList();
    if ($nb_objects > 1) {
      $object->language = "french";
      $object->loadMatchingObject();
    }
    if (!$object->_id || $object->sex == "u") {
      $object = new self();
      $object->firstname = $first_first_name;
      $object->loadMatchingObject();
    }

    return $object->sex ? $object->sex : "u";
  }

  static function countData() {
    $fs = new self();
    return $fs->countList();
  }
}