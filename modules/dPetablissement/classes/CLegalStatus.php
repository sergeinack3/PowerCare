<?php
/**
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Etablissement;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObject;

/**
 * Description
 */
class CLegalStatus extends CMbObject {
  /**
   * @var integer Primary key
   */
  public $status_code;
  public $legal_status_niv_3;
  public $name;
  public $short_name;


  /**
   * Initialize the class specifications
   *
   * @return CMbFieldSpec
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->dsn   = 'sae';
    $spec->table = "legal_status";
    $spec->key   = "status_code";

    return $spec;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();

    $props["legal_status_niv_3"]  = "num notNull maxLength|5";
    $props["name"]                = "str notNull";
    $props["short_name"]          = "str notNull";

    return $props;
  }

}
