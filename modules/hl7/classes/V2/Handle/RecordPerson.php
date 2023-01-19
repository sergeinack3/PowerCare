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
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\CEAIPatient;
use Ox\Interop\Hl7\CHL7Acknowledgment;
use Ox\Interop\Hl7\CHL7v2MessageXML;
use Ox\Interop\Hl7\CHL7v2TableEntry;
use Ox\Mediboard\Doctolib\CSenderHL7v2Doctolib;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CCorrespondant;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CIdentityProofType;
use Ox\Mediboard\Patients\CINSPatient;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientINSNIR;
use Ox\Mediboard\Patients\CPaysInsee;
use Ox\Mediboard\Patients\CSourceIdentite;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class RecordPerson
 * Record person, message XML HL7
 */
class RecordPerson extends CHL7v2MessageXML
{
    /** @var string */
    public static $event_codes = ["A28", "A29", "A31"];

    /** @var bool */
    private $unknown_patient = false;

    /**
     * Get data nodes
     *
     * @return array Get nodes
     */
    public function getContentNodes()
    {
        $data = parent::getContentNodes();

        $this->queryNodes("NK1", null, $data, true);

        $this->queryNodes("ROL", null, $data, true);

        $this->queryNodes("OBX", null, $data, true);

        if ($this->_is_i18n == "FRA") {
            $this->queryNode("ZFD", null, $data, true);
        }

        $root_element_name = $this->documentElement->nodeName;
        $insurances = $this->queryNodes("$root_element_name.INSURANCE", null, $varnull, true);
        foreach ($insurances as $_insurance) {
            $tmp = [];

            // IN1
            $this->queryNodes("IN1", $_insurance, $tmp, true);

            $data["insurances"][] = $tmp;
        }

        return $data;
    }

    /**
     * Handle event
     *
     * @param CHL7Acknowledgment $ack Acknowledgement
     * @param CMbObject $newPatient Person
     * @param array $data Nodes data
     *
     * @return null|string
     */
    public function handle(CHL7Acknowledgment $ack = null, CMbObject $newPatient = null, $data = [])
    {
        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $sender = $exchange_hl7v2->_ref_sender;
        $sender->loadConfigValues();

        $this->_ref_sender = $sender;

        // Quand on reçoit d'AppFine, on n'intègre pas les modifications
        if (CModule::getActive('appFineClient') && (CMbArray::get($sender->_configs, "handle_portail_patient"))) {
            return CAppFineClient::storeMessageHL7v2($ack, $exchange_hl7v2, $data, $newPatient);
        }

        // Traitement spécifique pour Doctolib
        if (CMbArray::get($sender->_configs, "handle_doctolib") && CModule::getActive("doctolib")) {
            CSenderHL7v2Doctolib::storeLastEvent($exchange_hl7v2);
        }

        // Acquittement d'erreur : identifiants RI et PI non fournis
        if (!$data['personIdentifiers']) {
            return $exchange_hl7v2->setAckAR($ack, "E100", null, $newPatient);
        }

        switch ($exchange_hl7v2->code) {
            // A29 - Delete person information
            case "A29":
                $eventCode = "A29";
                break;

            // All events
            default:
                $eventCode = "All";
        }

        $function_handle = "handle$eventCode";
        if (!method_exists($this, $function_handle)) {
            return $exchange_hl7v2->setAckAR($ack, "E006", null, $newPatient);
        }

        return $this->$function_handle($ack, $newPatient, $data);
    }

    /**
     * Get PD1 segment
     *
     * @param DOMNode $node Node
     * @param CPatient $newPatient Person
     *
     * @return void
     */
    public function getPD1(DOMNode $node, CPatient $newPatient)
    {
        // VIP ?
        $newPatient->vip = ($this->queryTextNode("PD1.12", $node) === "Y") ? 1 : 0;
    }

    /**
     * Récupération du segment ZFD
     *
     * @param DOMNode $node Node
     * @param CPatient $newPatient Patient
     *
     * @return void
     * @throws Exception
     */
    public function getZFD(DOMNode $node, CPatient $newPatient)
    {
        // Date lunaire
        $jour_lunaire = $this->queryTextNode("ZFD.1/NA.1", $node);
        $mois_lunaire = $this->queryTextNode("ZFD.1/NA.2", $node);
        $annee_lunaire = $this->queryTextNode("ZFD.1/NA.3", $node);

        if ($jour_lunaire && $mois_lunaire && $annee_lunaire) {
            $jour_lunaire = str_pad($jour_lunaire, 2, 0, STR_PAD_LEFT);
            $mois_lunaire = str_pad($mois_lunaire, 2, 0, STR_PAD_LEFT);
            $newPatient->naissance = "$annee_lunaire-$mois_lunaire-$jour_lunaire";
        }

        // Consentement SMS
        switch ($this->queryTextNode("ZFD.3", $node)) {
            case 'Y':
                $newPatient->allow_sms_notification = 1;
                break;
            case 'N':
                $newPatient->allow_sms_notification = 0;
                break;
            default:
        }
    }

