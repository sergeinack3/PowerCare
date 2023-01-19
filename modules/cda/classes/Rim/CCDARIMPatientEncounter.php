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
use Ox\Interop\Cda\Datatypes\Base\CCDAPQ;

/**
 * CCDARIMPatientEncounter Class
 */
class CCDARIMPatientEncounter extends CCDARIMAct {

  /**
   * @var CCDABL
   */
  public $preAdmitTestInd;

  /**
   * @var CCDACE
   */
  public $admissionreferralSourceCode;

  /**
   * @var CCDAPQ
   */
  public $lengthOfStayQuantity;

  /**
   * @var CCDACE
   */
  public $dischargeDispositionCode;

  public $specialCourtesiesCode  = array();
  public $specialArrangementCode = array();

}
