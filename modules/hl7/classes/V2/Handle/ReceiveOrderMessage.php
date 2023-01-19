<?php

/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\V2\Handle;

use DOMNode;
use Ox\AppFine\Server\CAppFineServer;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Interop\Hl7\CHL7Acknowledgment;
use Ox\Interop\Hl7\CHL7v2MessageXML;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class ReceiveOrderMessage
 * Order message, message XML HL7
 */
class ReceiveOrderMessage extends CHL7v2MessageXML
{
    static $event_codes = ["O01"];

    /**
     * Get contents
     *
     * @return array
     */
    function getContentNodes()
    {
        $data = parent::getContentNodes();

        $pv1 = $this->queryNode("PV1", null, $data, true);

        $data["admitIdentifiers"] = $this->getAdmitIdentifiers($pv1, $this->_ref_sender);

        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $sender         = $exchange_hl7v2->_ref_sender;
        $sender->loadConfigValues();

        $ORDER = $this->queryNodes("ORM_O01.ORDER", null, $varnull, true);
        if (CModule::getActive("appFine") && (CMbArray::get($sender->_configs, "handle_portail_patient"))) {
            $data["orders"] = [];
            foreach ($ORDER as $_ORM_O01_ORDER) {
                $tmp = [];
                // ORC
                $this->queryNode("ORC", $_ORM_O01_ORDER, $tmp, null);
                // OBX
                $this->queryNode("ORM_O01.ORDER_DETAIL/ORM_O01.OBSERVATION/OBX", $_ORM_O01_ORDER, $tmp, null);
                // OBR
                $this->queryNode("ORM_O01.ORDER_DETAIL/ORM_O01.ORDER_DETAIL_SEGMENTS/OBR", $_ORM_O01_ORDER, $tmp, null);

                $data["orders"][] = $tmp;
            }
        } else {
            foreach ($ORDER as $_ORM_O01_ORDER) {
                // ORC
                $this->queryNode("ORC", $_ORM_O01_ORDER, $data, true);
            }

            $ORDER_DETAIL          = $this->queryNode("ORM_O01.ORDER_DETAIL", null, $varnull, true);
            $ORDER_DETAIL_SEGMENTS = $this->queryNode("ORM_O01.ORDER_DETAIL_SEGMENTS", $ORDER_DETAIL, $varnull, true);

            // OBR
            $this->queryNode("OBR", $ORDER_DETAIL_SEGMENTS, $data, true);
        }

        return $data;
    }

