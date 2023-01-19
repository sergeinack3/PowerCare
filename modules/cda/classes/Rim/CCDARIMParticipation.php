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
use Ox\Interop\Cda\Datatypes\Base\CCDACD;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDACS;
use Ox\Interop\Cda\Datatypes\Base\CCDAED;
use Ox\Interop\Cda\Datatypes\Base\CCDAINT;

/**
 * CCDARIMParticipation Class
 */
class CCDARIMParticipation extends CCDAClasseCda {

  /**
   * @var CCDACS
   */
  public $typeCode;

  /**
   * @var CCDACD
   */
  public $functionCode;

  /**
   * @var CCDACS
   */
  public $contextControlCode;

  /**
   * @var CCDAINT
   */
  public $sequenceNumber;

  /**
   * @var CCDABL
   */
  public $negationInd;

  /**
   * @var CCDAED
   */
  public $noteText;

  /**
   * @var CCDACE
   */
  public $modeCode;

  /**
   * @var CCDACE
   */
  public $awarenessCode;

  /**
   * @var CCDACE
   */
  public $signatureCode;

  /**
   * @var CCDAED
   */
  public $signatureText;

  /**
   * @var CCDABL
   */
  public $performInd;

  /**
   * @var CCDACE
   */
  public $substitutionConditionCode;

  public $time = array();

}