    /**
     * Handle all ITI-30 events
     *
     * @param CHL7Acknowledgment $ack Acknowledgement
     * @param CPatient $newPatient Person
     * @param array $data Nodes data
     *
     * @return null|string
     */
    private function handleAll(CHL7Acknowledgment $ack, CPatient $newPatient, $data)
    {
        // Traitement du message des erreurs
        $_modif_patient = false;

        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $sender = $this->_ref_sender;

        $patientRI = CValue::read($data['personIdentifiers'], "RI");
        $patientRISender = CValue::read($data['personIdentifiers'], "RI_Sender");
        $patientPI = CValue::read($data['personIdentifiers'], "PI");
        $patientPIOther = CValue::read($data['personIdentifiers'], "PI_Others");
        $patientRIOther = CValue::read($data['personIdentifiers'], "RI_Others");

        $IPP = new CIdSante400();

        if ($patientPI) {
            $IPP = CIdSante400::getMatch("CPatient", $sender->_tag_patient, $patientPI);
        }

        // Dans le cas d'une modification et que l'IPP reçu n'est pas connu, que l'on est numéroteur alors on va ignorer
        // le message
        if (
            $sender->_configs["ignore_the_patient_with_an_unauthorized_IPP"] && $exchange_hl7v2->code === 'A31' && $patientPI &&
            !$IPP->_id && $sender->_id
        ) {
            $group = new CGroups();
            $group->load($sender->group_id);

            if ($group->isIPPSupplier()) {
                return $exchange_hl7v2->setAckAR($ack, "E160", null, $newPatient);
            }
        }

        // PI non connu (non fourni ou non retrouvé)
        if (!$patientPI || !$IPP->_id) {
            // RI fourni
            if ($patientRI) {
                // Recherche du patient par son RI
                if ($newPatient->load($patientRI)) {
                    $recoveredPatient = clone $newPatient;

                    if (CAppUI::isGroup() && ($newPatient->group_id && ($newPatient->group_id !== $sender->group_id))) {
                        return $exchange_hl7v2->setAckAR($ack, "E152", null, $newPatient);
                    }

                    // Mapping primaire du patient
                    $this->primaryMappingPatient($data, $newPatient);

                    // Le patient retrouvé est-il différent que celui du message ?
                    if ($sender->_configs["check_similar_patient"] && !$this->checkSimilarPatient(
                            $recoveredPatient,
                            $newPatient
                        )) {
                        $commentaire = "Le nom ($newPatient->nom / $recoveredPatient->nom) " .
                            "et/ou le prénom ($newPatient->prenom / $recoveredPatient->prenom) sont très différents.";

                        return $exchange_hl7v2->setAckAR($ack, "E123", $commentaire, $newPatient);
                    }

                    // On store le patient
                    if ($msgPatient = CEAIPatient::storePatient($newPatient, $sender, false, $data)) {
                        return $exchange_hl7v2->setAckAR($ack, "E101", $msgPatient, $newPatient);
                    }

                    $code_IPP = "I121";
                    $_modif_patient = true;
                } // Patient non retrouvé par son RI
                else {
                    $code_IPP = "I120";
                }
            } else {
                $this->unknown_patient = true;

                // Aucun IPP fourni
                if (!$patientPI) {
                    $code_IPP = "I125";
                } // Association de l'IPP
                else {
                    $code_IPP = "I122";
                }
                if (CModule::getActive('appFine') && (CMbArray::get($sender->_configs, "handle_portail_patient"))) {
                    $return = CAppFineServer::getPatientRISender($sender, $patientRISender, $data, $newPatient);

                    if (is_string($return)) {
                        return $exchange_hl7v2->setAckAR($ack, "E123", $return, $newPatient);
                    } elseif (is_numeric($return)) {
                        return $exchange_hl7v2->setAckAR($ack, "E$return", null, $newPatient);
                    }

                    $code_IPP = "I127";
                }
            }

            if (!$newPatient->_id) {
                // Mapping primaire du patient
                $this->primaryMappingPatient($data, $newPatient);

                $duplicate_patient = false;
                // Réception de doublons exacts avec IPP différents
                if ($sender->_configs["create_duplicate_patient"]) {
                    $clone_patient = new CPatient();
                    $clone_patient->cloneFrom($newPatient);
                    if (CMbArray::get($sender->_configs, 'search_patient')) {
                        $clone_patient->loadMatchingPatient(false, true, [], false, $sender->group_id);
                        $clone_patient->loadIPP($sender->group_id);
                    }

                    // Patient retrouvé mais IPP différent
                    if ($clone_patient->_id && $clone_patient->_IPP && ($patientPI != $clone_patient->_IPP)) {
                        $code_IPP = "I126";
                        $duplicate_patient = true;
                    }
                }

                if ($duplicate_patient === false) {
                    // Patient retrouvé
                    if (
                        CMbArray::get($sender->_configs, 'search_patient')
                        && $newPatient->loadMatchingPatient(false, true, [], false, $sender->group_id)
                    ) {
                        // Mapping primaire du patient
                        $this->primaryMappingPatient($data, $newPatient);

                        $code_IPP = "A121";
                        $_modif_patient = true;
                    }
                }

                // On store le patient
                $newPatient->_IPP = $IPP->id400;
                if ($msgPatient = CEAIPatient::storePatient($newPatient, $sender, false, $data)) {
                    return $exchange_hl7v2->setAckAR($ack, "E101", $msgPatient, $newPatient);
                }
            }

            $newPatient->_generate_IPP = false;
            // Mapping secondaire (correspondants, médecins) du patient
            if ($msgPatient = $this->secondaryMappingPatient($data, $newPatient)) {
                return $exchange_hl7v2->setAckAR($ack, "E101", $msgPatient, $newPatient);
            }

            if ($msgIPP = CEAIPatient::storeIPP($IPP, $newPatient, $sender)) {
                return $exchange_hl7v2->setAckAR($ack, "E102", $msgIPP, $newPatient);
            }

            if (CModule::getActive("appFine")) {
                if ($msg = CAppFineServer::createUser($newPatient, $sender, $data)) {
                    return $exchange_hl7v2->setAckAR($ack, "E106", $msg, $newPatient);
                }
            }

            $codes = [($_modif_patient ? "I102" : "I101"), $code_IPP];

            $comment = CEAIPatient::getComment($newPatient);
            $comment .= CEAIPatient::getComment($IPP);
        } // PI connu
        else {
            $newPatient->load($IPP->object_id);

            $recoveredPatient = clone $newPatient;

            // Mapping primaire du patient
            $this->primaryMappingPatient($data, $newPatient);

            // Le patient retrouvé est-il différent que celui du message ?
            if ($sender->_configs["check_similar_patient"] && !$this->checkSimilarPatient(
                    $recoveredPatient,
                    $newPatient
                )) {
                $commentaire = "Le nom ($newPatient->nom / $recoveredPatient->nom) " .
                    "et/ou le prénom ($newPatient->prenom / $recoveredPatient->prenom) sont très différents.";

                return $exchange_hl7v2->setAckAR($ack, "E124", $commentaire, $newPatient);
            }

            // RI non fourni
            if (!$patientRI) {
                $code_IPP = "I123";
            } else {
                $tmpPatient = new CPatient();
                // RI connu
                if ($tmpPatient->load($patientRI)) {
                    if ($tmpPatient->_id != $IPP->object_id) {
                        $comment = "L'identifiant source fait référence au patient : $IPP->object_id" .
                            "et l'identifiant cible au patient : $tmpPatient->_id.";

                        return $exchange_hl7v2->setAckAR($ack, "E101", $comment, $newPatient);
                    }
                    $code_IPP = "I124";
                } // RI non connu
                else {
                    $code_IPP = "A120";
                }
            }

            // On store le patient
            if ($msgPatient = CEAIPatient::storePatient($newPatient, $sender, false, $data)) {
                return $exchange_hl7v2->setAckAR($ack, "E101", $msgPatient, $newPatient);
            }

            // Mapping secondaire (correspondants, médecins) du patient
            if ($msgPatient = $this->secondaryMappingPatient($data, $newPatient)) {
                return $exchange_hl7v2->setAckAR($ack, "E101", $msgPatient, $newPatient);
            }

            if (CModule::getActive("appFine")) {
                if ($msg = CAppFineServer::createUser($newPatient, $sender, $data)) {
                    return $exchange_hl7v2->setAckAR($ack, "E106", $msg, $newPatient);
                }
            }

            $codes = ["I102", $code_IPP];

            $comment = CEAIPatient::getComment($newPatient);
        }

        if ($patientRISender) {
            CEAIPatient::storeRISender($patientRISender, $newPatient, $sender);
        }

        if ($patientPIOther) {
            CEAIPatient::storeOtherIdentifiers($patientPIOther, $newPatient);
        }

        if ($patientRIOther) {
            CEAIPatient::storeOtherIdentifiers($patientRIOther, $newPatient);
        }

        // Gestion INS-C, INS-NIR et INS-NIA
        if ($sender->_configs["ins_integrated"]) {
            $this->mapAndStoreINS($data, $newPatient);
        }

        if ($msgPatient = CEAIPatient::storePatient($newPatient, $sender, false, $data)) {
            return $exchange_hl7v2->setAckAR($ack, "E101", $msgPatient, $newPatient);
        }

        return $exchange_hl7v2->setAckAA($ack, $codes, $comment, $newPatient);
    }

