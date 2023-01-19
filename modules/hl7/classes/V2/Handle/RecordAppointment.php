<?php

/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\V2\Handle;

use DOMNode;
use Exception;
use Ox\AppFine\Client\CAppFineClient;
use Ox\AppFine\Server\CAppFineServer;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Erp\CabinetSIH\CCabinetSIHRecordData;
use Ox\Interop\Eai\CEAIMbObject;
use Ox\Interop\Eai\Tools\CDoctorTrait;
use Ox\Interop\Hl7\CHL7Acknowledgment;
use Ox\Interop\Hl7\CHL7v2MessageXML;
use Ox\Mediboard\Cabinet\CAgendaPraticien;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Doctolib\CDoctolib;
use Ox\Mediboard\Doctolib\CSenderHL7v2Doctolib;
use Ox\Mediboard\Galaxie\CGalaxie;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class RecordAppointment
 * Record appointment, message XML
 */
class RecordAppointment extends CHL7v2MessageXML
{
    use CDoctorTrait;

    /** @var string[] Event codes */
    public static $event_codes = ["S12", "S13", "S14", "S15", "S16", "S17", "S26"];

    /**
     * @see parent::getContentNodes
     */
    function getContentNodes()
    {
        $data = $resources = [];

        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $sender         = $exchange_hl7v2->_ref_sender;
        $sender->loadConfigValues();

        $this->queryNode("SCH", null, $data, true);

        $this->queryNodes("NTE", null, $data, true);

        $PID                       = $this->queryNode("PID", null, $data, true);
        $data["personIdentifiers"] = $this->getPersonIdentifiers("PID.3", $PID, $sender);

        $this->queryNode("PD1", null, $data, true);

        $this->queryNode("PV1", null, $data, true);

        $resources         = $this->queryNodes("SIU_$exchange_hl7v2->code.RESOURCES", null, $varnull, true);
        $data["resources"] = [];
        foreach ($resources as $_resource) {
            $tmp = [];

            $this->queryNodes("RGS", $_resource, $tmp, true);

            // $AISs
            $AISs = $this->queryNodes("SIU_$exchange_hl7v2->code.SERVICE", $_resource, $varnull);
            foreach ($AISs as $_AIS) {
                $this->queryNodes("AIS", $_AIS, $tmp, true);
            }

            // AIGs
            $AIGs = $this->queryNodes("SIU_$exchange_hl7v2->code.GENERAL_RESOURCE", $_resource, $varnull);
            foreach ($AIGs as $_AIG) {
                $this->queryNodes("AIG", $_AIG, $tmp, true);
            }

            // $AILs
            $AILs = $this->queryNodes("SIU_$exchange_hl7v2->code.LOCATION_RESOURCE", $_resource, $varnull);
            foreach ($AILs as $_AIL) {
                $this->queryNodes("AIL", $_AIL, $tmp, true);
            }

            // AIPs
            $AIPs = $this->queryNodes("SIU_$exchange_hl7v2->code.PERSONNEL_RESOURCE", $_resource, $varnull);
            foreach ($AIPs as $_AIP) {
                $this->queryNodes("AIP", $_AIP, $tmp, true);
            }

            if ($tmp) {
                $data["resources"][] = $tmp;
            }
        }

        $this->queryNode("ZTG", null, $data, true);

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
     * @throws Exception
     */
    function handle(CHL7Acknowledgment $ack = null, CMbObject $patient = null, $data = [])
    {
        $modif_appointment = false;

        // Traitement du message des erreurs
        $object = null;

        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $exchange_hl7v2->_ref_sender->loadConfigValues();
        $sender  = $this->_ref_sender = $exchange_hl7v2->_ref_sender;
        $configs = $sender->_configs;

        // Pas d'observations
        $first_result = reset($data["resources"]);

        // Traitement spécifique pour Doctolib
        if (CMbArray::get($sender->_configs, "handle_doctolib") && CModule::getActive("doctolib")) {
            CSenderHL7v2Doctolib::storeLastEvent($exchange_hl7v2);
        }

        // Traitement spécifique pour AppFine Client
        if (CModule::getActive("appFineClient") && CMbArray::get($sender->_configs, "handle_portail_patient")
            && in_array($exchange_hl7v2->code, RecordAppointment::$event_codes)
        ) {
            return CAppFineClient::handleConsultationPatient($ack, $data, $sender, $exchange_hl7v2);
        }

        // Traitement spécifique pour AppFine
        if (CModule::getActive("appFine") && CMbArray::get($sender->_configs, "handle_portail_patient")
            && in_array($exchange_hl7v2->code, RecordAppointment::$event_codes)
        ) {
            return CAppFineServer::handleConsultationPatient($ack, $data, $sender, $exchange_hl7v2);
        }

        // Traitement spécifique pour TAMM-SIH
        if (CModule::getActive("oxCabinetSIH") && CMbArray::get($configs, "handle_tamm_sih")
            && in_array($exchange_hl7v2->code, RecordAppointment::$event_codes)) {
            return CCabinetSIHRecordData::handleOperation($ack, $data, $sender, $exchange_hl7v2);
        }

        if (!$first_result) {
            return $exchange_hl7v2->setAckAR($ack, "E1000", null, $patient);
        }

        if ($sender->_configs["handle_SIU_object"] !== 'consultation') {
            return $exchange_hl7v2->setAckAR($ack, "E1011", null, $patient);
        }

        $patient = new CPatient();
        // Traitement du patient
        if (CMbArray::get($configs, "handle_patient_SIU")) {
            $hl7v2_record_person                      = new RecordPerson();
            $hl7v2_record_person->_ref_exchange_hl7v2 = $exchange_hl7v2;
            $msg_ack                                  = $hl7v2_record_person->handle($ack, $patient, $data);

            // Retour de l'acquittement si erreur sur le traitement du patient
            if ($exchange_hl7v2->statut_acquittement == "AR") {
                return $msg_ack;
            }
        } else {
            $patientPI = CValue::read($data['personIdentifiers'], "PI");
            // Patient
            if (!$patientPI) {
                return $exchange_hl7v2->setAckAR($ack, "E1001", null, $patient);
            }

            $IPP = CIdSante400::getMatch("CPatient", $sender->_tag_patient, $patientPI);
            // Patient non retrouvé par son IPP
            if (!$IPP->_id) {
                return $exchange_hl7v2->setAckAR($ack, "E1002", null, $patient);
            }
            $patient->load($IPP->object_id);
        }

        $sejour  = new CSejour();
        $venueAN = $this->getVenueAN($sender, $data);
        if ($venueAN) {
            $NDA    = CIdSante400::getMatch("CSejour", $sender->_tag_sejour, $venueAN);
            $sejour = $NDA->loadTargetObject();
            if (!$sejour->_id) {
                return $exchange_hl7v2->setAckAR($ack, "E1003", null, $patient);
            }

            if ($sejour->patient_id !== $patient->_id) {
                return $exchange_hl7v2->setAckAR($ack, "E1004", null, $patient);
            }
        }

        // Gestion du rendez-vous
        $SCH                         = CMbArray::get($data, "SCH");
        $own_consultation_identifier = $sender_consultation_identifier = null;
        $EI_1                        = $this->queryTextNode("SCH.2/EI.1", $SCH);
        $EI_2                        = $this->queryTextNode("SCH.2/EI.2", $SCH);
        $EI_3                        = $this->queryTextNode("SCH.2/EI.3", $SCH);

        // Notre propre identifiant de consult
        if ($EI_2 == CAppUI::conf("hl7 CHL7 assigning_authority_namespace_id", "CGroups-$sender->group_id")
            || $EI_3 == CAppUI::conf("hl7 CHL7 assigning_authority_universal_id", "CGroups-$sender->group_id")
        ) {
            $own_consultation_identifier = $EI_1;
        }

        // L'identifiant de consult du sender
        if ($EI_3 == $sender->_configs["assigning_authority_universal_id"]
            || $EI_2 == $sender->_configs["assigning_authority_universal_id"]
        ) {
            $sender_consultation_identifier = $EI_1;
        }

        if (!$own_consultation_identifier && !$sender_consultation_identifier) {
            return $exchange_hl7v2->setAckAR($ack, "E1005", null, $patient);
        }

        // Notre propre ID de consult
        $appointment = new CConsultation();
        if ($own_consultation_identifier) {
            $appointment->load($own_consultation_identifier);
            $appointment->loadRefPlageConsult();
        }

        $idex = new CIdSante400();
        // ID de consult du partenaire
        if ($sender_consultation_identifier) {
            $tag_consultation =
                (CMbArray::get($sender->_configs, "handle_doctolib") && CModule::getActive("doctolib")) ?
                    CDoctolib::getObjectTag($sender->group_id) : $sender->_tag_consultation;
            $idex             = CIdSante400::getMatch(
                $appointment->_class,
                $tag_consultation,
                $sender_consultation_identifier
            );
        }
        // Chargement de la consultation par l'ID du tiers
        if (!$appointment->_id && $idex->_id) {
            $appointment->load($idex->object_id);
            $appointment->loadRefPlageConsult();
        }

        // Si on ne retrouve pas la consultation et que l'on n'est pas en création, alors on retourne une erreur
        if (!$appointment->_id && ($exchange_hl7v2->code != "S12")) {
            return $exchange_hl7v2->setAckAR($ack, "E1006", null, $patient);
        }

        if ($appointment->_id && ($appointment->patient_id !== $patient->_id)) {
            return $exchange_hl7v2->setAckAR($ack, "E1007", null, $patient);
        }

        if ($appointment->_id) {
            $modif_appointment = true;
        }

        // Mapping de la consultation
        $return_appointment = $this->mapAndStoreAppointment($data, $appointment, $patient);
        if (is_string($return_appointment)) {
            return $exchange_hl7v2->setAckAR($ack, "E1008", $return_appointment, $appointment);
        }

        // Idex de l'id de la consult du sender
        if ($sender_consultation_identifier) {
            CEAIMbObject::storeIdex($idex, $appointment, $sender);
        }

        $codes   = [$modif_appointment ? "I1002" : "I1001"];
        $comment = CEAIMbObject::getComment($appointment);

        // Segment TAMM-Galaxie
        if (CMbArray::get($data, "ZTG") && CModule::getActive("galaxie")) {
            $comment .= CGalaxie::mapAppointment($this, $data["ZTG"], $appointment, $sender->group_id);
        }

        return $exchange_hl7v2->setAckAA($ack, $codes, $comment, $appointment);
    }

    /**
     * Mapping and store appointment
     *
     * @param array         $data        Datas
     * @param CConsultation $appointment Appointment
     * @param CPatient      $patient     Patient
     *
     * @return CConsultation|string
     * @throws Exception
     */
    function mapAndStoreAppointment($data, CConsultation $appointment, CPatient $patient)
    {
        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $sender         = $this->_ref_sender;

        $praticien_id = null;
        $agenda       = new CAgendaPraticien();

        if (CModule::getActive("doctolib") && CMbArray::get($sender->_configs, "handle_doctolib")) {
            $return = CSenderHL7v2Doctolib::getAgenda($this, $data);
            // Error ?
            if (!$return instanceof CAgendaPraticien) {
                return $return;
            }
            $agenda       = $return;
            $praticien_id = $agenda->praticien_id;
        } else {
            $praticien_id = $this->getPlacerContactPerson($data["SCH"]);
        }

        if (!$praticien_id) {
            return CAppUI::tr("CHL7Event-E1009");
        }

        $duration = $this->getAppointmentDuration($data["SCH"]);
        $this->getFillerStatutsCode($data["SCH"], $appointment);

        $appointment->_no_synchro_eai = true;
        $dateTime                     = $this->queryTextNode("SCH.11/TQ.4/TS.1", $data["SCH"]);

        // On récupère la fonction
        $function_id = $this->getAppointmentResourceGroup($data["resources"]);
        try {
            // Si on n'a pas la consultation on va la créer
            if (!$appointment->_id) {
                // Création de la consultation
                $appointment->createByDatetime(
                    $dateTime,
                    $praticien_id,
                    $patient->_id,
                    $duration,
                    $appointment->chrono,
                    1,
                    null,
                    $agenda->_id,
                    $duration,
                    $function_id
                );
            } // Modification de la date/heure de la consult. ou de la durée
            else {
                if (($appointment->_datetime !== $dateTime) || $appointment->_duree !== $duration) {
                    $appointment->changeDateTime(
                        $dateTime,
                        $appointment->duree,
                        $appointment->chrono,
                        null,
                        $agenda->_id,
                        $duration,
                        $function_id
                    );
                }

                // Le praticien a changé
                if ($appointment->_praticien_id !== $praticien_id) {
                    $appointment->changePraticien($praticien_id, $agenda->_id, $function_id);
                }
            }
        } catch (CMbException $e) {
            return $e->getMessage();
        }

        if ($exchange_hl7v2->code == "S15" || $exchange_hl7v2->code == "S17") {
            $appointment->annule = 1;
        }

        if ($exchange_hl7v2->code == "S26") {
            $appointment->annule           = 1;
            $appointment->motif_annulation = "not_arrived";
        }

        $this->getReason($data["SCH"], $appointment);

        // Récupération des notes sur la consultation
        $this->secondaryMappingAppointment($data, $appointment);

        $appointment->_no_synchro_eai = true;
        if ($msg = $appointment->store()) {
            return $msg;
        }
    }

    /**
     * Return Placer Contact Person
     *
     * @param DOMNode $SCH SCH node
     *
     * @return String
     * @throws Exception
     */
    private function getPlacerContactPerson(DOMNode $SCH)
    {
        // Recherche par défaut dans SCH.12
        $XCNs = $this->queryNodes('SCH.12', $SCH);
        if (!$XCNs || $XCNs->length === 0) {
            // Recherche alternative dans le SCH.16
            $XCNs = $this->queryNodes('SCH.16', $SCH);
        }

        if (!$XCNs || $XCNs->length === 0) {
            return null;
        }

        return $this->getDoctor($XCNs, new CMediusers(), false);
    }

    /**
     * Get appointment duration
     *
     * @param DOMNode $node SCH Node
     *
     * @return string|null
     * @throws Exception
     */
    private function getAppointmentDuration(DOMNode $node): ?string
    {
        $duration = null;
        $unit     = "m";

        // Duration + Unit
        $SCH_11_3 = $this->queryTextNode("SCH.11/TQ.3", $node);
        if ($SCH_11_3) {
            $duration = $SCH_11_3;
            if (!is_numeric($SCH_11_3)) {
                $unit     = substr($SCH_11_3, 0, 1);
                $duration = substr($SCH_11_3, 1);
            }
        }

        // Duration
        if ($SCH_9 = $this->queryTextNode("SCH.9", $node)) {
            $duration = $SCH_9;
        }

        // No duration
        if (!$duration) {
            return null;
        }

        // Unit
        if ($SCH_10 = $this->queryTextNode("SCH.10/CE.1", $node)) {
            $unit = $SCH_10;
        }

        switch (strtolower($unit)) {
            case 's':
                $duration = $duration / 60;
                break;
            case 'h':
                $duration = $duration * 60;

                break;
            default:
        }

        return $duration;
    }

    /**
     * Get filler statuts code
     *
     * @param DOMNode       $node        SCH Node
     * @param CConsultation $appointment Appointment
     *
     * @return void
     * @throws Exception
     */
    private function getFillerStatutsCode(DOMNode $node, CConsultation $appointment): void
    {
        // Table - 0278
        // Pending   - Appointment has not yet been confirmed
        // Waitlist  - Appointment has been placed on a waiting list for a particular slot, or set of slots
        // Booked    - The indicated appointment is booked
        // Started   - The indicated appointment has begun and is currently in progress
        // Complete  - The indicated appointment has completed normally (was not discontinued, canceled, or deleted)
        // Cancelled - The indicated appointment was stopped from occurring (canceled prior to starting)
        // Dc        - The indicated appointment was discontinued (DC'ed while in progress, discontinued parent appointment,
        //             or discontinued child appointment)
        // Deleted   - The indicated appointment was deleted from the filler application
        // Blocked   - The indicated time slot(s) is(are) blocked
        // Overbook  - The appointment has been confirmed; however it is confirmed in an overbooked state
        // Noshow    - The patient did not show up for the appointment

        switch ($this->queryTextNode("SCH.25/CE.1", $node)) {
            case 'Started':
            case 'In progress':
                $appointment->annule = 0;
                $appointment->chrono = "48";

                return;

            case 'Waiting':
                $appointment->annule = 0;
                $appointment->chrono = "32";

                return;

            case 'Complete':
                $appointment->annule = 0;
                $appointment->chrono = "64";

                return;

            case 'Deleted':
            case 'Cancelled':
                $appointment->annule = 1;

                return;

            case 'Noshow':
                $appointment->annule           = 1;
                $appointment->motif_annulation = "not_arrived";

                return;

            default:
                $appointment->annule = 0;
                $appointment->chrono = "16";
        }
    }

    private function getAppointmentResourceGroup(array $resources): ?int
    {
        $resource = CMbArray::get($resources, 0);
        if (!CMbArray::get($resource, 'AIG')) {
            return null;
        }

        $function_id = null;
        foreach (CMbArray::get($resource, 'AIG') as $_AIG) {
            $identifier = $this->queryTextNode("AIG.5/CE.1", $_AIG);
            $text = CMbString::lower($this->queryTextNode("AIG.5/CE.2", $_AIG));
            $name_of_coding = CMbString::lower($this->queryTextNode("AIG.5/CE.3", $_AIG));

            if (!$identifier) {
                continue;
            }

            if ($text !== 'finess' && $name_of_coding !== 'finess') {
                continue;
            }

            $function         = new CFunctions();
            $function->finess = $identifier;
            $function->loadMatchingObjectEsc();

            if ($function->_id) {
                return $function->_id;
            }
        }

        return $function_id;
    }

    /**
     * Get appointment's reason
     *
     * @param DOMNode       $node        SCH Node
     * @param CConsultation $appointment Appointment
     *
     * @return void
     * @throws Exception
     */
    private function getReason(DOMNode $node, CConsultation $appointment): void
    {
        // Dans le cas de Doctolib on synchronise le motif de Doctolib uniquement sur la création (S12)
        if (
            CModule::getActive("doctolib") &&
            CMbArray::get($this->_ref_sender->_configs, "handle_doctolib") &&
            $this->_ref_exchange_hl7v2->code != "S12"
        ) {
            return;
        }
        if ($appointment_reason = $this->queryTextNode("SCH.7/CE.2", $node)) {
            if (strpos($appointment->motif, $appointment_reason) === false) {
                $appointment->motif .= (($appointment->motif) ? " \n" : null) . $appointment_reason;
            }
        }

        if (!$event_reason = $this->queryTextNode("SCH.6/CE.2", $node)) {
            return;
        }

        if (strpos($appointment->motif, $event_reason) === false) {
            $appointment->motif .= (($appointment->motif) ? " \n" : null) . $event_reason;
        }
    }

    /**
     * Secondary mapping appointment
     *
     * @param array         $data
     * @param CConsultation $appointment
     *
     * @return void
     * @throws Exception
     */
    private function secondaryMappingAppointment(array $data, CConsultation $appointment): void
    {
        // Récupération des notes
        if (array_key_exists("NTE", $data)) {
            $this->getAppointmentNotes($data["NTE"], $appointment);
        }
    }

    /**
     * Get notes
     *
     * @param array         $nodes       NTE nodes
     * @param CConsultation $appointment Appointment
     *
     * @return void
     * @throws Exception
     */
    private function getAppointmentNotes(array $NTEs, CConsultation $appointment): void
    {
        foreach ($NTEs as $_NTE) {
            $comment = $this->queryTextNode("NTE.3", $_NTE);
            if (!$comment) {
                continue;
            }
            $comment = str_replace("\\.br\\", "\r\n", $comment);

            $note_type = $this->queryTextNode("NTE.4/CE.1", $_NTE);
            switch ($note_type) {
                // Information du patient
                case 'PI':
                    if (strpos($appointment->rques, $comment) === false) {
                        $appointment->rques .= (($appointment->rques) ? " \n" : null) .
                            'Information du patient : ' . $comment;
                    }
                    break;

                // Motif de la consultation
                case '1R':
                    if (strpos($appointment->motif, $comment) === false) {
                        $appointment->motif .= (($appointment->motif) ? " \n" : null) . $comment;
                    }
                    break;

                // Remarque
                case 'RE':
                    if (strpos($appointment->rques, $comment) === false) {
                        $appointment->rques .= (($appointment->rques) ? " \n" : null) . $comment;
                    }
                    break;

                // Histoire de la maladie
                case 'HD':
                    if (strpos($appointment->histoire_maladie, $comment) === false) {
                        $appointment->histoire_maladie .= (($appointment->histoire_maladie) ? " \n" : null) . $comment;
                    }
                    break;

                // Examen clinique
                case 'CE':
                    if (strpos($appointment->examen, $comment) === false) {
                        $appointment->examen .= (($appointment->examen) ? " \n" : null) . $comment;
                    }
                    break;

                // Au total
                case 'GR':
                    if (strpos($appointment->conclusion, $comment) === false) {
                        $appointment->conclusion .= (($appointment->conclusion) ? " \n" : null) . $comment;
                    }
                    break;

                default:
                    if (strpos($appointment->rques, $comment) === false) {
                        $appointment->rques .= (($appointment->rques) ? " \n" : null) . $comment;
                    }
            }
        }
    }
}
