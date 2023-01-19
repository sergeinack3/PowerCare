<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Rim;

use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\Datatypes\Base\CCDACS;
use Ox\Interop\Cda\Datatypes\Base\CCDAINT;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVL_TS;

/**
 * CCDARIMRoleLink Class
 */
class CCDARIMRoleLink extends CCDAClasseCda {

  /**
   * @var CCDACS
   */
  public $typeCode;

  /**
   * @var CCDAINT
   */
  public $priorityNumber;

  /**
   * @var CCDAIVL_TS
   */
  public $effectiveTime;

}