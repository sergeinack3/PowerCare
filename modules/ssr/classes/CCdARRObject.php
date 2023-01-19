<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\CStoredObject;

/**
 * Activité CdARR
 */
class CCdARRObject extends CStoredObject {

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec              = parent::getSpec();
    $spec->dsn         = 'cdarr';
    $spec->incremented = false;

    return $spec;
  }
}
