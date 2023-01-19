<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Rim;

use Ox\Interop\Cda\Datatypes\Base\CCDAAD;
use Ox\Interop\Cda\Datatypes\Base\CCDABL;
use Ox\Interop\Cda\Datatypes\Base\CCDAED;
use Ox\Interop\Cda\Datatypes\Base\CCDAST;

/**
 * CCDARIMPlace Class
 */
class CCDARIMPlace extends CCDARIMEntity {

  /**
   * @var CCDABL
   */
  public $mobileInd;

  /**
   * @var CCDAAD
   */
  public $addr;

  /**
   * @var CCDAED
   */
  public $directionText;

  /**
   * @var CCDAED
   */
  public $positionText;

  /**
   * @var CCDAST
   */
  public $gpsText;

}