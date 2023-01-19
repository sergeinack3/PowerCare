<?php

/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\V2\Handle;

use Ox\AppFine\Server\CAppFineServer;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\CEAISejour;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Hl7\CHL7Acknowledgment;
use Ox\Interop\Hl7\CHL7v2MessageXML;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class MoveAccountInformation
 * Move account information, message XML HL7
 */
class MoveAccountInformation extends CHL7v2MessageXML
{
    static $event_codes = ["A44", "A45", "A50"];

    /**
     * Get data nodes
     *
     * @return array Get nodes
     */
    function getContentNodes()
    {
        $data = [];

        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $sender         = $exchange_hl7v2->_ref_sender;
        $sender->loadConfigValues();

        $specific_nodes = $this->getSpecificNodes($exchange_hl7v2->code, $sender);
        $data           = array_merge($data, $specific_nodes);

        return $data;
    }

    /**
     * Get data for specific nodes
     *
     * @param string         $event_code Event code
     * @param CInteropSender $sender     Interop sender
     *
     * @return array Get nodes
     */
    function getSpecificNodes($event_code, CInteropSender $sender)
    {
        $data = [];

        switch ($event_code) {
            case "A44":
                $sub_data = [];

                foreach ($this->queryNodes("ADT_A44.PATIENT") as $_patient_group) {
                    $sub_data["PID"] = $PID = $this->queryNode("PID", $_patient_group);

                    $sub_data["personIdentifiers"] = $this->getPersonIdentifiers("PID.3", $PID, $sender);
                    $sub_data["admitIdentifiers"]  = $this->getAdmitIdentifiers($PID, $sender);

                    $sub_data["PD1"] = $this->queryNode("PD1", $_patient_group);

                    $sub_data["MRG"] = $MRG = $this->queryNode("MRG", $_patient_group);

                    $sub_data["personChangeIdentifiers"] = $this->getPersonIdentifiers("MRG.1", $MRG, $sender);
                    $sub_data["admitChangeIdentifiers"]  = $this->getAdmitIdentifiers($MRG, $sender);

                    $data["merge"][] = $sub_data;
                }

                break;

            case "A45":
                $data["PID"]               = $PID = $this->queryNode("PID");
                $data["personIdentifiers"] = $this->getPersonIdentifiers("PID.3", $PID, $sender);

                $sub_data = [];

                foreach ($this->queryNodes("ADT_A45.MERGE_INFO") as $_merge_info) {
                    $sub_data["PV1"]              = $PV1 = $this->queryNode("PV1", $_merge_info);
                    $sub_data["admitIdentifiers"] = $this->getAdmitIdentifiers($PV1, $sender);

                    $sub_data["MRG"] = $MRG = $this->queryNode("MRG", $_merge_info);

                    $data["merge"][] = $sub_data;
                }

                break;

            case "A50":
                $data["PID"]               = $PID = $this->queryNode("PID");
                $data["personIdentifiers"] = $this->getPersonIdentifiers("PID.3", $PID, $sender);

                $data["PV1"]              = $PV1 = $this->queryNode("PV1");
                $data["admitIdentifiers"] = $this->getAdmitIdentifiers($PV1, $sender);

                $data["MRG"] = $MRG = $this->queryNode("MRG");

                break;

            default:
        }

        return $data;
    }

    /**
     * Handle event
     *
     * @param CHL7Acknowledgment $ack        Acknowledgement
     * @param CMbObject          $newPatient Person
     * @param array              $data       Nodes data
     *
     * @return null|string
     */
    function handle(CHL7Acknowledgment $ack = null, CMbObject $newPatient = null, $data = [])
    {
        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $exchange_hl7v2->_ref_sender->loadConfigValues();

        $function_handle = "handle$exchange_hl7v2->code";

        if (!method_exists($this, $function_handle)) {
            return $exchange_hl7v2->setAckAR($ack, "E006", null, $newPatient);
        }

        return $this->$function_handle($ack, $newPatient, $data);
    }

