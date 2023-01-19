<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use Ox\Core\CMbException;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CInteropNorm;
use Ox\Interop\Hl7\Events\CPRPA;

/**
 * Class CHL7v3Messaging
 * Patient Administration
 */
class CHL7v3Messaging extends CInteropNorm
{
    /**
     * @see parent::__construct
     */
    function __construct()
    {
        $this->name = "CHL7v3Messaging";

        parent::__construct();
    }

    /**
     * Return data format object
     *
     * @param CExchangeDataFormat $exchange Instance of exchange
     *
     * @return object An instance of data format
     * @throws CMbException
     *
     */
    static function getEvent(CExchangeDataFormat $exchange)
    {
        switch ($exchange->type) {
            case "PRPA":
                return CPRPA::getEvent($exchange);

            default:
                throw new CMbException("CHL7v3Messaging_event-unknown");
        }
    }
}
