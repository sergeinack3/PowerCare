<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Rim;

use Ox\Interop\Cda\Datatypes\Base\CCDAIVL_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAPQ;

/**
 * CCDARIMSupply class
 */
class CCDARIMSupply extends CCDARIMAct {

  /**
   * @var CCDAPQ
   */
  public $quantity;

  /**
   * @var CCDAIVL_TS
   */
  public $expectedUseTime;

}