    /**
     * Handle event A44 - move account information - patient account number
     *
     * @param CHL7Acknowledgment $ack        Acknowledgment
     * @param CPatient           $newPatient Person
     * @param array              $data       Datas
     *
     * @return string
     */
    function handleA44(CHL7Acknowledgment $ack, CPatient $newPatient, $data)
    {
        // Traitement du message des erreurs
        $comment = "";

        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $sender         = $exchange_hl7v2->_ref_sender;

        // On considère que l'on a qu'un changement à faire
        if (count($data["merge"]) > 1) {
            return $exchange_hl7v2->setAckAR($ack, "E701", null, $newPatient);
        }
        $data = CMbArray::get($data["merge"], 0);

        // Si l'expéditeur est AppFine on fait un traitement spécifique
        if (CModule::getActive("appFine") && CMbArray::get($sender->_configs, "handle_portail_patient")
            && in_array($exchange_hl7v2->code, MoveAccountInformation::$event_codes)
        ) {
            return CAppFineServer::handleEvenementSejourA44($ack, $data, $sender, $exchange_hl7v2);
        }

        // Impossibilité dans Mediboard de modifier le patient d'un séjour
        if (CAppUI::conf("dPplanningOp CSejour patient_id") == 0) {
            return $exchange_hl7v2->setAckAR($ack, "E700", null, $newPatient);
        }

        $venue = new CSejour();

        $mbPatient       = new CPatient();
        $mbPatientChange = new CPatient();

        $patientPI = CValue::read($data['personIdentifiers'], "PI");
        $patientRI = CValue::read($data['personIdentifiers'], "RI");

        $patientChangePI = CValue::read($data['personChangeIdentifiers'], "PI");
        $patientChangeRI = CValue::read($data['personChangeIdentifiers'], "RI");

        // Acquittement d'erreur : identifiants RI et PI non fournis
        if (!$patientRI && !$patientPI || !$patientChangeRI && !$patientChangePI) {
            return $exchange_hl7v2->setAckAR($ack, "E100", null, $newPatient);
        }

        $idexPatient = CIdSante400::getMatch("CPatient", $sender->_tag_patient, $patientPI);
        if ($mbPatient->load($patientRI)) {
            if ($mbPatient->_id != $idexPatient->object_id) {
                $comment = "L'identifiant source fait référence au patient : $idexPatient->object_id";
                $comment .= " et l'identifiant cible au patient : $mbPatient->_id.";

                return $exchange_hl7v2->setAckAR($ack, "E601", $comment, $newPatient);
            }
        }
        if (!$mbPatient->_id) {
            $mbPatient->load($idexPatient->object_id);
        }

        $idexPatientChange = CIdSante400::getMatch("CPatient", $sender->_tag_patient, $patientChangePI);
        if ($mbPatientChange->load($patientChangeRI)) {
            if ($mbPatientChange->_id != $idexPatientChange->object_id) {
                $comment = "L'identifiant source fait référence au patient : $idexPatientChange->object_id";
                $comment .= "et l'identifiant cible au patient : $mbPatientChange->_id.";

                return $exchange_hl7v2->setAckAR($ack, "E602", $comment, $newPatient);
            }
        }
        if (!$mbPatientChange->_id) {
            $mbPatientChange->load($idexPatientChange->object_id);
        }

        if (!$mbPatient->_id || !$mbPatientChange->_id) {
            $comment = !$mbPatient->_id ?
                "Le patient $mbPatient->_id est inconnu dans Mediboard." : "Le patient $mbPatientChange->_id est inconnu dans Mediboard.";

            return $exchange_hl7v2->setAckAR($ack, "E603", $comment, $newPatient);
        }

        $venueAN = $this->getVenueAN($sender, $data);
        $NDA     = CIdSante400::getMatch("CSejour", $sender->_tag_sejour, $venueAN);
        if (!$venueAN || !$NDA->_id) {
            return $exchange_hl7v2->setAckAR($ack, "E604", $comment, $mbPatient);
        }

        $venue->load($NDA->object_id);

        // Impossibilité dans Mediboard de modifier le patient d'un séjour ayant une entrée réelle
        if (CAppUI::conf("dPplanningOp CSejour patient_id") == 2 && $venue->entree_reelle) {
            return $exchange_hl7v2->setAckAR($ack, "E605", null, $venue);
        }

        if ($venue->patient_id != $mbPatientChange->_id || $venue->patient_id != $mbPatient->_id) {
            return $exchange_hl7v2->setAckAR($ack, "E606", null, $venue);
        }

        $venue->patient_id = $mbPatient->_id;
        if ($msg = $venue->store()) {
            return $exchange_hl7v2->setAckAR($ack, "E607", $msg, $venue);
        }

        $comment = CEAISejour::getComment($venue);

        return $exchange_hl7v2->setAckAA($ack, "I600", $comment, $venue);
    }

