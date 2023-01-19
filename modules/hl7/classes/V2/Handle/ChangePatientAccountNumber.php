<?php

/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\V2\Handle;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Interop\Eai\CDomain;
use Ox\Interop\Hl7\CHL7Acknowledgment;
use Ox\Interop\Hl7\CHL7v2MessageXML;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class ChangePatientIdentifierList
 * Change patient account number, message XML HL7
 */
class ChangePatientAccountNumber extends CHL7v2MessageXML
{
    static $event_codes = ["A49"];

    /**
     * Get contents
     *
     * @return array
     * @throws Exception
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
     * @throws Exception
     */
    function handle(CHL7Acknowledgment $ack = null, CMbObject $patient = null, $data = [])
    {
        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $sender         = $exchange_hl7v2->_ref_sender;
        $sender->loadConfigValues();

        $this->_ref_sender = $sender;

        // Acquittement d'erreur : identifiants RI et PI non fournis
        if (!$data['personIdentifiers']) {
            return $exchange_hl7v2->setAckAR($ack, "E100", null, $patient);
        }

        $function_handle = "handle$exchange_hl7v2->code";
        if (!method_exists($this, $function_handle)) {
            return $exchange_hl7v2->setAckAR($ack, "E006", null, $patient);
        }

        return $this->$function_handle($ack, $patient, $data);
    }

    /**
     * Handle event A49
     *
     * @param CHL7Acknowledgment $ack     Acknowledgment
     * @param CPatient           $patient Person
     * @param array              $data    Data
     *
     * @return string
     * @throws Exception
     */
    function handleA49(CHL7Acknowledgment $ack, CPatient $patient, $data)
    {
        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $sender         = $exchange_hl7v2->_ref_sender;
        $sender->loadConfigValues();

        $this->_ref_sender = $sender;

        $PI = CMbArray::get($data["personIdentifiers"], "PI");
        if (!$PI) {
            return $exchange_hl7v2->setAckAR($ack, "E900", null, $patient);
        }

        $patient->_IPP = $PI;
        $patient->loadFromIPP($sender->group_id);
        if (!$patient->_id) {
            return $exchange_hl7v2->setAckAR($ack, "E901", null, $patient);
        }

        $venueAN = $this->getVenueAN($sender, $data);
        if (!$venueAN) {
            return $exchange_hl7v2->setAckAR($ack, "E902", null, $patient);
        }

        $error_codes       = [];
        $msg_store         = $msg_trash = null;
        $identifier_change = false;

        $sejour = new CSejour();
        foreach ($this->queryNodes('MRG.3', $data['MRG']) as $_MRG_1) {
            $id_number            = $this->queryTextNode('CX.1', $_MRG_1);
            $namespace_id         = $this->queryTextNode('CX.4/HD.1', $_MRG_1);
            $universal_id         = $this->queryTextNode('CX.4/HD.2', $_MRG_1);
            $identifier_type_code = $this->queryTextNode('CX.5', $_MRG_1);

            // NA - Account number
            // RI - Ressource identifier
            // On va attribuer un nouveau NDA au séjour et éventuellement passer l'autre en trash
            if ($identifier_type_code === 'NA' || $identifier_type_code === 'RI') {
                if ($identifier_type_code === 'NA') {
                    if (CValue::read($sender->_configs, "search_master_NDA")) {
                        $domain = CDomain::getMasterDomainSejour($sender->group_id);
                        if ($domain->namespace_id != $namespace_id) {
                            continue;
                        }
                    }

                    // Chargement du NDA
                    $NDA_incorrect = new CIdSante400();
                    if ($id_number) {
                        $NDA_incorrect = CIdSante400::getMatch($sejour->_class, $sender->_tag_sejour, $id_number);
                    }

                    // NA non connu (non fourni ou non retrouvé)
                    if (!$id_number || !$NDA_incorrect->_id) {
                        $error_codes[] = "E903";
                        continue;
                    }

                    $sejour->load($NDA_incorrect->object_id);
                } else {
                    if (!$id_number) {
                        $error_codes[] = "E905";
                        continue;
                    }
                    $guid = "CGroups-$sender->group_id";
                    if (
                        $namespace_id === CAppUI::conf('hl7 CHL7 assigning_authority_namespace_id', $guid)
                        || $universal_id === CAppUI::conf('hl7 CHL7 assigning_authority_universal_id', $guid)
                    ) {
                        $sejour->load($id_number);
                        if (!$sejour->_id) {
                            $error_codes[] = "E905";
                            continue;
                        }
                    } else {
                        $error_codes[] = "E905";
                        continue;
                    }
                }

                // Passage en trash du NDA du séjour a éliminer
                if ($msg_trash = $sejour->trashNDA()) {
                    $error_codes[] = "E904";
                    continue;
                }

                if ($sejour->patient_id != $patient->_id) {
                    return $exchange_hl7v2->setAckAR($ack, "E906", null, $sejour);
                }

                // Sauvegarde du nouveau NDA
                $NDA = CIdSante400::getMatch($sejour->_class, $sender->_tag_sejour, $venueAN, $sejour->_id);
                if ($msg_store = $NDA->store()) {
                    $error_codes[] = "E907";
                    continue;
                }

                $identifier_change = true;
            }
        }

        if ($error_codes) {
            return $exchange_hl7v2->setAckAR(
                $ack,
                $error_codes,
                $msg_store || $msg_trash ? $msg_store . $msg_trash : null,
                $sejour
            );
        }

        if ($identifier_change === false) {
            return $exchange_hl7v2->setAckAE($ack, 'W900', null, $sejour);
        }

        return $exchange_hl7v2->setAckAA($ack, "I901", null, $sejour);
    }
}
