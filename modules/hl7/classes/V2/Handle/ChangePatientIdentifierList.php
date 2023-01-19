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
use Ox\Interop\Eai\CDomain;
use Ox\Interop\Hl7\CHL7Acknowledgment;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Interop\Hl7\CHL7v2MessageXML;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientINSNIR;
use Ox\Mediboard\Patients\CSourceIdentite;
use Ox\Mediboard\Patients\PatientStatus;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class ChangePatientIdentifierList
 * Change patient identifier list, message XML HL7
 */
class ChangePatientIdentifierList extends CHL7v2MessageXML
{
    static $event_codes = ["A46", "A47"];

    /**
     * Get contents
     *
     * @return array
     */
    function getContentNodes()
    {
        $data = parent::getContentNodes();

        $this->queryNode("MRG", null, $data, true);

        return $data;
    }

    /**
     * Handle change patient identifier list message
     *
     * @param CHL7Acknowledgment $ack     Acknowledgment
     * @param CMbObject          $patient Person
     * @param array              $data    Data
     *
     * @return string
     */
    function handle(CHL7Acknowledgment $ack = null, CMbObject $patient = null, $data = [])
    {
        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $sender         = $exchange_hl7v2->_ref_sender;
        $sender->loadConfigValues();

        $this->_ref_sender = $sender;

        // Acquittement d'erreur : identifiants RI et PI non fournis
        if (!['personIdentifiers']) {
            return $exchange_hl7v2->setAckAR($ack, "E100", null, $patient);
        }

        $function_handle = "handle$exchange_hl7v2->code";

        if (!method_exists($this, $function_handle)) {
            return $exchange_hl7v2->setAckAR($ack, "E006", null, $patient);
        }

        return $this->$function_handle($ack, $patient, $data);
    }

    /**
     * Handle event A46
     *
     * @param CHL7Acknowledgment $ack     Acknowledgment
     * @param CPatient           $patient Person
     * @param array              $data    Data
     *
     * @return string
     */
    function handleA46(CHL7Acknowledgment $ack, CPatient $patient, $data)
    {
        $handle_mode = CHL7v2Message::$handle_mode;

        CHL7v2Message::$handle_mode = "simple";

        $msg = $this->handleA47($ack, $patient, $data);

        CHL7v2Message::$handle_mode = $handle_mode;

        return $msg;
    }