    /**
     * Primary mapping person
     *
     * @param array $data Datas
     * @param CPatient $newPatient Person
     *
     * @return void
     */
    public function primaryMappingPatient($data, CPatient $newPatient)
    {
        $sender = $this->_ref_sender;

        // Segment PID
        $this->getPID($data["PID"], $newPatient, $data);

        // Segment PD1
        $this->getSegment("PD1", $data, $newPatient);

        if (CMbArray::get($data, 'ZFD')) {
            // Type de justif et mode d'obtention
            $this->getModeObtentionIdentite($data['ZFD'], $newPatient);
        }

        $this->mapSourceIdentite($newPatient);

        // Dans le cas où l'on sépare les patients par établissement
        if (CAppUI::isGroup()) {
            $newPatient->group_id = $sender->group_id;
        }
    }

    /**
     * Get PID segment
     *
     * @param DOMNode $node Node
     * @param CPatient $newPatient Person
     * @param array $data Datas
     *
     * @return void
     */
    public function getPID(DOMNode $node, CPatient $newPatient, $data = null)
    {
        $PID5 = $this->query("PID.5", $node);
        foreach ($PID5 as $_PID5) {
            // Nom(s)
            $this->getNames($_PID5, $newPatient, $PID5);

            // Prenom(s)
            $this->getFirstNames($_PID5, $newPatient);
        }

        // Date de naissance
        $PID_7 = $this->queryTextNode("PID.7/TS.1", $node);
        $newPatient->naissance = $PID_7 ? CMbDT::date($PID_7) : null;

        // Cas d'un patient anonyme
        if ($newPatient->naissance && !$newPatient->prenom) {
            $newPatient->prenom = CValue::read($data['personIdentifiers'], "PI");
        }

        // Sexe
        $newPatient->sexe = CHL7v2TableEntry::mapFrom("1", $this->queryTextNode("PID.8", $node));

        // Civilité : si le patient change de sexe on va redéfinir la civilité
        $newPatient->civilite = $newPatient->fieldModified('sexe') ? 'guess' : ($newPatient->civilite ?: 'guess');
        /* @todo Voir comment faire ! Nouvelle table HL7 ? */
        //$newPatient->civilite = $this->queryTextNode("XPN.5", $_PID5);

        // Adresse(s)
        $this->getAdresses($node, $newPatient);

        // Téléphones
        $this->getPhones($node, $newPatient);

        // E-mail
        $this->getEmail($node, $newPatient);

        // Situation famille
        $this->getMaritalStatus($node, $newPatient);

        // Rang naissance
        $this->getRangNaissance($node, $newPatient);

        // Décès
        $this->getDeces($node, $newPatient);

        // NSS
        $this->getNSS($node, $newPatient, $data);

        $sender = $this->_ref_sender;
        if (!$sender) {
            return;
        }

        // Statut du patient
        $this->getPatientState($node, $newPatient);
    }

