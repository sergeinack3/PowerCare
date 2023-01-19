<?php

/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\V2\Handle;

use Ox\AppFine\Server\CAppFineServer;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\CEAIPatient;
use Ox\Interop\Hl7\CHL7Acknowledgment;
use Ox\Interop\Hl7\CHL7v2MessageXML;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\System\CMergeLog;
use Throwable;

/**
 * Class MergePersons
 * Merge persons, message XML HL7
 */
class MergePersons extends CHL7v2MessageXML
{
    static $event_codes = ["A40"];

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

        foreach ($this->queryNodes("ADT_A40.PATIENT") as $_patient_group) {
            $sub_data["PID"] = $PID = $this->queryNode("PID", $_patient_group);

            $sub_data["personIdentifiers"] = $this->getPersonIdentifiers("PID.3", $PID, $sender);

            $sub_data["PD1"] = $this->queryNode("PD1", $_patient_group);

            $sub_data["MRG"] = $MRG = $this->queryNode("MRG", $_patient_group);

            $sub_data["personElimineIdentifiers"] = $this->getPersonIdentifiers("MRG.1", $MRG, $sender);

            $data["merge"][] = $sub_data;
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
        // Traitement du message des erreurs
        $comment = $warning = "";

        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $exchange_hl7v2->_ref_sender->loadConfigValues();
        $sender = $exchange_hl7v2->_ref_sender;

        if (CModule::getActive('appFine') && CMbArray::get($sender->_configs, "handle_portail_patient")) {
            return CAppFineServer::handleMergePatient($ack, $data, $sender, $exchange_hl7v2);
        }

        foreach ($data["merge"] as $_data_merge) {
            $data = $_data_merge;

            $mbPatient        = new CPatient();
            $mbPatientElimine = new CPatient();

            $patientPI = CValue::read($data['personIdentifiers'], "PI");
            $patientRI = CValue::read($data['personIdentifiers'], "RI");

            $patientEliminePI = CValue::read($data['personElimineIdentifiers'], "PI");
            $patientElimineRI = CValue::read($data['personElimineIdentifiers'], "RI");

            // Acquittement d'erreur : identifiants RI et PI non fournis
            if (!$patientRI && !$patientPI || !$patientElimineRI && !$patientEliminePI) {
                return $exchange_hl7v2->setAckAR($ack, "E100", null, $newPatient);
            }

            $idexPatient = CIdSante400::getMatch("CPatient", $sender->_tag_patient, $patientPI);
            if ($mbPatient->load($patientRI)) {
                if ($idexPatient->object_id && $mbPatient->_id != $idexPatient->object_id) {
                    $comment = "L'identifiant source fait référence au patient : $idexPatient->object_id";
                    $comment .= " et l'identifiant cible au patient : $mbPatient->_id.";

                    return $exchange_hl7v2->setAckAR($ack, "E130", $comment, $newPatient);
                }
            }
            if (!$mbPatient->_id) {
                $mbPatient->load($idexPatient->object_id);
            }

            $idexPatientElimine = CIdSante400::getMatch("CPatient", $sender->_tag_patient, $patientEliminePI);
            if ($mbPatientElimine->load($patientElimineRI)) {
                if ($idexPatientElimine->object_id && $mbPatientElimine->_id != $idexPatientElimine->object_id) {
                    $comment = "L'identifiant source fait référence au patient : $idexPatientElimine->object_id";
                    $comment .= "et l'identifiant cible au patient : $mbPatientElimine->_id.";

                    return $exchange_hl7v2->setAckAR($ack, "E131", $comment, $newPatient);
                }
            }
            if (!$mbPatientElimine->_id) {
                $mbPatientElimine->load($idexPatientElimine->object_id);
            }

            if (!$mbPatient->_id || !$mbPatientElimine->_id) {
                $comment = !$mbPatient->_id ?
                    "Le patient $mbPatient->_id est inconnu dans Mediboard." : "Le patient $mbPatientElimine->_id est inconnu dans Mediboard.";

                return $exchange_hl7v2->setAckAR($ack, "E120", $comment, $newPatient);
            }

            // Passage en trash de l'IPP du patient a éliminer
            $newPatient->trashIPP($idexPatientElimine);

            if ($mbPatient->_id == $mbPatientElimine->_id) {
                return $exchange_hl7v2->setAckAA($ack, "I104", null, $newPatient);
            }

            $patientsElimine_array = [$mbPatientElimine];
            $first_patient_id      = $mbPatient->_id;

            try {
                $mbPatient->checkMerge($patientsElimine_array);
            } catch (Throwable $t) {
                $comment = "La fusion de ces deux patients n'est pas possible à cause des problèmes suivants : {$t->getMessage()}";

                return $exchange_hl7v2->setAckAR($ack, "E121", $comment, $newPatient);
            }

            $mbPatientElimine_id = $mbPatientElimine->_id;

            /** @todo mergePlainFields resets the _id */
            $mbPatient->_id = $first_patient_id;

            // Notifier les autres destinataires
            $mbPatient->_eai_sender_guid = $sender->_guid;

            $merge_log = CMergeLog::logStart(CUser::get()->_id, $mbPatient, $patientsElimine_array, false);
            $merge_log->logCheck();

            try {
                $mbPatient->merge($patientsElimine_array, false, $merge_log);
                $merge_log->logEnd();
            } catch (Throwable $t) {
                $merge_log->logFromThrowable($t);

                return $exchange_hl7v2->setAckAR($ack, "E103", $t->getMessage(), $mbPatient);
            }

            $mbPatient->_mbPatientElimine_id = $mbPatientElimine_id;

            $comment = CEAIPatient::getComment($mbPatient, $mbPatientElimine);
        }

        return $exchange_hl7v2->setAckAA($ack, "I103", $comment, $mbPatient);
    }
}