    /**
     * Handle event A47
     *
     * @param CHL7Acknowledgment $ack     Acknowledgment
     * @param CPatient           $patient Person
     * @param array              $data    Data
     *
     * @return string
     */
    function handleA47(CHL7Acknowledgment $ack, CPatient $patient, $data)
    {
        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $sender         = $exchange_hl7v2->_ref_sender;
        $sender->loadConfigValues();

        $this->_ref_sender = $sender;

        if (CModule::getActive('appFine')) {
            if ($sender->_configs['handle_portail_patient']) {
                return CAppFineServer::handleA47($ack, $data, $sender, $exchange_hl7v2);
            }
        }

        $error_codes       = [];
        $msg_store         = $msg_trash = null;
        $identifier_change = false;

        foreach ($this->queryNodes('MRG.1', $data['MRG']) as $_MRG_1) {
            $id_number            = $this->queryTextNode('CX.1', $_MRG_1);
            $namespace_id         = $this->queryTextNode('CX.4/HD.1', $_MRG_1);
            $universal_id         = $this->queryTextNode('CX.4/HD.2', $_MRG_1);
            $identifier_type_code = $this->queryTextNode('CX.5', $_MRG_1);

            // PI - Patient internal identifier
            // RI - Ressource identifier
            // On va attribuer un nouvel IPP au patient et éventuellement passer l'autre en trash
            if ($identifier_type_code === 'PI' || $identifier_type_code === 'RI') {
                if ($identifier_type_code === 'PI') {
                    if (CValue::read($sender->_configs, "search_master_IPP")) {
                        $domain = CDomain::getMasterDomainPatient($sender->group_id);
                        if ($domain->namespace_id != $namespace_id) {
                            continue;
                        }
                    }

                    // Chargement de l'IPP
                    $IPP_incorrect = new CIdSante400();
                    if ($id_number) {
                        $IPP_incorrect = CIdSante400::getMatch($patient->_class, $sender->_tag_patient, $id_number);
                    }

                    // PI non connu (non fourni ou non retrouvé)
                    if (!$id_number || !$IPP_incorrect->_id) {
                        $error_codes[] = "E141";
                        continue;
                    }

                    $patient->load($IPP_incorrect->object_id);

                    // Passage en trash de l'IPP du patient a éliminer
                    if ($msg_trash = $patient->trashIPP($IPP_incorrect)) {
                        $error_codes[] = "E140";
                        continue;
                    }
                } else {
                    if (!$id_number) {
                        $error_codes[] = "E144";
                        continue;
                    }
                    $guid = "CGroups-$sender->group_id";
                    if (
                        $namespace_id === CAppUI::conf('hl7 CHL7 assigning_authority_namespace_id', $guid)
                        || $universal_id === CAppUI::conf('hl7 CHL7 assigning_authority_universal_id', $guid)
                    ) {
                        $patient->load($id_number);
                        if (!$patient->_id) {
                            $error_codes[] = "E144";
                            continue;
                        }
                    } else {
                        $error_codes[] = "E144";
                        continue;
                    }

                    // Passage en trash de l'IPP du patient a éliminer
                    $IPP_incorrect = CIdSante400::getMatch(
                        $patient->_class,
                        $sender->_tag_patient,
                        null,
                        $patient->_id
                    );
                    if ($msg_trash = $patient->trashIPP($IPP_incorrect)) {
                        $error_codes[] = "E140";
                        continue;
                    }
                }

                $patientPI = CValue::read($data['personIdentifiers'], "PI");
                if (!$patientPI) {
                    continue;
                }

                // Sauvegarde du nouvel IPP
                $IPP = CIdSante400::getMatch($patient->_class, $sender->_tag_patient, $patientPI, $patient->_id);
                if ($msg_store = $IPP->store()) {
                    $error_codes[] = "E142";
                    continue;
                }

                $identifier_change = true;
            }

            // INS-NIR - Identifiant National de Santé NIR
            if (
                $this->queryTextNode(
                    'CX.5',
                    $_MRG_1
                ) === 'INS'
                && ($universal_id === CPatientINSNIR::OID_INS_NIR || $universal_id === CPatientINSNIR::OID_INS_NIR_TEST)
            ) {
                if ($list_ins_nir = CMbArray::get($data['personIdentifiers'], 'INS-NIR')) {
                    foreach ($list_ins_nir as $_ins_nir) {
                        // Suppression de l'INS-NIR : PID.3 = "" / MRG.1 = INS-NIR
                        if (CMbArray::get($_ins_nir, 'id_number') == '""') {
                            // Chargement du patient par son INS-NIR - MRG.1
                            $patient_insnir           = new CPatientINSNIR();
                            $patient_insnir->ins_nir  = $id_number;
                            $patient_insnir->provider = 'INSi';
                            $patient_insnir->loadMatchingObject();

                            if ($patient_insnir->_id && $patient_insnir->source_identite_id) {
                                $patient = $patient_insnir->loadRefSourceIdentite()->loadRefPatient();
                                $patient->loadRefSourceIdentite();
                                $patient->loadRefsSourcesIdentite();

                                // On passe la source d'identité INSi inactive
                                PatientStatus::demotePatientStatus($patient);

                                $identifier_change = true;
                            }
                        } else {
                            // Modification si nouvel INS : PID.3 = nouvel INS-NIR
                            // Chargement du patient par son INS-NIR - MRG.1
                            $patient_insnir           = new CPatientINSNIR();
                            $patient_insnir->ins_nir  = $id_number;
                            $patient_insnir->provider = 'INSi';
                            $patient_insnir->loadMatchingObject();

                            if ($patient_insnir->_id && $patient_insnir->source_identite_id) {
                                // On vient créer la source d'identité insi
                                $patient                  = $patient_insnir->loadRefSourceIdentite()->loadRefPatient();
                                $patient->_ins            = CMbArray::get($_ins_nir, 'id_number');
                                $patient->_ins_type       = ($universal_id === CPatientINSNIR::OID_INS_NIA) ?
                                    'NIA' : 'NIR';
                                $patient->_oid            = $universal_id;
                                $patient->_mode_obtention = CSourceIdentite::MODE_OBTENTION_INSI;

                                if ($msg_store = $patient->store()) {
                                    $error_codes[] = "E140";
                                }

                                $identifier_change = true;
                            }
                        }
                    }
                }
            }
        }

        if ($error_codes) {
            return $exchange_hl7v2->setAckAR(
                $ack,
                $error_codes,
                $msg_store || $msg_trash ? $msg_store . $msg_trash : null,
                $patient
            );
        }

        if ($identifier_change === false) {
            return $exchange_hl7v2->setAckAE($ack, 'W140', null, $patient);
        }

        return $exchange_hl7v2->setAckAA($ack, "I140", null, $patient);
    }
}
