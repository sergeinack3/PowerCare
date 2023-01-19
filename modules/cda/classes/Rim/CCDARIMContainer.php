<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Rim;

use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDAPQ;

/**
 * CCDARIMContainer Class
 */
class CCDARIMContainer extends CCDARIMManufacturedMaterial {

  /**
   * @var CCDAPQ
   */
  public $capacityQuantity;

  /**
   * @var CCDAPQ
   */
  public $heightquantity;

  /**
   * @var CCDAPQ
   */
  public $diameterQuantity;

  /**
   * @var CCDACE
   */
  public $capTypeCode;

  /**
   * @var CCDACE
   */
  public $seperatortypeCode;

  /**
   * @var CCDAPQ
   */
  public $barrierDeltaQuantity;

  /**
   * @var CCDAPQ
   */
  public $bottomDeltaQuantity;
}
