<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Rim;

use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAIVL_PQ;

/**
 * CCDARIMSubstanceAdministration Class
 */
class CCDARIMSubstanceAdministration extends CCDARIMAct {

  /**
   * @var CCDACE
   */
  public $routeCode;

  /**
   * @var CCDACE
   */
  public $administrationUnitCode;

  /**
   * @var CCDAIVL_PQ
   */
  public $doseQuantity;

  /**
   * @var CCDAIVL_PQ
   */
  public $rateQuantity;


  public $approachSiteCode   = array();
  public $doseCheckQuantity  = array();
  public $maxDoseQuantity    = array();

}