    /**
     * Get adresses
     *
     * @param DOMNode $node Node
     * @param CPatient $newPatient Person
     *
     * @return void
     */
    public function getAdresses(DOMNode $node, CPatient $newPatient)
    {
        $PID11 = $this->query("PID.11", $node);
        $addresses = [];
        foreach ($PID11 as $_PID11) {
            $adress_type = $this->queryTextNode("XAD.7", $_PID11);

            /* @todo Ajouter la gestion des multi-lignes - SAD.2 */
            $addresses[$adress_type]["adresse"] = $this->queryTextNode("XAD.1", $_PID11);
            $addresses[$adress_type]["adresse_comp"] = $this->queryTextNode("XAD.2", $_PID11);
            $addresses[$adress_type]["ville"] = $this->queryTextNode("XAD.3", $_PID11);
            $addresses[$adress_type]["cp"] = $this->queryTextNode("XAD.5", $_PID11);
            $addresses[$adress_type]["pays_insee"] = $this->queryTextNode("XAD.6", $_PID11);
            $addresses[$adress_type]["county"] = $this->queryTextNode("XAD.9", $_PID11);
        }

        // Lieu de naissance
        if (array_key_exists("BDL", $addresses)) {
            $newPatient->lieu_naissance = CValue::read($addresses["BDL"], "ville");
            $newPatient->cp_naissance = CValue::read($addresses["BDL"], "cp");
            $newPatient->commune_naissance_insee = CValue::read($addresses["BDL"], "county");

            if ($alpha_3 = CValue::read($addresses["BDL"], "pays_insee")) {
                $pays = CPaysInsee::getPaysByAlpha($alpha_3);

                $newPatient->pays_naissance_insee = $pays->numerique;
                $newPatient->_pays_naissance = $pays->nom_fr;
            }

            unset($addresses["BDL"]);
        }

        // Adresse
        if (array_key_exists("H", $addresses)) {
            $this->getAdress($addresses["H"], $newPatient);
        } else {
            foreach ($addresses as $_address) {
                $this->getAdress($_address, $newPatient);
            }
        }
    }

    /**
     * Get first name
     *
     * @param string $adress Adress
     * @param CPatient $newPatient Person
     *
     * @return void
     */
    private function getAdress($adress, CPatient $newPatient)
    {
        $newPatient->adresse = $adress["adresse"];
        if ($adress["adresse_comp"]) {
            $newPatient->adresse .= $this->getCompAdress($adress["adresse_comp"]);
        }

        $newPatient->ville = $adress["ville"];
        $newPatient->cp = $adress["cp"];
        if ($adress["pays_insee"]) {
            $pays = CPaysInsee::getPaysByAlpha($adress["pays_insee"]);

            $newPatient->pays_insee = $pays->numerique;
            $newPatient->pays = $pays->nom_fr;
        }
    }

    /**
     * Get formatted adress
     *
     * @param string $adress Adress
     *
     * @return string
     */
    private function getCompAdress($adress)
    {
        return "\n" . str_replace("\\S\\", "\n", $adress);
    }

    /**
     * Get phones
     *
     * @param DOMNode $node Node
     * @param CPatient $newPatient Person
     *
     * @return void
     * @throws Exception
     */
    private function getPhones(DOMNode $node, CPatient $newPatient)
    {
        $sender = $this->_ref_sender;

        $PID13 = $this->query("PID.13", $node);
        foreach ($PID13 as $_PID13) {
            $tel_number = $this->queryTextNode("XTN.12", $_PID13);

            if (!$tel_number) {
                $tel_number = $this->queryTextNode("XTN.1", $_PID13);
            }

            $tel_number = $this->getPhone($tel_number);
            $XTN_3 = $this->queryTextNode("XTN.3", $_PID13);
            switch ($this->queryTextNode("XTN.2", $_PID13)) {
                case "PRN":
                    if ($XTN_3 == "PH") {
                        $newPatient->tel = $tel_number;
                    }

                    if ($XTN_3 == "CP") {
                        $newPatient->tel2 = $tel_number;
                    }
                    break;

                case "ORN":
                    if ($XTN_3 == "PH") {
                        $newPatient->tel_autre = $tel_number;
                    }

                    if ($XTN_3 == "CP") {
                        if (!$newPatient->tel2) {
                            $newPatient->tel2 = $tel_number;
                        } else {
                            $newPatient->tel_autre_mobile = $tel_number;
                        }
                    }
                    break;

                case "WPN":
                    if ($XTN_3 == "PH" || $XTN_3 == "CP") {
                        $newPatient->tel_pro = $tel_number;
                    }
                    break;

                default:
                    if (CMbArray::get($sender->_configs, "handle_doctolib") && CModule::getActive("doctolib")) {
                        if (preg_match('/^(\+33|0)(6|7)/', $tel_number)) {
                            $newPatient->tel2 = $tel_number;
                        } else {
                            $newPatient->tel = $tel_number;
                        }
                    }
            }
        }
    }

