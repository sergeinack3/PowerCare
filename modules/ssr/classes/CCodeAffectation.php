<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\CMbObject;

/**
 * CCodeAffectation class
 */
class CCodeAffectation extends CMbObject {
  /** @var integer Primary key */
  public $code_affectation_id;

  /** @var string */
  public $code_type;
  /** @var int */
  public $code;
  /** @var int */
  public $function_id;

  /** @var CPrestaSSR */
  public $_ref_code;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec                  = parent::getSpec();
    $spec->table           = "code_affectation";
    $spec->key             = "code_affectation_id";
    $spec->uniques["code"] = ["function_id", "code"];

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props["code_type"]   = "enum list|H+ default|H+";
    $props["code"]        = "str notNull";
    $props["function_id"] = "ref class|CFunctions notNull back|codes_affectations";

    return $props;
  }

  /**
   * Loads the code using the type
   *
   * @return CPrestaSSR|null
   */
  public function loadRefCode() {
    if ($this->code_type === "H+") {
      return $this->_ref_code = CPrestaSSR::get($this->code);
    }

    return null;
  }

}