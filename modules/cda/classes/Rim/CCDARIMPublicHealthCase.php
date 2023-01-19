<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Rim;

use Ox\Interop\Cda\Datatypes\Base\CCDACE;

/**
 * CCDARIMPublicHealthCase Class
 */
class CCDARIMPublicHealthCase extends CCDARIMObservation {

  /**
   * @var CCDACE
   */
  public $detectionMethodCode;

  /**
   * @var CCDACE
   */
  public $transmissionModeCode;

  /**
   * @var CCDACE
   */
  public $diseaseImportedCode;

}