    /**
     * Get email
     *
     * @param DOMNode $node Node
     * @param CPatient $newPatient Person
     *
     * @return void
     * @throws Exception
     */
    private function getEmail(DOMNode $node, CPatient $newPatient)
    {
        $sender = $this->_ref_sender;
        $PID13 = $this->query("PID.13", $node);

        foreach ($PID13 as $_PID13) {
            $email = $this->queryTextNode("XTN.4", $_PID13);
            if (CMbArray::get($sender->_configs, "handle_doctolib") && CModule::getActive("doctolib") && $email) {
                $newPatient->email = $email;
            } else {
                if ($this->queryTextNode("XTN.2", $_PID13) != "NET") {
                    continue;
                }

                if ($this->queryTextNode("XTN.3", $_PID13) != "Internet") {
                    continue;
                }

                $newPatient->email = $email;
            }
        }
    }

    /**
     * Get marital status
     *
     * @param DOMNode $node Node
     * @param CPatient $newPatient Person
     *
     * @return void
     */
    private function getMaritalStatus(DOMNode $node, CPatient $newPatient)
    {
        if ($marital_status = $this->queryTextNode("PID.16", $node)) {
            $newPatient->situation_famille = $marital_status;
        }
    }

    /**
     * Get birth order
     *
     * @param DOMNode $node Node
     * @param CPatient $newPatient Person
     *
     * @return void
     */
    private function getRangNaissance(DOMNode $node, CPatient $newPatient)
    {
        if ($rang_naissance = $this->queryTextNode("PID.25", $node)) {
            $newPatient->rang_naissance = $rang_naissance;
        }
    }

    /**
     * Get patient death datetime
     *
     * @param DOMNode $node Node
     * @param CPatient $newPatient Person
     *
     * @return void
     */
    private function getDeces(DOMNode $node, CPatient $newPatient)
    {
        if ($deces = $this->queryTextNode("PID.29/TS.1", $node)) {
            $newPatient->deces = CMbDT::dateTime($deces);
        }
    }

    /**
     * Récupère le numéro de sécurité social du patient
     *
     * @param DOMNode $node PID
     * @param CPatient $patient Patient
     *
     * @return void
     */
    private function getNSS(DOMNode $node, CPatient $patient, array $data = []): void
    {
        $sender = $this->_ref_sender;
        if ($sender && $sender->_configs["handle_NSS"] == "PID_19") {
            $patient->matricule = $this->queryTextNode("PID.19", $node);
        } elseif ($matricule = CMbArray::get($data["personIdentifiers"], 'SS')) {
            $patient->matricule = $matricule;
        } elseif ($matricule = CMbArray::get($data["personIdentifiers"], 'NH')) {
            $patient->matricule = $matricule;
        }
    }

    /**
     * Get the patient state
     *
     * @param DOMNode $node Node
     * @param CPatient $newPatient Patient
     *
     * @return void
     */
    private function getPatientState(DOMNode $node, CPatient $newPatient)
    {
        $sender = $this->_ref_sender;

        // L'expéditeur peut forcer la création d'identités non qualifiés
        $unqualified_identity = CMbArray::get($sender->_configs, "unqualified_identity") ? "VIDE" : null;

        $states = $this->queryNodes("PID.32", $node);
        if (!$states || $states->length === 0) {
            if ($unqualified_identity && !$newPatient->status) {
                $newPatient->status = $unqualified_identity;
            }

            return;
        }

        foreach ($states as $_state) {
            $state = $this->queryTextNode(".", $_state);
            if (CModule::getActive('appFine')
                && (CMbArray::get($sender->_configs, "handle_portail_patient")
                    && $state != "SAppFine" && $state != "DOUA")) {
                continue;
            }

            if ($state == "CACH" && !CModule::getActive('appFine')) {
                $newPatient->vip = true;

                continue;
            }

            if ($state == "SAppFine") {
                $newPatient->_responsable_compte = true;

                continue;
            }

            if ($state == "DOUA") {
                // TODO : Gestion doublon Medipole
                //$newPatient->_force_duplicate = true;

                continue;
            }

            // VIDE|PROV|VALI|RECUP|QUAL
            if (in_array($state, $newPatient->_specs["status"]->_list)) {
                $newPatient->status = $state;
            }
        }

        // On force une identité VIDE
        if ($unqualified_identity) {
            $newPatient->status = $unqualified_identity;
        }

        $newPatient->_status_no_guess = true;
    }

