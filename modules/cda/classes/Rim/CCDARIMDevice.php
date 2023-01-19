<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Rim;

use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDASC;
use Ox\Interop\Cda\Datatypes\Base\CCDATS;

/**
 * CCDARIMDevice Class
 */
class CCDARIMDevice extends CCDARIMManufacturedMaterial {

  /**
   * @var CCDASC
   */
  public $manufacturerModelName;

  /**
   * @var CCDASC
   */
  public $softwareName;

  /**
   * @var CCDACE
   */
  public $localRemoteControlStateCode;

  /**
   * @var CCDACE
   */
  public $alertLevelCode;

  /**
   * @var CCDATS
   */
  public $lastCalibrationTime;

}
