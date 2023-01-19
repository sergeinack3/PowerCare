<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Rim;

use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDAMO;
use Ox\Interop\Cda\Datatypes\Base\CCDARTO_QTY_QTY;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAIVL_MO;

/**
 * CCDARIMAccount Class
 */
class CCDARIMAccount extends CCDARIMAct {

  /**
   * @var CCDAMO
   */
  public $balanceAmt;

  /**
   * @var CCDACE
   */
  public $currencyCode;

  /**
   * @var CCDARTO_QTY_QTY
   */
  public $interestRateQuantity;

  /**
   * @var CCDAIVL_MO
   */
  public $allowedBlanceQuantity;

}
