<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Rim;

use Ox\Interop\Cda\Datatypes\Base\CCDACE;

/**
 * CCDARIMOrganization Class
 */
class CCDARIMOrganization extends CCDARIMEntity {

  /**
   * @var CCDACE
   */
  public $standardIndustryClassCode;

  public $addr = array();

}
