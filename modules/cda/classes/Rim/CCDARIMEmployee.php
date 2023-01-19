<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Rim;

use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDAED;
use Ox\Interop\Cda\Datatypes\Base\CCDAMO;
use Ox\Interop\Cda\Datatypes\Base\CCDASC;

/**
 * CCDARIMEmployee Class
 */
class CCDARIMEmployee extends CCDARIMRole {

  /**
   * @var CCDACE
   */
  public $jobCode;

  /**
   * @var CCDASC
   */
  public $jobTitleName;

  /**
   * @var CCDACE
   */
  public $jobClassCode;

  /**
   * @var CCDACE
   */
  public $occupationCode;

  /**
   * @var CCDACE
   */
  public $salaryTypeCode;

  /**
   * @var CCDAMO
   */
  public $salaryQuantity;

  /**
   * @var CCDAED
   */
  public $hazardExposureText;

  /**
   * @var CCDAED
   */
  public $protectiveEquipementText;
}
