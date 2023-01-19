<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\CAppUI;

/**
 * Extension documentaire concernant une activite CsARR
 */
class CExtensionDocumentaireCsARR extends CCsARRObject {
  public $code;
  public $libelle;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'code_extension';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props            = parent::getProps();
    $props["code"]    = "str notNull length|2 seekable";
    $props["libelle"] = "text notNull";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view      = "$this->code - $this->libelle";
    $this->_shortview = $this->code;
  }

  /**
   * Charge la liste des extensions documentaires
   *
   * @return array
   */
  static function getList() {
    $extensions_doc = array();
    if (CAppUI::gconf("ssr general use_acte_presta") == 'csarr') {
      $extension_doc  = new self;
      $extensions_doc = $extension_doc->loadList();
    }

    return $extensions_doc;
  }
}
