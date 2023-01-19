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
use Ox\Interop\Cda\Datatypes\Base\CCDACS;
use Ox\Interop\Cda\Datatypes\Base\CCDAED;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVL_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAST;

/**
 * CCDARIMAct Class
 */
class CCDARIMAct extends CCDAClasseCda
{

    /**
     * @var CCDACS
     */
    public $classCode;

    /**
     * @var CCDACS
     */
    public $moodCode;

    /**
     * @var CCDACE
     */
    public $code;

    /**
     * @var CCDABL
     */
    public $negationInd;

    /**
     * @var CCDAST
     */
    public $derivationExpr;

    /**
     * @var CCDAST
     */
    public $title;

    /**
     * @var CCDAED
     */
    public $text;

    /**
     * @var CCDACS
     */
    public $statusCode;

    /**
     * @var CCDAIVL_TS
     */
    public $effectiveTime;

    /**
     * @var CCDAIVL_TS
     */
    public $activityTime;

    /**
     * @var CCDABL
     */
    public $interruptibleInd;

    /**
     * @var CCDACE
     */
    public $levelCode;

    /**
     * @var CCDABL
     */
    public $independentInd;

    /**
     * @var CCDACE
     */
    public $uncertaintyCode;

    /**
     * @var CCDACS
     */
    public $languageCode;

    /**
     * @var CCDAII
     */
    public $id = array();

    public $priorityCode = [];

    /**
     * @var CCDACE
     */
    public $confidentialityCode;
    public $repeatNumber = [];
    public $reasonCode   = [];

}
