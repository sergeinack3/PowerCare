<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Pmsi;

use Exception;
use Ox\Core\CMbObject;

/**
 * Description
 */
class CCIM10 extends CMbObject {
  public $id;

  public $code;
  public $type_mco;
  public $libelle_court;
  public $libelle;
  public $exist;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "codes_atih";
    $spec->key   = "id";
    $spec->dsn   = "cim10";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                  = parent::getProps();
    $props["code"]          = "str";
    $props["type_mco"]      = "enum list|0|1|2|3|4";
    $props["libelle_court"] = "str seekable";
    $props["libelle"]       = "str seekable";

    return $props;
  }

  /**
   * @inheritdoc
   *
   * @return self
   */
  function load($id = null) {
    parent::load($id);
    if ($this->_id) {
      $this->exist = true;
    }

    return $this;
  }

  /**
   * @param $code
   *
   * @return self
   * @throws Exception
   */
  static function get($code) {
    $cim = new CCIM10();
    $cim->code = $code;
    $cim->loadMatchingObject();

    if ($cim->_id) {
      $cim->exist = true;
    }

    return $cim;
  }
}