    /**
     * Handle receive order message
     *
     * @param CHL7Acknowledgment $ack     Acknowledgment
     * @param CMbObject          $patient Person
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

        if (CModule::getActive("appFine") && (CMbArray::get($sender->_configs, "handle_portail_patient"))) {
            return CAppFineServer::handleOrderMessage($ack, $data, $sender, $exchange_hl7v2);
        }
        $patientPI = CValue::read($data['personIdentifiers'], "PI");

        if (!$patientPI) {
            return $exchange_hl7v2->setORRError($ack, "E007");
        }

        $IPP = CIdSante400::getMatch("CPatient", $sender->_tag_patient, $patientPI);
        // Patient non retrouvé par son IPP
        if (!$IPP->_id) {
            return $exchange_hl7v2->setORRError($ack, "E105");
        }
        $patient->load($IPP->object_id);

        $venueAN = $this->getVenueAN($sender, $data);

        $NDA = CIdSante400::getMatch("CSejour", $sender->_tag_sejour, $venueAN);
        // Séjour non retrouvé par son NDA
        if (!$NDA->_id) {
            return $exchange_hl7v2->setORRError($ack, "E205");
        }
        $sejour = new CSejour();
        $sejour->load($NDA->object_id);

        // Common order - ORC
        $orc           = $data["ORC"];
        $obr           = $data["OBR"];
        $event_request = $this->getEventRequest($orc);
        $consultation  = new CConsultation();

        $placer_id = $this->getPlacerNumber($orc);
        $filler_id = $this->getFillerNumber($orc);

        switch ($event_request) {
            // new order
            case "SN":
                $datetime = $this->getDate($orc);
                $orc12    = $this->getDoctorNode($orc, $data);

                $XCNs = $this->queryNodes('ORC.12', $orc);
                if (!$XCNs || $XCNs->length === 0) {
                    return $exchange_hl7v2->setORRError($ack, "E801");
                }

                $medisuer_id = $this->getDoctor($XCNs, new CMediusers(), false);
                if (!$medisuer_id) {
                    return $exchange_hl7v2->setORRError($ack, "E801");
                }
                try {
                    $consultation->createByDatetime($datetime, $medisuer_id, $patient->_id);
                } catch (CMbException $e) {
                    return $exchange_hl7v2->setORRError($ack, "E802", $e->getMessage());
                }

                if (!$consultation->_id) {
                    return $exchange_hl7v2->setORRError($ack, "E802");
                }

                $idex        = new CIdSante400();
                $idex->id400 = $filler_id;
                $idex->tag   = $sender->_tag_consultation;
                $idex->setObject($consultation);
                $idex->store();
                break;
            //Modification
            case "SC":
                $consultation->load($placer_id);
                $status_code = $this->getStatusCode($orc);
                switch ($status_code) {
                    case "CM":
                        $status = CConsultation::TERMINE;
                        break;
                    case "OD":
                        $status = CConsultation::PLANIFIE;
                        break;
                    case "IP":
                        $status = CConsultation::EN_COURS;
                        break;
                    default:
                        return $exchange_hl7v2->setORRError($ack, "E803");
                }
                $consultation->chrono = $status;

                if ($msg = $consultation->store()) {
                    return $exchange_hl7v2->setORRError($ack, "E804", $msg);
                }

                $obr4 = $this->getExamen("OBR.4", $obr, $data);

                //Identifiant de l'élément de prescription
                $examen_id   = $this->getExamenID($obr4);
                $examen_name = $this->getExamenName($obr4);

                //todo gérer avec l'élément de prescription

                break;
            // cancel order request
            case "OC":
                $consultation->annule = "1";
                if ($msg = $consultation->store()) {
                    return $exchange_hl7v2->setORRError($ack, "E804", $msg);
                }

                $idex        = CIdSante400::getMatchFor($consultation, $sender->_tag_consultation);
                $idex->id400 = "trash_$idex->id400";
                if ($msg = $idex->store()) {
                    return $exchange_hl7v2->setORRError($ack, "E805", $msg);
                }
                break;
            default:
                return $exchange_hl7v2->setORRError($ack, "E205");
        }

        return $exchange_hl7v2->setORRSuccess($ack);
    }

    /**
     * Get event request (Order Control)
     *
     * @param DOMNode $node ORC node
     *
     * @return string
     */
    function getEventRequest(DOMNode $node)
    {
        return $this->queryTextNode("ORC.1", $node);
    }

    /**
     * Get the placer number
     *
     * @param DOMNode $node ORC node
     *
     * @return string
     */
    function getPlacerNumber($node)
    {
        $placer = "ORC.2/EI.1";
        if ($this->_ref_sender->_configs["change_filler_placer"]) {
            $placer = "ORC.3/EI.1";
        }

        return $this->queryTextNode($placer, $node);
    }

    /**
     * Get the filler number
     *
     * @param DOMNode $node ORC node
     *
     * @return string
     */
    function getFillerNumber($node)
    {
        $filler = "ORC.3/EI.1";
        if ($this->_ref_sender->_configs["change_filler_placer"]) {
            $filler = "ORC.2/EI.1";
        }

        return $this->queryTextNode($filler, $node);
    }

    /**
     * Get the date
     *
     * @param DOMNode $node ORC node
     *
     * @return string
     */
    function getDate($node)
    {
        return $this->queryTextNode("ORC.9/TS.1", $node);
    }

    /**
     * Get the doctor information
     *
     * @param DOMNode $node ORC node
     * @param array   $data array of data
     *
     * @return string
     */
    function getDoctorNode($node, $data)
    {
        return $this->queryNode("ORC.12", $node, $data);
    }

    /**
     * Get the status code
     *
     * @param DOMNode $node ORC node
     *
     * @return string
     */
    function getStatusCode($node)
    {
        return $this->queryTextNode("ORC.5", $node);
    }

    /**
     * Get the examen
     *
     * @param DOMNode $node ORC node
     * @param array   $data array of data
     *
     * @return string
     */
    function getExamen($node, $data)
    {
        return $this->queryNode("OBR.4", $node, $data);
    }

    /**
     * Get the examen identifiant
     *
     * @param DOMNode $node ORC node
     *
     * @return string
     */
    function getExamenID($node)
    {
        return $this->queryTextNode("CE.1", $node);
    }

    /**
     * Get the examen name
     *
     * @param DOMNode $node ORC node
     *
     * @return string
     */
    function getExamenName($node)
    {
        return $this->queryTextNode("CE.2", $node);
    }
}
