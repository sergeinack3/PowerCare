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
 * Person class
 */
class CCDARIMPerson extends CCDARIMLivingSubject {

  /**
   * @var CCDACE
   */
  public $maritalStatusCode;

  /**
   * @var CCDACE
   */
  public $educationLevelCode;

  /**
   * @var CCDACE
   */
  public $livingArrangementCode;

  /**
   * @var CCDACE
   */
  public $religiousAffiliationCode;

  public $addr            = array();
  public $raceCode        = array();
  public $disabilityCode  = array();
  public $ethnicGroupCode = array();

}
