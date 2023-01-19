<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Rim;

use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\Datatypes\Base\CCDABL;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;

/**
 * CCDARIMLanguageCommunication Class
 */
class CCDARIMLanguageCommunication extends CCDAClasseCda {

  /**
   * @var CCDACE
   */
  public $languageCode;

  /**
   * @var CCDACE
   */
  public $modeCode;

  /**
   * @var CCDACE
   */
  public $proficiencyLevelCode;

  /**
   * @var CCDABL
   */
  public $preferenceInd;

}