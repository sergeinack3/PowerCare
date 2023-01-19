<?php
/**
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Qualite;

use Ox\Core\CMbObject;

/**
 * Element de fiche d'évènement indésirable
 * Class CEiItem
 */
class CEiItem extends CMbObject {
  // DB Table key
  public $ei_item_id;

  // DB Fields
  public $ei_categorie_id;
  public $nom;

  // Behaviour Fileds
  public $_checked;

  // Object References
  /** @var CEiCategorie */
  public $_ref_categorie;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'ei_item';
    $spec->key   = 'ei_item_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specs                    = parent::getProps();
    $specs["ei_categorie_id"] = "ref notNull class|CEiCategorie back|items";
    $specs["nom"]             = "str notNull maxLength|50";

    return $specs;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->nom;
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    $this->_ref_categorie = new CEiCategorie;
    $this->_ref_categorie->load($this->ei_categorie_id);
  }
}