    /**
     * Handle event A45 - move visit information - visit number
     *
     * @param CHL7Acknowledgment $ack        Acknowledgment
     * @param CPatient           $newPatient Person
     * @param array              $data       Datas
     *
     * @return string
     */
    function handleA45(CHL7Acknowledgment $ack, CPatient $newPatient, $data)
    {
        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $sender         = $exchange_hl7v2->_ref_sender;

        // On considère que l'on a qu'un changement à faire
        if (count($data["merge"]) > 1) {
            return $exchange_hl7v2->setAckAR($ack, "E701", null, $newPatient);
        }
        $merge = CValue::read($data["merge"], 0);

        $keep_patient = new CPatient();

        $patientPI = CValue::read($data['personIdentifiers'], "PI");
        // Acquittement d'erreur : identifiants RI et PI non fournis
        if (!$patientPI) {
            return $exchange_hl7v2->setAckAR($ack, "E100", null, $newPatient);
        }

        // Chargement du patient
        $idexPatient = CIdSante400::getMatch("CPatient", $sender->_tag_patient, $patientPI);
        $keep_patient->load($idexPatient->object_id);
        if (!$keep_patient->_id) {
            return $exchange_hl7v2->setAckAR($ack, "E702", null, $keep_patient);
        }

        // MRG-1 Ancien numéro du patient
        // Chargement du séjour pour ce patient
        $MRG_1              = $this->queryTextNode("MRG.1/CX.1", $merge["MRG"]);
        $idex               = new CIdSante400();
        $idex->object_class = "CPatient";
        $idex->id400        = $MRG_1;
        $idex->tag          = $sender->_tag_patient;
        /** @var CPatient $patient_removing */
        $patient_removing = $idex->getMbObject();

        if (!$patient_removing || !$patient_removing->_id) {
            return $exchange_hl7v2->setAckAR($ack, "E703", null, $newPatient);
        }

        // MRG-5 Numéro de dossier
        // Chargement du dossier par le numéro de séjour
        $MRG_5              = $this->queryTextNode("MRG.5/CX.1", $merge["MRG"]);
        $idex               = new CIdSante400();
        $idex->object_class = "CSejour";
        $idex->id400        = $MRG_5;
        $idex->tag          = $sender->_tag_sejour;
        /** @var CSejour $venue */
        $venue = $idex->getMbObject();

        if (!$venue || !$venue->_id) {
            return $exchange_hl7v2->setAckAR($ack, "E704", null, $newPatient);
        }

        // Si le patient du séjour retrouvé est différent de celui que l'on doit "supprimer"
        if ($venue->patient_id != $patient_removing->_id) {
            return $exchange_hl7v2->setAckAR($ack, "E705", null, $venue);
        }

        // Réattribution du patient_id sur le séjour
        // Impossibilité dans Mediboard de modifier le patient d'un séjour ayant une entrée réelle
        if (CAppUI::conf("dPplanningOp CSejour patient_id") == 2 && $venue->entree_reelle) {
            return $exchange_hl7v2->setAckAR($ack, "E706", null, $venue);
        }

        $venue->patient_id = $keep_patient->_id;

        // Notifier les autres destinataires autre que le sender
        $venue->_eai_sender_guid = $sender->_guid;
        if ($msg = $venue->store()) {
            return $exchange_hl7v2->setAckAR($ack, "E707", $msg, $venue);
        }

        $comment = CEAISejour::getComment($venue);

        return $exchange_hl7v2->setAckAA($ack, "I700", $comment, $venue);
    }

    /**
     * Handle event A50 - change visit number
     *
     * @param CHL7Acknowledgment $ack        Acknowledgment
     * @param CPatient           $newPatient Person
     * @param array              $data       Datas
     *
     * @return string
     */
    function handleA50(CHL7Acknowledgment $ack, CPatient $newPatient, $data)
    {
        // Traitement du message des erreurs
        $comment = "";
        $venue   = new CSejour();

        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $sender         = $exchange_hl7v2->_ref_sender;

        // Prise en charge du A50 seulement pour le NRA
        if (!CAppUI::conf("dPplanningOp CSejour use_dossier_rang")) {
            return $exchange_hl7v2->setAckAR($ack, "E801", null, $venue);
        }

        $patientPI = CValue::read($data['personIdentifiers'], "PI");
        $venueAN   = $this->getVenueAN($sender, $data);

        // Acquittement d'erreur : identifiants RI et PI non fournis
        if (!$patientPI || !$venueAN) {
            return $exchange_hl7v2->setAckAR($ack, "E100", null, $newPatient);
        }

        $idexVenue = CIdSante400::getMatch("CSejour", $sender->_tag_sejour, $venueAN);
        $venue->load($idexVenue->object_id);
        if (!$venue->_id) {
            return $exchange_hl7v2->setAckAR($ack, "E802", null, $venue);
        }

        // Chargement du NRA
        $venue->loadNRA($sender->group_id);
        $NRA = $venue->_ref_NRA;

        // MRG-6 Ancien FID
        $MRG_6 = $this->queryTextNode("MRG.6/CX.1", $data["MRG"]);
        if ($NRA->id400 != $MRG_6) {
            return $exchange_hl7v2->setAckAR($ack, "E803", null, $venue);
        }

        // Réattribution du nouveau NRA
        $PV1_50 = $this->queryTextNode("PV1.50/CX.1", $data["PV1"]);

        $NRA->id400 = $PV1_50;

        if ($msg = $NRA->store()) {
            return $exchange_hl7v2->setAckAR($ack, "E804", $msg, $venue);
        }

        return $exchange_hl7v2->setAckAA($ack, "I800", $comment, $venue);
    }
}
