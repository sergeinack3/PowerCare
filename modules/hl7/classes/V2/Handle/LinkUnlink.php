<?php

/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\V2\Handle;

use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Interop\Hl7\CHL7Acknowledgment;
use Ox\Interop\Hl7\CHL7v2MessageXML;
use Ox\Mediboard\Patients\CPatient;

/**
 * Class LinkUnlink
 * Link/Unlink patients, message XML HL7
 */
class LinkUnlink extends CHL7v2MessageXML
{
    static $event_codes = ["A24", "A37"];

    /**
     * Get contents
     *
     * @return array
     */
    function getContentNodes()
    {
        $data = [];

        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $sender         = $exchange_hl7v2->_ref_sender;
        $sender->loadConfigValues();

        $this->_ref_sender = $sender;

        $this->queryNode("EVN", null, $data, true);

        $sub_data = [];
        foreach ($this->queryNodes("PID") as $_PID) {
            $sub_data["DOMElement"]        = $_PID;
            $sub_data["personIdentifiers"] = $this->getPersonIdentifiers("PID.3", $_PID, $sender);

            $data["PID"][] = $sub_data;
        }

        return $data;
    }

    /**
     * Handle link/unlink patients message
     *
     * @param CHL7Acknowledgment $ack     Acknowledgment
     * @param CPatient           $patient Person
     * @param array              $data    Data
     *
     * @return string|void
     */
    function handle(CHL7Acknowledgment $ack = null, CMbObject $patient = null, $data = [])
    {
        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $sender         = $exchange_hl7v2->_ref_sender;
        $sender->loadConfigValues();

        $this->_ref_sender = $sender;

        if (count($data["PID"]) != 2) {
            return $exchange_hl7v2->setAckAR($ack, "E500", null, $patient);
        }

        foreach ($data["PID"] as $_PID) {
            $patientPI = CValue::read($_PID['personIdentifiers'], "PI");

            // Acquittement d'erreur : identifiants PI non fournis
            if (!$patientPI) {
                return $exchange_hl7v2->setAckAR($ack, "E100", null, $patient);
            }
        }

        $patient_1_PI = CValue::read($data["PID"][0]['personIdentifiers'], "PI");
        $patient_2_PI = CValue::read($data["PID"][1]['personIdentifiers'], "PI");

        $patient_1       = new CPatient();
        $patient_1->_IPP = $patient_1_PI;
        $patient_1->loadFromIPP($sender->group_id);
        // PI non connu (non fourni ou non retrouvé)
        if (!$patient_1->_id) {
            return $exchange_hl7v2->setAckAR($ack, "E501", null, $patient_1);
        }

        $patient_2       = new CPatient();
        $patient_2->_IPP = $patient_2_PI;
        $patient_2->loadFromIPP($sender->group_id);
        // PI non connu (non fourni ou non retrouvé)
        if (!$patient_2->_id) {
            return $exchange_hl7v2->setAckAR($ack, "E501", null, $patient_2);
        }

        $function_handle = "handle$exchange_hl7v2->code";

        if (!method_exists($this, $function_handle)) {
            return $exchange_hl7v2->setAckAR($ack, "E006", null, $patient);
        }

        return $this->$function_handle($ack, $patient_1, $patient_2, $data);
    }

    /**
     * Handle event A24 - Link two patients
     *
     * @param CHL7Acknowledgment $ack       Acknowledgment
     * @param CPatient           $patient_1 Person
     * @param CPatient           $patient_2 Person
     * @param array              $data      Data
     *
     * @return string
     */
    function handleA24(CHL7Acknowledgment $ack, CPatient $patient_1, CPatient $patient_2, $data)
    {
        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;

        // Association des deux patients
        $patient_1->patient_link_id = $patient_2->_id;
        if ($msg = $patient_1->store()) {
            return $exchange_hl7v2->setAckAR($ack, "E502", $msg, $patient_1);
        }

        return $exchange_hl7v2->setAckAA($ack, "I501", null, $patient_1);
    }

    /**
     * Handle event A37 - Unlink two previously linked patients
     *
     * @param CHL7Acknowledgment $ack       Acknowledgment
     * @param CPatient           $patient_1 Person
     * @param CPatient           $patient_2 Person
     * @param array              $data      Data
     *
     * @return string
     */
    function handleA37(CHL7Acknowledgment $ack, CPatient $patient_1, CPatient $patient_2, $data)
    {
        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;

        // Association des deux patients
        $patient_1->patient_link_id = "";
        if ($msg = $patient_1->store()) {
            return $exchange_hl7v2->setAckAR($ack, "E503", $msg, $patient_1);
        }

        return $exchange_hl7v2->setAckAA($ack, "I502", null, $patient_1);
    }
}
