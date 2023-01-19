<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Rim;

use Ox\Interop\Cda\Datatypes\Base\CCDABL;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDAINT;
use Ox\Interop\Cda\Datatypes\Base\CCDATS;

/**
 * CCDARIMLivingSubject Class
 */
class CCDARIMLivingSubject extends CCDARIMEntity {

  /**
   * @var CCDACE
   */
  public $administrativeGenderCode;

  /**
   * @var CCDATS
   */
  public $birthTime;

  /**
   * @var CCDABL
   */
  public $deceasedInd;

  /**
   * @var CCDATS
   */
  public $deceasedTime;

  /**
   * @var CCDABL
   */
  public $multipleBirthInd;

  /**
   * @var CCDAINT
   */
  public $multipleBirthOrderNumber;

  /**
   * @var CCDABL
   */
  public $organDonorInd;

}
