<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Rim;

use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDACS;
use Ox\Interop\Cda\Datatypes\Base\CCDAED;

/**
 * CCDARIMEntity Class
 */
class CCDARIMEntity extends CCDAClasseCda {

  /**
   * @var CCDACS
   */
  public $classCode;

  /**
   * @var CCDACS
   */
  public $determinerCode;

  /**
   * @var CCDACE
   */
  public $code;

  /**
   * @var CCDAED
   */
  public $desc;

  /**
   * @var CCDACS
   */
  public $statusCode;

  /**
   * @var CCDACE
   */
  public $riskCode;

  /**
   * @var CCDACE
   */
  public $handlingCode;

  public $id            = array();
  public $quantity      = array();
  public $name          = array();
  public $existenceTime = array();
  public $telecom       = array();

}