<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante;

use Ox\Core\CMbObjectConfig;
use Ox\Core\CMbObjectSpec;

/**
 * Description
 */
class CReceiverHPrimSanteConfig extends CMbObjectConfig {
  public $receiver_hprimsante_config_id;

  public $object_id;

  //version
  public $ADM_version;
  public $ADM_sous_type;

  /**
   * Initialize object specification
   *
   * @return CMbObjectSpec the spec
   */
  function getSpec() {
    $spec = parent::getSpec();

    $spec->table = "receiver_hprimsante_config";
    $spec->key   = "receiver_hprimsante_config_id";
    $spec->uniques["uniques"] = array("object_id");

    return $spec;
  }

  /**
   * Get properties specifications as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();

    $props["object_id"]     = "ref class|CReceiverHprimSante back|object_configs";

    // Version
    $props["ADM_version"]   = "enum list|2.1|2.2|2.3|2.4|2.5 default|2.1";

    $props["ADM_sous_type"] = "enum list|C|L|R default|C";

    return $props;
  }
}
