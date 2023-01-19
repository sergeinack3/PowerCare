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
use Ox\Interop\Cda\Datatypes\Base\CCDACS;
use Ox\Interop\Cda\Datatypes\Base\CCDAINT;
use Ox\Interop\Cda\Datatypes\Base\CCDAPQ;
use Ox\Interop\Cda\Datatypes\Base\CCDAST;

/**
 * CCDARIMActRelationship Class
 */
class CCDARIMActRelationship extends CCDAClasseCda {

  /**
   * @var CCDACS
   */
  public $typeCode;

  /**
   * @var CCDABL
   */
  public $inversionInd;

  /**
   * @var CCDACS
   */
  public $contextControlCode;

  /**
   * @var CCDABL
   */
  public $contextConductionInd;

  /**
   * @var CCDAINT
   */
  public $sequenceNumber;

  /**
   * @var CCDAINT
   */
  public $priorityNumber;

  /**
   * @var CCDAPQ
   */
  public $pauseQuantity;

  /**
   * @var CCDACS
   */
  public $checkpointcode;

  /**
   * @var CCDACS
   */
  public $splitCode;

  /**
   * @var CCDACS
   */
  public $joinCode;

  /**
   * @var CCDABL
   */
  public $negationInd;

  /**
   * @var CCDACS
   */
  public $conjunctionCode;

  /**
   * @var CCDAST
   */
  public $localVariableName;

  /**
   * @var CCDABL
   */
  public $seperatableInd;

  /**
   * @var CCDACS
   */
  public $subsetCode;

}