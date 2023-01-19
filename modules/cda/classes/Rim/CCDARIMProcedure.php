<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Rim;

/**
 * CCDARIMProcedure Class
 */
class CCDARIMProcedure extends CCDARIMAct {

  public $methodCode = array();
  public $approachSiteCode = array();
  public $targetSiteCode = array();

}
