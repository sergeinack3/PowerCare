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
 * Catégories des fiches d'évènements indésirables
 * Class CEiCategorie
 */
class CEiCategorie extends CMbObject {
  // DB Table key
  public $ei_categorie_id;

  // DB Fields
  public $nom;

  // Behaviour Fileds
  public $_checked;

  // Object References
  /** @var  CEiItem[] */
  public $_ref_items;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'ei_categories';
    $spec->key   = 'ei_categorie_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specs        = parent::getProps();
    $specs["nom"] = "str notNull maxLength|50";

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
   * @see parent::loadRefsBack()
   */
  function loadRefsBack() {
    $this->_ref_items = $this->loadBackRefs("items", "nom");
  }
}