    /**
     * Mode d'obtention et type de justificatif de l'identité
     *
     * @param DOMNode $node
     * @param CPatient $newPatient
     */
    private function getModeObtentionIdentite(DOMNode $node, CPatient $newPatient): void
    {
        // Indicateur de date de naissance corrigée
        if ($ZFD_4 = $this->queryTextNode("ZFD.4", $node)) {
            $newPatient->_source_naissance_corrigee = ($ZFD_4 === "Y") ? 1 : 0;
        }

        $newPatient->_mode_obtention = CSourceIdentite::MODE_OBTENTION_INTEROP;
        // Mode d'obtention de l'identité
        if ($ZFD_5 = $this->queryTextNode("ZFD.5", $node)) {
            $mode_obtention = CHL7v2TableEntry::mapFrom(
                "9003",
                $ZFD_5
            );

            $newPatient->_mode_obtention = $mode_obtention;
        }

        // Type de justificatif d'identité
        if ($ZFD_7 = $this->queryTextNode("ZFD.7", $node)) {
            $type_justificatif = CHL7v2TableEntry::mapFrom(
                "9004",
                $ZFD_7
            );

            $identityProofType = CIdentityProofType::get(
                $type_justificatif ?: $ZFD_7
            );

            if (!$identityProofType->_id) {
                $description = CHL7v2TableEntry::getDescription(
                    "9004",
                    $ZFD_7
                );
                $identityProofType->label = $description ?: $ZFD_7;
                $identityProofType->trust_level = CIdentityProofType::TRUST_LEVEL_LOW;
                $identityProofType->store();
            }

            $newPatient->_identity_proof_type_id = $identityProofType->_id;
        }

        // Dans le cas où le patient est VALI dans le message mais que l'on n'a pas le type de justificatif, on va
        // le notifier pour le forcer dans la fiche patient
        if (
            !$newPatient->_identity_proof_type_id &&
            ($newPatient->status === 'VALI' || $newPatient->status === 'QUAL' || $newPatient->status === 'RECUP')
        ) {
            // On va créer le type ABS_INTEROP
            $identityProofType = CIdentityProofType::get(
                CIdentityProofType::PROOF_IDENTITY_NONE_CODE
            );
            if (!$identityProofType->_id) {
                $identityProofType->trust_level = CIdentityProofType::TRUST_LEVEL_HIGH;
                $identityProofType->label = CAppUI::tr(
                    'CIdentityProofType-msg-No proof of identity in the message'
                );
                // On force à null pour en faire un type "générique"
                $identityProofType->group_id = "";
                $identityProofType->store();
            }

            $newPatient->_identity_proof_type_id = $identityProofType->_id;
        }

        // Date de fin de validité du document
        if ($ZFD_8 = $this->queryTextNode("ZFD.8", $node)) {
            $newPatient->_source__date_fin_validite = $ZFD_8;
        }
    }

    /**
     * Map source identite
     *
     *
     */
    private function mapSourceIdentite(CPatient $patient): void
    {
        foreach (CSourceIdentite::TRAITS_STRICTS_REFERENCE as $_source_field => $_patient_field) {
            $_source_field = "_source_{$_patient_field}";

            $patient->$_source_field = $patient->$_patient_field;
        }
    }

    /**
     * Check similar person
     *
     * @param CPatient $recoveredPatient Person recovered
     * @param CPatient $newPatient Person
     *
     * @return bool
     */
    private function checkSimilarPatient(CPatient $recoveredPatient, CPatient $newPatient)
    {
        return $recoveredPatient->checkSimilar($newPatient->nom, $newPatient->prenom, false);
    }

    /**
     * Secondary mapping person
     *
     * @param array $data Datas
     * @param CPatient $newPatient Person
     *
     * @return string
     */
    private function secondaryMappingPatient($data, CPatient $newPatient)
    {
        $sender = $this->_ref_sender;

        // Possible seulement dans le cas où le patient
        if (!$newPatient->_id) {
            return;
        }

        // Couverture
        if (array_key_exists("insurances", $data)) {
            foreach ($data["insurances"] as $_insurance) {
                if (array_key_exists("IN1", $_insurance)) {
                    foreach ($_insurance["IN1"] as $_IN1) {
                        $this->getIN1($_IN1, null, $newPatient);
                    }
                }
            }
        }

        // Correspondants médicaux
        if (array_key_exists("ROL", $data)) {
            foreach ($data["ROL"] as $_ROL) {
                $this->getROL($_ROL, $newPatient);
            }
        }

        // Correspondances
        // Possible ssi le patient est déjà enregistré
        if (array_key_exists("NK1", $data)) {
            foreach ($data["NK1"] as $_NK1) {
                $this->getNK1($_NK1, $newPatient);
            }
        }

        // Constantes du patient
        if (array_key_exists("OBX", $data)) {
            $count = 1;
            foreach ($data["OBX"] as $_OBX) {
                $this->getOBX($_OBX, $newPatient, $data, $sender, $count);
                $count++;
            }
        }

        // Complément démographique
        if (array_key_exists("ZFD", $data)) {
            $this->getSegment("ZFD", $data, $newPatient);
        }
    }

