<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Rim;

use Ox\Interop\Cda\Datatypes\Base\CCDAMO;
use Ox\Interop\Cda\Datatypes\Base\CCDAREAL;

/**
 * CCDARIMFinancialTransaction Class
 */
class CCDARIMFinancialTransaction extends CCDARIMAct {

  /**
   * @var CCDAMO
   */
  public $amt;

  /**
   * @var CCDAREAL
   */
  public $creditExchangeRateQuantity;

  /**
   * @var CCDAREAL
   */
  public $debitExchangeRateQuantity;

}
