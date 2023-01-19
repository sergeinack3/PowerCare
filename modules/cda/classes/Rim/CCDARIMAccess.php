<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Rim;

use Ox\Interop\Cda\Datatypes\Base\CCDACD;
use Ox\Interop\Cda\Datatypes\Base\CCDAPQ;

/**
 * CCDARIMAccess Class
 */
class CCDARIMAccess extends CCDARIMRole {

  /**
   * @var CCDACD
   */
  public $approachSiteCode;

  /**
   * @var CCDACD
   */
  public $targetSiteCode;

  /**
   * @var CCDAPQ
   */
  public $gaugeQuantity;
}
