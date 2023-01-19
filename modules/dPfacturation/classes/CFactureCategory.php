<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation;

use Ox\Core\CMbObject;
use Ox\Mediboard\Mediusers\CFunctions;

/**
 * Catégorie de facture
 */
class CFactureCategory extends CMbObject {
  // DB Table key
  public $facture_category_id;

  // DB References
  public $group_id;
  public $function_id;

  // DB Fields
  public $libelle;
  public $code;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'facture_category';
    $spec->key   = 'facture_category_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["group_id"]    = "ref notNull class|CGroups back|cat_bill_etab";
    $props["function_id"] = "ref notNull class|CFunctions back|cat_bill_fct";
    $props["libelle"]     = "str notNull autocomplete";
    $props["code"]        = "num maxLength|6";
    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->libelle;
  }

  /**
   * Liste les catégories existantes pour une fonction donnée
   *
   * @param int $function_id Fonction
   *
   * @return CFunctions[]
   */
  function getListForFunction($function_id) {
    $category = new CFactureCategory();
    $category->function_id = $function_id;
    return $categorys = $category->loadMatchingList("libelle");
  }
}