    /**
     * Get ROL segment
     *
     * @param DOMNode $node Node
     * @param CPatient $newPatient Person
     *
     * @return void
     */
    public function getROL(DOMNode $node, CPatient $newPatient)
    {
        $sender = $this->_ref_sender;

        $ROL_4 = $this->queryNodes("ROL.4", $node)->item(0);

        $action_code = $this->queryTextNode("ROL.2", $node);
        switch ($this->queryTextNode("ROL.3/CE.1", $node)) {
            // Médecin traitant
            case "ODRP":
                $newPatient->medecin_traitant = $action_code === "DE" ? "" : $this->getMedecin($ROL_4);
                break;
            case "RT":
                $correspondant = new CCorrespondant();
                $correspondant->patient_id = $newPatient->_id;
                $correspondant->medecin_id = $this->getMedecin($ROL_4);
                if (!$correspondant->loadMatchingObjectEsc()) {
                    // Notifier les autres destinataires autre que le sender
                    $correspondant->_eai_sender_guid = $sender->_guid;
                    $correspondant->store();
                } elseif ($action_code === "DE") {
                    // Notifier les autres destinataires autre que le sender
                    $correspondant->_eai_sender_guid = $sender->_guid;
                    $correspondant->delete();
                }
                break;

            default:
        }
    }

    /**
     * Get doctor
     *
     * @param DOMNode $node Node
     *
     * @return int
     */
    private function getMedecin(DOMNode $node)
    {
        $xcn1 = $this->queryTextNode("XCN.1", $node);
        $xcn2 = $this->queryTextNode("XCN.2/FN.1", $node);
        $xcn3 = $this->queryTextNode("XCN.3", $node);

        if (!$xcn1 && !$xcn2 && !$xcn3) {
            return null;
        }

        $medecin = new CMedecin();
        $medecin->actif = 1;

        $sender = $this->_ref_sender;
        switch ($this->queryTextNode("XCN.13", $node)) {
            case "RPPS":
                $medecin->rpps = $xcn1;
                $medecin->loadMatchingObjectEsc();
                break;

            case "ADELI":
                $medecin->adeli = $xcn1;
                $medecin->loadMatchingObjectEsc();
                break;

            case "RI":
                // Gestion de l'identifiant MB
                if ($this->queryTextNode("XCN.9/CX.4/HD.2", $node) == CAppUI::conf(
                        "hl7 CHL7 assigning_authority_universal_id",
                        "CGroups-$sender->group_id"
                    )
                ) {
                    $medecin->load($xcn1);
                }
                break;

            default:
        }

        // Si pas retrouvé par son identifiant
        if (!$medecin->_id) {
            if ($xcn3 == "") {
                $xcn3 = null;
            }

            $medecin = new CMedecin();
            $medecin->nom = $xcn2;
            $medecin->prenom = $xcn3;
            $medecin->loadMatchingObjectEsc();

            // Dans le cas où il n'est pas connu dans MB on le créé
            $medecin->store();
        }

        return $medecin->_id;
    }

    /**
     * Get NK1 segment
     *
     * @param DOMNode $node Node
     * @param CPatient $newPatient Person
     *
     * @return string
     */
    public function getNK1(DOMNode $node, CPatient $newPatient)
    {
        $sender = $this->_ref_sender;

        $NK1_2 = $this->queryNode("NK1.2", $node);
        $nom = $this->queryTextNode("XPN.1/FN.1", $NK1_2);
        $prenom = $this->queryTextNode("XPN.2", $NK1_2);

        if ($prenom == "") {
            $prenom = null;
        }

        $parente = $this->queryTextNode("NK1.3/CE.1", $node);
        $parente_autre = null;
        if ($parente == "OTH") {
            $parente_autre = $this->queryTextNode("NK1.3/CE.2", $node);
        }

        $NK1_4 = $this->queryNode("NK1.4", $node);
        $adresse = $this->queryTextNode("XAD.1/SAD.1", $NK1_4);
        $cp = $this->queryTextNode("XAD.5", $NK1_4);
        $ville = $this->queryTextNode("XAD.3", $NK1_4);
        $date_debut = $this->queryTextNode("NK1.8", $node);
        $date_fin = $this->queryTextNode("NK1.9", $node);

        $NK1_5 = $this->queryNodes("NK1.5", $node);

        $tel = $mobile = $email = null;
        foreach ($NK1_5 as $_NK1_5) {
            $tel_number = $this->queryTextNode("XTN.12", $_NK1_5);

            if (!$tel_number) {
                $tel_number = $this->queryTextNode("XTN.1", $_NK1_5);
            }

            $tel_number = $this->getPhone($tel_number);
            switch ($this->queryTextNode("XTN.2", $_NK1_5)) {
                case "PRN":
                    if ($this->queryTextNode("XTN.3", $_NK1_5) == "PH") {
                        $tel = $tel_number;
                    }

                    if ($this->queryTextNode("XTN.3", $_NK1_5) == "CP") {
                        $mobile = $tel_number;
                    }
                    break;

                case "ORN":
                    if ($this->queryTextNode("XTN.3", $_NK1_5) == "CP") {
                        $mobile = $tel_number;
                    }
                    break;

                case "NET":
                    if ($this->queryTextNode("XTN.3", $_NK1_5) == "Internet") {
                        $email = $this->queryTextNode("XTN.4", $_NK1_5);
                    }
                    break;

                default:
                    $tel = $tel_number;
            }
        }

        $relation = $this->queryTextNode("NK1.7/CE.1", $node);
        $relation_autre = null;
        if ($relation == "O") {
            $relation_autre = $this->queryTextNode("NK1.7/CE.2", $node);
        }

        if ($parente == "GRD") {
            $newPatient->tutelle = "tutelle";
        }

        $corres_patient = new CCorrespondantPatient();
        $corres_patient->patient_id = $newPatient->_id;
        $corres_patient->nom = $nom;
        $corres_patient->prenom = $prenom;
        $corres_patient->relation = CHL7v2TableEntry::mapFrom("131", $relation);
        $corres_patient->loadMatchingObjectEsc();

        $corres_patient->adresse = $adresse;
        $corres_patient->cp = $cp;
        $corres_patient->ville = $ville;
        $corres_patient->tel = $tel;
        $corres_patient->mob = $mobile;
        $corres_patient->parente = CHL7v2TableEntry::mapFrom("63", $parente);
        $corres_patient->parente_autre = $parente_autre;
        $corres_patient->relation_autre = $relation_autre;
        $corres_patient->email = $email;

        if ($date_debut) {
            $corres_patient->date_debut = CMbDT::date($date_debut);
        }

        if ($date_fin) {
            $corres_patient->date_fin = CMbDT::date($date_fin);
        }

        // Notifier les autres destinataires autre que le sender
        $corres_patient->_eai_sender_guid = $sender->_guid;

        if ($msg = $corres_patient->store()) {
            return $msg;
        }

        // AppFine
        if ($this->queryTextNode("NK1.39", $node) == "Y") {
            $newPatient->_correspond_responsable = $corres_patient;
        }
    }

