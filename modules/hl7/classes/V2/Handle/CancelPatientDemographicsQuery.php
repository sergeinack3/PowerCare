<?php

/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\V2\Handle;

use Ox\Core\CMbObject;
use Ox\Interop\Hl7\CHL7Acknowledgment;
use Ox\Interop\Hl7\CHL7v2MessageXML;

/**
 * Class CancelPatientDemographicsQuery
 * Cancel Query, message XML HL7
 */
class CancelPatientDemographicsQuery extends CHL7v2MessageXML
{

    /** @var string */
    static $event_codes = ["J01"];

    /**
     * Get data nodes
     *
     * @return array Get nodes
     */
    function getContentNodes()
    {
        $data = [];

        return $data;
    }

    /**
     * Handle event
     *
     * @param CHL7Acknowledgment $ack     Acknowledgement
     * @param CMbObject          $patient Person
     * @param array              $data    Nodes data
     *
     * @return null|string
     */
    function handle(CHL7Acknowledgment $ack = null, CMbObject $patient = null, $data = [])
    {
        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $sender         = $exchange_hl7v2->_ref_sender;
        $sender->loadConfigValues();

        $this->_ref_sender = $sender;

        return $exchange_hl7v2->setAckAA($ack, null);
    }
}
