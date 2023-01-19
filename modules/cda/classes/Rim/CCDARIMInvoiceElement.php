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
use Ox\Interop\Cda\Datatypes\Base\CCDARTO_QTY_QTY;

/**
 * CCDARIMInvoiceElement Class
 */
class CCDARIMInvoiceElement extends CCDARIMAct {

  /**
   * @var CCDAMO
   */
  public $netAmt;

  /**
   * @var CCDAREAL
   */
  public $factorNumber;

  /**
   * @var CCDAREAL
   */
  public $pointsNumber;

  /**
   * @var CCDARTO_QTY_QTY
   */
  public $unitQuantity;

  /**
   * @var CCDARTO_QTY_QTY
   */
  public $unitPriceAmt;


  public $modifierCode = array();

}
