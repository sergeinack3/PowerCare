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

/**
 * CCDARIMNonPersonLivingSubject Class
 */
class CCDARIMNonPersonLivingSubject extends CCDARIMLivingSubject {

  /**
   * @var CCDAED
   */
  public $strainText;

  /**
   * @var CCDACE
   */
  public $genderStatusCode;

}
