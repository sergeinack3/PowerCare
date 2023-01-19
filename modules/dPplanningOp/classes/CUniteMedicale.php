<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\CMbObject;

/**
 * Table type_autorisation_um pour le pmsi
 */
class CUniteMedicale extends CMbObject {
  // DB Table key
  public $racine_code;
  public $spec_char;
  public $code_concat;
  public $libelle;
  public $mode_hospitalisation;
  public $sae;

  public $_mode_hospitalisation = [];

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->dsn   = 'sae';
    $spec->table = "type_autorisation_um";
    $spec->key   = "code_concat";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                         = parent::getProps();
    $props["racine_code"]          = "num notNull maxLength|3";
    $props["spec_char"]            = "str";
    $props["libelle"]              = "text notNull";
    $props["mode_hospitalisation"] = "str";
    $props["sae"]                  = "str";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view      = $this->code_concat . " - " . $this->libelle;
    $this->_shortview = $this->code_concat;
    if ($this->mode_hospitalisation) {
      $this->_mode_hospitalisation = explode("|", $this->mode_hospitalisation);
    }
  }

  /**
   * @return array
   */
  function loadListUm() {
    return $this->loadList();
  }
}