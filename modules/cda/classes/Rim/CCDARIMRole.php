<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Rim;

use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\Datatypes\Base\CCDABL;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDACS;
use Ox\Interop\Cda\Datatypes\Base\CCDAED;
use Ox\Interop\Cda\Datatypes\Base\CCDARTO;

/**
 * CCDARIMRole Class
 */
class CCDARIMRole extends CCDAClasseCda {

  /**
   * @var CCDACS
   */
  public $classCode;

  /**
   * @var CCDACE
   */
  public $code;

  /**
   * @var CCDABL
   */
  public $negationInd;

  /**
   * @var CCDACS
   */
  public $statusCode;

  /**
   * @var CCDAED
   */
  public $certificateText;

  /**
   * @var CCDARTO
   */
  public $quantity;

  public $id                  = array();
  public $name                = array();
  public $addr                = array();
  public $telecom             = array();
  public $effectiveTime       = array();
  public $confidentialityCode = array();
  public $positionNumber      = array();

}
