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
 * Catégories de document qualité
 * Class CCategorieDoc
 */
class CCategorieDoc extends CMbObject {
  // DB Table key
  public $doc_categorie_id;

  // DB Fields
  public $nom;
  public $code;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'doc_categories';
    $spec->key   = 'doc_categorie_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specs         = parent::getProps();
    $specs["nom"]  = "str notNull maxLength|50";
    $specs["code"] = "str notNull maxLength|1";

    return $specs;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view      = "$this->code - $this->nom";
    $this->_shortview = $this->code;
  }
}