    /**
     * Récupère les INS du patient et les enregistre
     *
     * @param array $data Datas
     * @param CPatient $patient Patient
     *
     * @return void
     */
    private function mapAndStoreINS(array $data, CPatient $patient): void
    {
        if (!$patient->_id) {
            return;
        }

        // Gestion INS-C
        if ($list_insc = CMbArray::get($data['personIdentifiers'], 'INS-C')) {
            foreach ($list_insc as $_insc) {
                $date = CMbArray::get($_insc, 'effective_date');

                $ins = new CINSPatient();
                $ins->patient_id = $patient->_id;
                $ins->type = substr(CMbArray::get($_insc, 'identifier_type_code'), -1);
                $ins->ins = CMbArray::get($_insc, 'id_number');
                $ins->loadMatchingObject();
                if ($date && $ins->date < $date) {
                    $ins->date = CMbDT::dateTime($date);
                    $ins->provider = $this->_ref_sender->nom;
                }

                $ins->store();
            }
        }

        // Gestion INS-NIR
        if ($list_ins_nir = CMbArray::get($data['personIdentifiers'], 'INS-NIR')) {
            foreach ($list_ins_nir as $_ins_nir) {
                $this->storeINS($_ins_nir, $patient);
            }
        }
    }

    /**
     * Store un INS (INS-NIR, INS-NIA) du patient
     *
     * @param array $data_ins Data INS
     * @param CPatient $patient Patient
     *
     * @return void
     */
    private function storeINS(array $data_ins, CPatient $patient): void
    {
        $universal_id = CMbArray::get($data_ins, 'universal_id');
        $id_number = CMbArray::get($data_ins, 'id_number');

        if (!$id_number && !$universal_id) {
            return;
        }

        $patient->_ins = $id_number;
        $patient->_ins_type = ($universal_id === CPatientINSNIR::OID_INS_NIA) ? 'NIA' : 'NIR';
        $patient->_oid = $universal_id;
        $patient->_mode_obtention = CSourceIdentite::MODE_OBTENTION_INSI;
        $patient->_force_new_insi_source = false;
        // Dans le cas d'un patient inconnu pour lequel on rajoute un INS, on va le rendre temporaire
        if ($this->unknown_patient) {
            $patient->_ins_temporaire = true;
        }
    }

    /**
     * Handle A29 event - Delete person information
     *
     * @param CHL7Acknowledgment $ack Acknowledgement
     * @param CPatient $newPatient Person
     * @param array $data Nodes data
     *
     * @return null|string
     */
    private function handleA29(CHL7Acknowledgment $ack, CPatient $newPatient, $data)
    {
        // Traitement du message des erreurs
        $comment = $warning = "";

        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $sender = $this->_ref_sender;

        $patientPI = CValue::read($data['personIdentifiers'], "PI");
        $IPP = new CIdSante400();
        if ($patientPI) {
            $IPP = CIdSante400::getMatch("CPatient", $sender->_tag_patient, $patientPI);
        }

        if (!$patientPI || !$IPP->_id) {
            return $exchange_hl7v2->setAckAR($ack, "E150", null, $newPatient);
        }

        $newPatient->load($IPP->object_id);

        // Passage en trash de l'IPP du patient
        if ($msg = $newPatient->trashIPP($IPP)) {
            return $exchange_hl7v2->setAckAR($ack, "E151", $msg, $newPatient);
        }

        // Annulation de tous les séjours du patient qui n'ont pas d'entrée réelle
        $where = [];
        $where['entree_reelle'] = "IS NULL";
        $where['group_id'] = " = '$sender->group_id'";

        $sejours = $newPatient->loadRefsSejours($where);

        foreach ($sejours as $_sejour) {
            // Notifier les autres destinataires autre que le sender
            $_sejour->_eai_sender_guid = $sender->_guid;
            // Pas de génération de NDA
            $_sejour->_generate_NDA = false;
            // On ne check pas la cohérence des dates des consults/intervs
            $_sejour->_skip_date_consistencies = true;

            // On annule le séjour
            $_sejour->annule = 1;
            $_sejour->store();
        }

        $codes = ["I150"];

        return $exchange_hl7v2->setAckAA($ack, $codes, $comment, $newPatient);
    }
}
