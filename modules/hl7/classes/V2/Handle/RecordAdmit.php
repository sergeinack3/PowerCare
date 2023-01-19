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
use Ox\AppFine\Server\CAppFineServer;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Erp\CabinetSIH\CCabinetSIHRecordData;
use Ox\Interop\Eai\CEAISejour;
use Ox\Interop\Hl7\CHL7Acknowledgment;
use Ox\Interop\Hl7\CHL7v2Acknowledgment;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2MessageXML;
use Ox\Interop\Hl7\CHL7v2TableEntry;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CEtabExterne;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CAffectationUniteFonctionnelle;
use Ox\Mediboard\Hospi\CChambre;
use Ox\Mediboard\Hospi\CItemLiaison;
use Ox\Mediboard\Hospi\CItemPrestation;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CMovement;
use Ox\Mediboard\Hospi\CPrestationJournaliere;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Maternite\CNaissance;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CCorrespondant;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\PlanningOp\CChargePriceIndicator;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\PlanningOp\CModeSortieSejour;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\Urgences\CRPU;

/**
 * Class RecordAdmit
 * Record admit, message XML HL7
 */
class RecordAdmit extends CHL7v2MessageXML
{
    static $event_codes = [
        "A01",
        "A02",
        "A03",
        "A04",
        "A05",
        "A06",
        "A07",
        "A08",
        "A09",
        "A10",
        "A11",
        "A12",
        "A13",
        "A14",
        "A15",
        "A16",
        "A21",
        "A22",
        "A25",
        "A26",
        "A27",
        "A32",
        "A33",
        "A38",
        "A52",
        "A53",
        "A54",
        "A55",
        "Z80",
        "Z81",
        "Z84",
        "Z85",
        "Z99",
    ];

    /** @var CConsultation|CRPU|CSejour */
    public $_object_found_by_vn;

    public $_doctor_id;

    /**
     * Get data nodes
     *
     * @return array Get nodes
     * @throws Exception
     */
    function getContentNodes()
    {
        $data = parent::getContentNodes();

        $sender = $this->_ref_sender;

        $this->queryNodes("NK1", null, $data, true);

        $this->queryNodes("ROL", null, $data, true);

        $PV1 = $this->queryNode("PV1", null, $data, true);

        $data["admitIdentifiers"] = $this->getAdmitIdentifiers($PV1, $sender);

        $this->queryNode("PV2", null, $data, true);

        // Traitement des segments spécifiques extension française PAM
        if ($this->_is_i18n == "FRA" || $sender->_configs["iti31_historic_movement"]) {
            $this->queryNode("ZBE", null, $data, true);
        }

        if ($this->_is_i18n == "FRA") {
            $this->queryNode("ZFP", null, $data, true);

            $this->queryNode("ZFV", null, $data, true);

            $this->queryNode("ZFM", null, $data, true);

            $this->queryNode("ZFD", null, $data, true);
        }

        $this->queryNodes("OBX", null, $data, true);

        $this->queryNodes("DRG", null, $data, true);

        $this->queryNodes("GT1", null, $data, true);

        $root_element_name = $this->documentElement->nodeName;
        $insurances        = $this->queryNodes("$root_element_name.INSURANCE", null, $varnull, true);
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
     * @param CHL7Acknowledgment $ack        Acknowledgement
     * @param CMbObject          $newPatient Person
     * @param array              $data       Nodes data
     *
     * @return null|string
     * @throws Exception
     */
    function handle(CHL7Acknowledgment $ack = null, CMbObject $newPatient = null, $data = [])
    {
        $event_temp = $ack->event;

        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $sender         = $exchange_hl7v2->_ref_sender;
        $sender->loadConfigValues();

        $configs = $sender->_configs;

        $this->_ref_sender = $sender;

        // Acquittement d'erreur : identifiants RI et NA, VN non fournis
        if (!$data['admitIdentifiers'] && !$this->getVenueAN($sender, $data)) {
            return $exchange_hl7v2->setAckAR($ack, "E200", null, $newPatient);
        }

        // Traitement du patient
        if (CMbArray::get($configs, "handle_patient_ITI_31")) {
            $hl7v2_record_person                      = new RecordPerson();
            $hl7v2_record_person->_ref_exchange_hl7v2 = $exchange_hl7v2;
            $msg_ack                                  = $hl7v2_record_person->handle($ack, $newPatient, $data);

            // Retour de l'acquittement si erreur sur le traitement du patient
            if ($exchange_hl7v2->statut_acquittement == "AR") {
                return $msg_ack;
            }
        } else {
            // AppFine
            if (CModule::getActive("appFine") && CMbArray::get($configs, "handle_portail_patient")
                && in_array($exchange_hl7v2->code, RecordAdmit::$event_codes)) {
                return CAppFineServer::handleEvenementSejour($ack, $data, $sender, $exchange_hl7v2);
            }

            // TAMM-SIH
            if (CModule::getActive("oxCabinetSIH") && CMbArray::get($configs, "handle_tamm_sih")
                && in_array($exchange_hl7v2->code, RecordAdmit::$event_codes)) {
                return CCabinetSIHRecordData::handleAdmit($ack, $data, $sender, $exchange_hl7v2);
            }

            // Patient
            $patientPI = CValue::read($data['personIdentifiers'], "PI");
            if (!$patientPI) {
                return $exchange_hl7v2->setAckAR($ack, "E007", null, $newPatient);
            }

            $IPP = CIdSante400::getMatch("CPatient", $sender->_tag_patient, $patientPI);
            // Patient non retrouvé par son IPP
            if (!$IPP->_id) {
                return $exchange_hl7v2->setAckAR($ack, "E105", null, $newPatient);
            }
            $newPatient->load($IPP->object_id);
        }

        // Traitement du séjour
        $ack                      = new CHL7v2Acknowledgment($event_temp);
        $ack->message_control_id  = $data['identifiantMessage'];
        $ack->_ref_exchange_hl7v2 = $exchange_hl7v2;

        $newVenue = new CSejour();

        // Ignorer le séjour selon des champs HL7
        if ($ignore_admit_with_field = CMbArray::get($configs, "ignore_admit_with_field")) {
            $ignored_fields = preg_split("/\s*,\s*/", $ignore_admit_with_field);
            foreach ($ignored_fields as $_ignored_field) {
                [$field, $value] = explode("|", $_ignored_field);
                $node_value = $this->queryTextNode("//$field");
                if ($node_value && $node_value == $value) {
                    return $exchange_hl7v2->setAckAE($ack, "A200", null, $newVenue);
                }
            }
        }

        // Affectation du patient
        $newVenue->patient_id = $newPatient->_id;
        $newVenue->loadRefPatient();

        // Affectation de l'établissement
        $newVenue->group_id = $sender->group_id;

        $function_handle = "handle$exchange_hl7v2->code";

        if (!method_exists($this, $function_handle)) {
            return $exchange_hl7v2->setAckAR($ack, "E006", null, $newVenue);
        }

        return $this->$function_handle($ack, $newVenue, $data);
    }

    /**
     * Handle event A01 - admit / visit notification
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     */
    function handleA01(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création possible
        return $this->handleA05($ack, $newVenue, $data);
    }

    /**
     * Handle event A05 - pre-admit a patient
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleA05(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création possible
        $_modif_sejour = false;

        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $sender         = $this->_ref_sender;

        $venueRI = CValue::read($data['admitIdentifiers'], "RI");
        //$venueRISender = CValue::read($data['admitIdentifiers'], "RI_Sender");
        $venueNPA = CValue::read($data['admitIdentifiers'], "NPA");
        $venueVN  = CValue::read($data['admitIdentifiers'], "VN");
        $venueAN  = $this->getVenueAN($sender, $data);

        $NDA = new CIdSante400();

        $sender_purge_idex_movements = $sender->_configs["purge_idex_movements"];
        if ($venueAN) {
            $NDA = CIdSante400::getMatch("CSejour", $sender->_tag_sejour, $venueAN);
        }

        // NDA non connu (non fourni ou non retrouvé)
        if (!$NDA->_id) {
            // Aucun NDA fourni / Association du NDA
            $code_NDA = !$venueAN ? "I225" : "I222";

            $found = false;

            // NPA fourni
            if (!$found && $venueNPA) {
                $manage_npa = CMbArray::get($sender->_configs, "manage_npa");
                if ($manage_npa) {
                    $NPA = CIdSante400::getMatch("CSejour", $sender->_tag_sejour, $venueNPA);
                    if ($NPA->_id) {
                        $found = true;

                        $newVenue->load($NPA->object_id);

                        // Mapping de la venue
                        $this->mappingVenue($data, $newVenue);

                        // Notifier les autres destinataires autre que le sender
                        $newVenue->_eai_sender_guid = $sender->_guid;
                        // On ne check pas la cohérence des dates des consults/intervs
                        $newVenue->_skip_date_consistencies = true;

                        if ($msgVenue = $newVenue->store()) {
                            if ($newVenue->_collisions) {
                                return $exchange_hl7v2->setAckAR(
                                    $ack,
                                    "E213",
                                    $msgVenue,
                                    reset($newVenue->_collisions)
                                );
                            }

                            return $exchange_hl7v2->setAckAR($ack, "E201", $msgVenue, $newVenue);
                        }

                        // Passage en trash du NPA
                        $NPA->tag = CAppUI::conf('dPplanningOp CSejour tag_dossier_trash') . $NPA->tag;
                        $NPA->store();
                    }
                } else {
                    return $exchange_hl7v2->setAckAR($ack, "E237", null, $newVenue);
                }
            }

            // VN fourni
            if (!$found && $venueVN && !$sender_purge_idex_movements) {
                // Le champ PV1.2 conditionne le remplissage et l'interprétation de PV1.19
                $this->getSejourByVisitNumber($newVenue, $data);
                if ($newVenue->_id) {
                    $found = true;

                    // Mapping du séjour
                    $this->mappingVenue($data, $newVenue);

                    // Notifier les autres destinataires autre que le sender
                    $newVenue->_eai_sender_guid = $sender->_guid;
                    // Pas de génération de NDA
                    $newVenue->_generate_NDA = false;
                    // On ne check pas la cohérence des dates des consults/intervs
                    $newVenue->_skip_date_consistencies = true;
                    if ($msgVenue = $newVenue->store()) {
                        if ($newVenue->_collisions) {
                            return $exchange_hl7v2->setAckAR($ack, "E213", $msgVenue, reset($newVenue->_collisions));
                        }

                        return $exchange_hl7v2->setAckAR($ack, "E201", $msgVenue, $newVenue);
                    }

                    $code_NDA      = "A222";
                    $_modif_sejour = true;
                }
            }

            // RI fourni
            if (!$found && $venueRI) {
                // Recherche du séjour par son RI
                if ($newVenue->load($venueRI)) {
                    // Mapping du séjour
                    $this->mappingVenue($data, $newVenue);

                    // Le séjour retrouvé est-il différent que celui du message ?
                    /* @todo voir comment faire (même patient, même praticien, même date ?) */

                    // Notifier les autres destinataires autre que le sender
                    $newVenue->_eai_sender_guid = $sender->_guid;
                    // Pas de génération de NDA
                    $newVenue->_generate_NDA = false;
                    // On ne check pas la cohérence des dates des consults/intervs
                    $newVenue->_skip_date_consistencies = true;
                    if ($msgVenue = $newVenue->store()) {
                        if ($newVenue->_collisions) {
                            return $exchange_hl7v2->setAckAR($ack, "E213", $msgVenue, reset($newVenue->_collisions));
                        }

                        return $exchange_hl7v2->setAckAR($ack, "E201", $msgVenue, $newVenue);
                    }

                    $code_NDA      = "I221";
                    $_modif_sejour = true;
                } // Séjour non retrouvé par son RI
                else {
                    $code_NDA = "I220";
                }
            }

            if (!$newVenue->_id) {
                // Mapping du séjour
                $this->mappingVenue($data, $newVenue);
                // Séjour retrouvé ?
                if (CAppUI::conf("hl7 strictSejourMatch")) {
                    // Recherche d'un num dossier déjà existant pour cette venue
                    if ($newVenue->loadMatchingSejour(null, true, false, true)) {
                        $code_NDA      = "A221";
                        $_modif_sejour = true;
                    }
                } else {
                    // Valuer "entree" et "sortie"
                    $newVenue->updatePlainFields();

                    // Si on a la config pour matcher les séjours de type externe, on l'enlève du tableau
                    if ($sender->_configs["exclude_not_collide_exte"]) {
                        CMbArray::removeValue("exte", $newVenue->_not_collides);
                    }

                    $collision = $newVenue->getCollisions();

                    if (count($collision) == 1) {
                        $newVenue = reset($collision);

                        $code_NDA      = "A222";
                        $_modif_sejour = true;
                    }
                }

                // Mapping du séjour
                $newVenue = $this->mappingVenue($data, $newVenue);

                // Notifier les autres destinataires autre que le sender
                $newVenue->_eai_sender_guid = $sender->_guid;
                // Pas de génération de NDA
                $newVenue->_generate_NDA = false;
                // On ne check pas la cohérence des dates des consults/intervs
                $newVenue->_skip_date_consistencies = true;

                if ($msgVenue = $newVenue->store()) {
                    if ($newVenue->_collisions) {
                        return $exchange_hl7v2->setAckAR($ack, "E213", $msgVenue, reset($newVenue->_collisions));
                    }

                    return $exchange_hl7v2->setAckAR($ack, "E201", $msgVenue, $newVenue);
                }
            }

            if ($msgNDA = CEAISejour::storeNDA($NDA, $newVenue, $sender)) {
                return $exchange_hl7v2->setAckAR($ack, "E202", $msgNDA, $newVenue);
            }

            if ($msgNRA = $this->getAlternateVisitID($data["PV1"], $newVenue)) {
                return $exchange_hl7v2->setAckAR($ack, "E214", $msgNRA, $newVenue);
            }

            // Création du VN, voir de l'objet
            if ($msgVN = $this->createObjectByVisitNumber($newVenue, $data)) {
                return $exchange_hl7v2->setAckAR($ack, "E210", $msgVN, $newVenue);
            }

            $codes = [($_modif_sejour ? "I202" : "I201"), $code_NDA];

            $comment = CEAISejour::getComment($newVenue);
            $comment .= CEAISejour::getComment($NDA);
        } // NDA connu
        else {
            $error_code = "";
            if ($this->isAmbiguousNDA($newVenue, $data, $NDA, $error_code)) {
                return $exchange_hl7v2->setAckAR($ack, $error_code, CAppUI::tr("CHL7Event-E234"), $newVenue);
            }

            // Mapping de la venue
            $this->mappingVenue($data, $newVenue);

            // RI non fourni
            if (!$venueRI) {
                $code_NDA = "I223";
            } else {
                $tmpVenue = new CSejour();
                // RI connu
                if ($tmpVenue->load($venueRI)) {
                    if ($tmpVenue->_id != $NDA->object_id) {
                        $comment = "L'id source fait référence au séjour : $NDA->object_id et l'id cible au séjour : $tmpVenue->_id.";

                        return $exchange_hl7v2->setAckAR($ack, "E230", $comment, $newVenue);
                    }
                    $code_NDA = "I224";
                } // RI non connu
                else {
                    $code_NDA = "A220";
                }
            }

            // Notifier les autres destinataires autre que le sender
            $newVenue->_eai_sender_guid = $sender->_guid;
            // On ne check pas la cohérence des dates des consults/intervs
            $newVenue->_skip_date_consistencies = true;

            // Dans le cas d'une multiple séance, la venue associée à un NDA n'est pas nécessairement retrouvée
            $seance = false;
            if (!$newVenue->_id) {
                // Pas de génération de NDA
                $newVenue->_generate_NDA = false;
                $seance                  = true;
            }

            if ($msgVenue = $newVenue->store()) {
                if ($newVenue->_collisions) {
                    return $exchange_hl7v2->setAckAR($ack, "E213", $msgVenue, reset($newVenue->_collisions));
                }

                return $exchange_hl7v2->setAckAR($ack, "E201", $msgVenue, $newVenue);
            }

            // Dans le cas d'une multiple séance, la venue associée à un NDA n'est pas nécessairement retrouvée
            if ($seance) {
                $NDA = CIdSante400::getMatch("CSejour", $sender->_tag_sejour, $venueAN, $newVenue->_id);

                if ($msgNDA = CEAISejour::storeNDA($NDA, $newVenue, $sender)) {
                    return $exchange_hl7v2->setAckAR($ack, "E202", $msgNDA, $newVenue);
                }
            }

            // Création du VN, voir de l'objet
            if ($msgVN = $this->createObjectByVisitNumber($newVenue, $data)) {
                return $exchange_hl7v2->setAckAR($ack, "E210", $msgVN, $newVenue);
            }

            $codes = ["I202", $code_NDA];

            $comment = CEAISejour::getComment($newVenue);
        }

        // Mapping du mouvement
        if ($sender_purge_idex_movements) {
            // On recherche un mouvement de l'event (A05/A01/A04)
            $movement                        = new CMovement();
            $movement->sejour_id             = $newVenue->_id;
            $movement->original_trigger_code = $this->_ref_exchange_hl7v2->code;
            $movement->cancel                = 0;
            $movement->loadMatchingObject();

            // Si on a un mouvement alors on annule tous les autres
            if ($movement->_id) {
                foreach ($newVenue->loadRefsMovements() as $_movement) {
                    // On passe en trash l'idex associé
                    $_movement->loadLastId400();
                    $last_id400 = $_movement->_ref_last_id400;
                    if ($last_id400->_id) {
                        $last_id400->tag              = "trash_" . $last_id400->tag;
                        $last_id400->_eai_sender_guid = $sender->_guid;
                        $last_id400->store();
                    }

                    // On annule le mouvement
                    $_movement->cancel           = 1;
                    $_movement->_eai_sender_guid = $sender->_guid;
                    $_movement->store();
                }
            }
        }

        $return_movement = $this->mapAndStoreMovement($ack, $newVenue, $data);
        if (is_string($return_movement)) {
            return $return_movement;
        }
        $movement = $return_movement;

        // Mapping de l'affectation
        $return_affectation = $this->mapAndStoreAffectation($newVenue, $data, $return_movement);
        if (is_string($return_affectation)) {
            return $exchange_hl7v2->setAckAR($ack, "E208", $return_affectation, $newVenue);
        }
        $affectation = $return_affectation;

        // Affectation de l'affectation au mouvement
        if ($movement && $affectation && $affectation->_id) {
            $movement->affectation_id   = $affectation->_id;
            $movement->_eai_sender_guid = $sender->_guid;
            $movement->store();
        }

        // Dans le cas d'une grossesse
        if ($return_grossesse = $this->storeGrossesse($newVenue)) {
            return $exchange_hl7v2->setAckAR($ack, "E211", $return_grossesse, $newVenue);
        }

        // Dans le cas d'une naissance
        if ($return_naissance = $this->mapAndStoreNaissance($newVenue, $data)) {
            return $exchange_hl7v2->setAckAR($ack, "E212", $return_naissance, $newVenue);
        }

        return $exchange_hl7v2->setAckAA($ack, $codes, $comment, $newVenue);
    }

    /**
     * Mapping de la venue
     *
     * @param array   $data     Datas
     * @param CSejour $newVenue Admit
     *
     * @return CSejour
     * @throws CHL7v2Exception
     */
    function mappingVenue($data, CSejour $newVenue)
    {
        $event_code = $this->_ref_exchange_hl7v2->code;

        // Cas spécifique de certains segments
        // A14 : Demande de pré-admission
        if ($event_code == "A14") {
            $newVenue->recuse = -1;
        }

        // A27 : Annulation de la demande de pré-admission
        // A38 : Annulation du séjour
        if ($event_code == "A38" || $event_code == "A27") {
            $newVenue->annule = 1;
        }

        // A11 : suppression de l'entrée et/ou annulation du séjour si on a pas de mouvement de pré-admission
        if ($event_code == "A11") {
            $movements = $newVenue->loadRefsMovements(["original_trigger_code" => " = 'A05'"]);
            if (!$movements) {
                $newVenue->annule = 1;
            }
        }

        // A15 : Mutation prévisionnelle
        if ($event_code == "A15") {
            $newVenue->mode_sortie = "transfert";
        }

        // A26 : Annulation mutation prévisionnelle
        if ($event_code == "A26") {
            $newVenue->mode_sortie = "";
        }

        // A16 : Sortie définitive confirmée
        if ($event_code == "A16") {
            $newVenue->confirme = $newVenue->sortie;
        }

        // A25 : Annulation de la confirmation de la sortie définitive
        if ($event_code == "A25") {
            $newVenue->confirme = "";
        }

        // Segment PV1
        $this->getSegment("PV1", $data, $newVenue);

        // Segment PV2
        $this->getSegment("PV2", $data, $newVenue);

        // Segment ZFD
        $this->getSegment("ZFD", $data, $newVenue);

        // Segment ZFM
        $this->getSegment("ZFM", $data, $newVenue);

        // Segment ZFP
        $this->getSegment("ZFP", $data, $newVenue);

        // Segment ZFV
        $this->getSegment("ZFV", $data, $newVenue);

        // Segment DRG
        if (array_key_exists("DRG", $data)) {
            foreach ($data["DRG"] as $_DRG) {
                $this->getDRG($_DRG, $newVenue);
            }
        }

        // Débiteurs
        if (array_key_exists("GT1", $data)) {
            foreach ($data["GT1"] as $_GT1) {
                $this->getGT1($_GT1, $newVenue);
            }
        }

        // Couverture
        if (array_key_exists("insurances", $data)) {
            foreach ($data["insurances"] as $_insurance) {
                if (array_key_exists("IN1", $_insurance)) {
                    foreach ($_insurance["IN1"] as $_IN1) {
                        $this->getIN1($_IN1, $newVenue);
                    }
                }
            }
        }

        // Constantes
        if (array_key_exists("OBX", $data)) {
            foreach ($data["OBX"] as $_OBX) {
                $this->getOBX($_OBX, $newVenue, $data);
            }
        }

        // Dans le cas où l'on a pas de PV2, la sortie prévue peut-être nulle
        if ($newVenue->entree_reelle && !$newVenue->sortie_prevue) {
            $entree = $newVenue->entree_reelle ? $newVenue->entree_reelle : $newVenue->entree_prevue;

            $addDateTime = CAppUI::gconf("dPplanningOp CSejour sortie_prevue " . $newVenue->type);
            switch ($addDateTime) {
                case "1/4":
                    $addDateTime = "00:15:00";
                    break;
                case "1/2":
                    $addDateTime = "00:30:00";
                    break;
                default:
                    $addDateTime = $addDateTime . ":00:00";
            }
            $newVenue->sortie_prevue = CMbDT::addDateTime($addDateTime, $entree);
        }

        /* TODO Supprimer ceci après l'ajout des times picker */
        $newVenue->_hour_entree_prevue = null;
        $newVenue->_min_entree_prevue  = null;
        $newVenue->_hour_sortie_prevue = null;
        $newVenue->_min_sortie_prevue  = null;

        return $newVenue;
    }

    /**
     * Récupération du segment DRG
     *
     * @param DOMNode $node     Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getDRG(DOMNode $node, CSejour $newVenue)
    {
        if (!$newVenue->_id) {
            return;
        }

        $DRG_3 = $this->queryTextNode("DRG.3", $node);
        if ($DRG_3 == "Y") {
            $newVenue->facture = "1";
        }
    }

    /**
     * Récupération du segment GT1
     *
     * @param DOMNode $node     Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getGT1(DOMNode $node, CSejour $newVenue)
    {
        if (!$newVenue->_id) {
            return;
        }

        $patient = $newVenue->_ref_patient;
        if (!$patient->_id) {
            return;
        }

        $GT1_2 = $this->queryTextNode("GT1.2/CX.1", $node);

        $GT1_3  = $this->queryNode("GT1.3", $node);
        $nom    = $this->queryTextNode("XPN.1/FN.1", $GT1_3);
        $prenom = $this->queryTextNode("XPN.2", $GT1_3);

        if ($prenom == "") {
            $prenom = null;
        }

        $adresse = $ville = $cp = null;
        $GT1_5   = $this->queryNode("GT1.5", $node);
        if ($GT1_5) {
            $adresse = $this->queryTextNode("XAD.1/SAD.1", $GT1_5);
            $ville   = $this->queryTextNode("XAD.3", $GT1_5);
            $cp      = $this->queryTextNode("XAD.5", $GT1_5);
        }

        $tel   = $ean = $ean_base = null;
        $GT1_6 = $this->queryNodes("GT1.6", $node);
        if ($GT1_6) {
            foreach ($GT1_6 as $_GT1_6) {
                if (!$tel) {
                    $tel = $this->queryTextNode("XTN.12", $_GT1_6);
                }

                if (!$tel) {
                    $tel = $this->queryTextNode("XTN.1", $_GT1_6);
                }

                $XTN_4 = $this->queryTextNode("XTN.4", $_GT1_6);
                // EAN base => Ean Dest
                if (preg_match('/^Ean Dest=(\d+)$/', $XTN_4, $matches) === 1) {
                    $ean_base = CMbArray::get($matches, 1);
                }

                // EAN => Code Ean
                if (preg_match('/^Code Ean=(\d+)$/', $XTN_4, $matches) === 1) {
                    $ean = CMbArray::get($matches, 1);
                }
            }
        }

        $GT1_13 = $this->queryTextNode("GT1.13", $node);
        $GT1_14 = $this->queryTextNode("GT1.14", $node);

        $corres_patient      = new CCorrespondantPatient();
        $ds                  = $corres_patient->getDS();
        $where               = [];
        $where["patient_id"] = " = '$patient->_id'";
        if ($nom) {
            $where["nom"] = $ds->prepare("LIKE %", $nom);
        }
        if ($prenom) {
            $where["prenom"] = $ds->prepare("LIKE %", $prenom);
        }
        $where["relation"] = " = 'assurance'";
        $now               = CMbDT::date();
        $next              = CMbDT::date("+ 1 DAY");

        $where["date_debut"] = " <= '$now' AND IFNULL(date_fin, '$next') >= '$now'";

        $corres_patient->loadObject($where);

        if (!$corres_patient->_id) {
            $corres_patient->patient_id = $patient->_id;
            $corres_patient->nom        = $nom;
            $corres_patient->relation   = "assurance";
        }

        $corres_patient->prenom  = $prenom;
        $corres_patient->adresse = $adresse;
        $corres_patient->cp      = $cp;
        $corres_patient->ville   = $ville;
        $corres_patient->tel     = $tel;
        $GT1_16                  = $this->queryNode("GT1.16", $node);
        if ($GT1_16) {
            $num_assure = $this->queryTextNode("XPN.1/FN.1", $GT1_16);
            if ($num_assure != "0") {
                $corres_patient->assure_id = $num_assure;
            }
        }

        $corres_patient->ean      = $ean;
        $corres_patient->ean_base = $ean_base;

        if ($ean) {
            $where = [
                "patient_id" => "IS NULL",
                "relation"   => "= 'assurance'",
                "ean"        => "= '$ean'",
            ];

            $assurance = new CCorrespondantPatient();
            $assurance->loadObject($where);

            $corres_patient->type_pec = $assurance->type_pec;
        }

        if ($GT1_13) {
            $corres_patient->date_debut = CMbDT::date($GT1_13);
        }

        if ($GT1_14) {
            $corres_patient->date_fin = CMbDT::date($GT1_14);
        }

        $sender = $this->_ref_sender;
        // Notifier les autres destinataires autre que le sender
        $corres_patient->_eai_sender_guid = $sender->_guid;

        if ($msg = $corres_patient->store()) {
            $corres_patient->repair();
            $corres_patient->_eai_sender_guid = $sender->_guid;
            $corres_patient->store();
        }
    }

    /**
     * Récupération du séjour par le numéro de visite
     *
     * @param CSejour $newVenue Admit
     * @param array   $data     Datas
     *
     * @return bool
     * @throws Exception
     */
    function getSejourByVisitNumber(CSejour $newVenue, $data)
    {
        $sender  = $this->_ref_sender;
        $venueVN = CValue::read($data['admitIdentifiers'], "VN");

        $where                      = $ljoin = [];
        $where["id_sante400.tag"]   = " = '$sender->_tag_visit_number'";
        $where["id_sante400.id400"] = " = '$venueVN'";

        switch ($this->queryTextNode("PV1.2", $data["PV1"])) {
            // Identifie la venue pour actes et consultation externe
            case 'O':
                $consultation = new CConsultation();

                $ljoin["id_sante400"]              = "id_sante400.object_id = consultation.consultation_id";
                $where["id_sante400.object_class"] = " = 'CConsultation'";
                $where["consultation.type"]        = " != 'chimio'";

                $consultation->loadObject($where, null, null, $ljoin);
                // Nécessaire pour savoir quel objet créé en cas de besoin
                $this->_object_found_by_vn = $consultation;

                if (!$consultation->_id) {
                    return false;
                }

                $newVenue->load($consultation->sejour_id);

                return true;
            // Identifie une séance
            case 'R':
                $consultation = new CConsultation();

                $ljoin["id_sante400"]              = "id_sante400.object_id = consultation.consultation_id";
                $where["id_sante400.object_class"] = " = 'CConsultation'";
                $where["consultation.type"]        = " = 'chimio'";

                $consultation->loadObject($where, null, null, $ljoin);
                // Nécessaire pour savoir quel objet créé en cas de besoin
                $this->_object_found_by_vn = $consultation;

                if (!$consultation->_id) {
                    return false;
                }

                $newVenue->load($consultation->sejour_id);

                return true;
            // Identifie le n° de passage aux urgences
            case 'E':
                $rpu = new CRPU();

                $ljoin["id_sante400"]              = "id_sante400.object_id = rpu.rpu_id";
                $where["id_sante400.object_class"] = " = 'CRPU'";

                $rpu->loadObject($where, null, null, $ljoin);
                // Nécessaire pour savoir quel objet créé en cas de besoin
                $this->_object_found_by_vn = $rpu;

                if (!$rpu->_id) {
                    return false;
                }

                $newVenue->load($rpu->sejour_id);

                return true;
            // Identifie le séjour ou hospitalisation à domicile
            default:
                $idexVisitNumber           = CIdSante400::getMatch("CSejour", $sender->_tag_visit_number, $venueVN);
                $this->_object_found_by_vn = $newVenue;
                if (!$idexVisitNumber->_id) {
                    return false;
                }

                $newVenue->load($idexVisitNumber->object_id);
                $this->_object_found_by_vn = $newVenue;

                return true;
        }
    }

    /**
     * Récupération du numéro de visit alternatif
     *
     * @param DOMNode $node     PV1 Node
     * @param CSejour $newVenue Admit
     *
     * @return string
     * @throws Exception
     */
    function getAlternateVisitID(DOMNode $node, CSejour $newVenue)
    {
        if (!CAppUI::conf("dPplanningOp CSejour use_dossier_rang")) {
            return null;
        }

        $sender = $this->_ref_sender;

        $tag_NRA = $newVenue->getTagNRA($newVenue->group_id);
        $PV1_50  = $this->queryTextNode("PV1.50/CX.1", $node);

        //Paramétrage de l'id 400
        $idexNRA                   = CIdSante400::getMatch($newVenue->_class, $tag_NRA, $PV1_50, $newVenue->_id);
        $idexNRA->_eai_sender_guid = $sender->_guid;

        return $idexNRA->store();
    }

    /**
     * Création de l'objet par son numéro de visite
     *
     * @param CSejour $newVenue Admit
     * @param array   $data     Datas
     *
     * @return null|string|void
     * @throws Exception
     */
    function createObjectByVisitNumber(CSejour $newVenue, $data)
    {
        $venueVN = CValue::read($data['admitIdentifiers'], "VN");
        if (!$venueVN) {
            return null;
        }

        $this->getSejourByVisitNumber($newVenue, $data);
        if (!$this->_object_found_by_vn) {
            return null;
        }

        $sender = $this->_ref_sender;

        $object_found_by_vn = $this->_object_found_by_vn;
        // Création de l'objet ?
        if (!$object_found_by_vn->_id && CAppUI::conf("smp create_object_by_vn")) {
            $where                         = [];
            $where["sejour_id"]            = " = '$newVenue->_id'";
            $object_found_by_vn->sejour_id = $newVenue->_id;

            // On va rechercher l'objet en fonction de son type, où le créer
            switch ($this->queryTextNode("PV1.2", $data["PV1"])) {
                // Identifie la venue pour actes et consultation externe (CConsultation && type != chimio)
                case 'O':
                    $where["type"] = " != 'chimio'";
                    break;
                // Identifie une séance (CConsultation && type == chimio)
                case 'R':
                    $where["type"]            = " = 'chimio'";
                    $object_found_by_vn->type = "chimio";
                    break;
                // Identifie le n° de passage aux urgences
                case 'E':
                    $object_found_by_vn->_patient_id = $newVenue->patient_id;
                    $object_found_by_vn->_entree     = $newVenue->entree;

                    break;
                default:
            }

            $count_list = $object_found_by_vn->countList($where);
            if ($count_list > 1) {
                /* @todo voir comment gérer ceci ! */
                return null;
            }

            if ($object_found_by_vn instanceof CConsultation) {
                $datetime = $this->queryTextNode("EVN.6/TS.1", $data["EVN"]);

                if ($data["PV2"]) {
                    $object_found_by_vn->motif = $this->queryTextNode("PV2.12", $data["PV2"]);
                }

                try {
                    // Création de la consultation
                    $object_found_by_vn->createByDatetime(
                        $datetime,
                        $newVenue->praticien_id,
                        $newVenue->patient_id
                    );
                } catch (CMbException $e) {
                    return $e->getMessage();
                }
            }

            // Dans le cas où l'on doit créer l'objet
            if (!$object_found_by_vn->_id) {
                $object_found_by_vn->_eai_sender_guid = $sender->_guid;
                if ($msg = $object_found_by_vn->store()) {
                    return $msg;
                }
            }

            // On affecte le VN
            $object_class = $object_found_by_vn->_class;
            $object_id    = $object_found_by_vn->_id;
        } else {
            // On affecte le VN du séjour
            $object_class = $newVenue->_class;
            $object_id    = $newVenue->_id;
        }

        $idexVN = CIdSante400::getMatch($object_class, $sender->_tag_visit_number, $venueVN, $object_id);
        // L'idex est déjà associé sur notre objet
        if ($idexVN->_id) {
            return null;
        }

        // Création de l'idex
        $idexVN->_eai_sender_guid = $sender->_guid;

        return $idexVN->store();
    }

    /**
     * Mapping et enregistrement du mouvement
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return CMovement|string|null
     * @throws Exception
     */
    function mapAndStoreMovement(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        if (!array_key_exists("ZBE", $data) || !$data["ZBE"]) {
            return null;
        }

        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;

        $movement = new CMovement();
        if (!$movement = $this->mappingMovement($data, $newVenue, $movement)) {
            return $exchange_hl7v2->setAckAR($ack, "E206", null, $newVenue);
        }

        if (is_string($movement)) {
            return $exchange_hl7v2->setAckAR($ack, "E206", $movement, $newVenue);
        }

        return $movement;
    }

    /**
     * Mapping du mouvement
     *
     * @param array     $data     Datas
     * @param CSejour   $newVenue Admit
     * @param CMovement $movement Movement
     *
     * @return CMovement|string|null
     */
    function mappingMovement($data, CSejour $newVenue, CMovement $movement)
    {
        if (!array_key_exists("ZBE", $data) || !$data["ZBE"]) {
            return null;
        }

        // Segment ZBE
        return $this->getZBE($data["ZBE"], $newVenue, $movement);
    }

    /**
     * Récupération du segment ZBE
     *
     * @param DOMNode   $node     Node
     * @param CSejour   $newVenue Admit
     * @param CMovement $movement Movement
     *
     * @return CMovement|string|null
     * @throws Exception
     */
    function getZBE(DOMNode $node, CSejour $newVenue, CMovement $movement)
    {
        $sender      = $this->_ref_sender;
        $idex_create = false;
        $event_code  = $this->_ref_exchange_hl7v2->code;

        $own_movement    = null;
        $sender_movement = null;
        foreach ($this->queryNodes("ZBE.1", $node) as $ZBE_1) {
            $EI_1 = $this->queryTextNode("EI.1", $ZBE_1);
            $EI_2 = $this->queryTextNode("EI.2", $ZBE_1);
            $EI_3 = $this->queryTextNode("EI.3", $ZBE_1);

            // Notre propre identifiant de mouvement
            if ($EI_2 == CAppUI::conf("hl7 CHL7 assigning_authority_namespace_id", "CGroups-$sender->group_id") ||
                $EI_3 == CAppUI::conf("hl7 CHL7 assigning_authority_universal_id", "CGroups-$sender->group_id")) {
                $own_movement = $EI_1;
                break;
            }

            // L'identifiant de mouvement du sender
            if ($EI_3 == $sender->_configs["assigning_authority_universal_id"] ||
                $EI_2 == $sender->_configs["assigning_authority_universal_id"]) {
                $sender_movement = $EI_1;
                continue;
            }
        }

        if (!$own_movement && !$sender_movement) {
            return "Impossible d'identifier le mouvement";
        }

        $movement_id = $own_movement ? $own_movement : $sender_movement;
        if (!$movement_id) {
            return null;
        }

        $start_movement_dt = $this->queryTextNode("ZBE.2/TS.1", $node);
        $action            = $this->queryTextNode("ZBE.4", $node);
        $original_trigger  = $this->queryTextNode("ZBE.6", $node);
        if (!$original_trigger) {
            $original_trigger = $event_code;
        }

        $movement->sejour_id             = $newVenue->_id;
        $movement->original_trigger_code = $original_trigger;
        $movement->cancel                = 0;

        $idexMovement = new CIdSante400();

        // Notre propre ID de mouvement
        if ($own_movement) {
            $movement_id_split       = explode("-", $movement_id);
            $movement->movement_type = $movement_id_split[0];
            $movement->_id           = $movement_id_split[1];
            $movement->loadMatchingObjectEsc();
            if (!$movement->_id) {
                return null;
            }

            if ($sender_movement) {
                $idexMovement = CIdSante400::getMatch("CMovement", $sender->_tag_movement, $sender_movement);
                if (!$idexMovement->_id) {
                    $idex_create = true;
                }
            }
        } // ID mouvement provenant d'un système tiers
        else {
            $idexMovement = CIdSante400::getMatch("CMovement", $sender->_tag_movement, $movement_id);
            if ($idexMovement->_id) {
                $movement->load($idexMovement->object_id);
            } // Recherche d'un mouvement identique dans le cas ou il ne s'agit pas d'une mutation / absence
            else {
                $idex_create = true;
                if ($event_code != "A02" && $event_code != "A21") {
                    $movement->cancel = 0;
                    $movement->loadMatchingObjectEsc();
                }
            }

            $movement->movement_type = $newVenue->getMovementType($original_trigger);
        }

        // Erreur dans le cas où le type du mouvement est UPDATE ou CANCEL et que l'on a pas retrouvé le mvt
        if (($action == "UPDATE" || $action == "CANCEL") && !$movement->_id) {
            return null;
        }

        if ($action == "CANCEL") {
            $movement->cancel = true;
        }

        $movement->start_of_movement = $start_movement_dt;
        $movement->_eai_sender_guid  = $sender->_guid;
        if ($msg = $movement->store()) {
            return $msg;
        }

        if ($idex_create) {
            $idexMovement->object_id        = $movement->_id;
            $idexMovement->_eai_sender_guid = $sender->_guid;
            if ($msg = $idexMovement->store()) {
                return $msg;
            }
        }

        return $movement;
    }

    /**
     * Mapping et enregistrement de l'affectation
     *
     * @param CSejour   $newVenue Admit
     * @param array     $data     Datas
     * @param CMovement $movement Movement
     *
     * @return CAffectation|string|null
     * @throws Exception
     */
    function mapAndStoreAffectation(CSejour $newVenue, $data, CMovement $movement = null)
    {
        $sender = $this->_ref_sender;

        if ($newVenue->annule) {
            return null;
        }

        $PV1_3 = $this->queryNode("PV1.3", $data["PV1"]);

        $affectation            = new CAffectation();
        $affectation->sejour_id = $newVenue->_id;

        $event_code = $this->_ref_exchange_hl7v2->code;

        // Récupération de la date de réalisation de l'évènement
        // Dans le cas spécifique de quelques évènements, on récupère le code sur le ZBE
        $datetime = $this->queryTextNode("EVN.6/TS.1", $data["EVN"]);
        if (array_key_exists("ZBE", $data) && $data["ZBE"] && CMbArray::in(
                $event_code,
                ["A01", "A02", "A04", "A15", "Z80", "Z84"]
            )) {
            $datetime = $this->queryTextNode("ZBE.2/TS.1", $data["ZBE"]);
        }

        switch ($event_code) {
            // Cas d'une sortie, on ne fait rien sur l'affectation
            case "A03":
                return null;

            // Cas d'une suppression de mutation ou d'une permission d'absence
            case "A12":
            case "A52":
                // Quand on a un mouvement (provenant d'un ZBE)
                if (array_key_exists("ZBE", $data) && $data["ZBE"]) {
                    if (!$movement) {
                        return null;
                    }

                    $affectation->load($movement->affectation_id);
                    if (!$affectation->_id) {
                        return "Le mouvement '$movement->_id' n'est pas lié à une affectation dans Mediboard";
                    }
                } // Cas de l'international
                else {
                    $affectation->entree = $datetime;
                    $affectation->loadMatchingObject();

                    if (!$affectation->_id) {
                        return null;
                    }
                }

                // Pas de synchronisation
                $affectation->_no_synchro_eai = true;
                if ($msgAffectation = $affectation->delete()) {
                    return $msgAffectation;
                }

                return null;

            // Annulation admission
            case "A11":
                if (!$movement) {
                    return null;
                }

                $affectation = $newVenue->getCurrAffectation($datetime);

                // Si le mouvement n'a pas d'affectation associée, et que l'on a déjà une affectation dans MB
                if (!$movement->affectation_id && $affectation->_id) {
                    return "Le mouvement '$movement->_id' n'est pas lié à une affectation dans Mediboard";
                }

                // Si on a une affectation associée, alors on charge celle-ci
                if ($movement->affectation_id) {
                    $affectation = $movement->loadRefAffectation();
                }

                // Pas de synchronisation
                $affectation->_no_synchro_eai = true;
                if ($msg = $affectation->delete()) {
                    return $msg;
                }

                return null;

            // Annuler le retour du patient
            case "A53":
                if (!$movement) {
                    return null;
                }

                $affectation->load($movement->affectation_id);
                if (!$affectation->_id) {
                    return "Le mouvement '$movement->_id' n'est pas lié à une affectation dans Mediboard";
                }

                $affectation->effectue = 0;

                // Pas de synchronisation
                $affectation->_no_synchro_eai  = true;
                $affectation->_eai_sender_guid = $sender->_guid;
                if ($msg = $affectation->store()) {
                    return $msg;
                }

                return $affectation;

            // Cas d'un départ pour une permission d'absence
            case "A21":
                $affectation->entree = $datetime;
                $affectation->loadMatchingObject();

                // Si on ne retrouve pas une affectation
                // Création de l'affectation
                // et mettre à 'effectuee' la précédente si elle existe sinon création de celle-ci
                if (!$affectation->_id) {
                    $service_externe = CService::loadServiceExterne($sender->group_id);

                    if (!$service_externe->_id) {
                        return "CService-externe-none";
                    }

                    $affectation->service_id = $service_externe->_id;

                    $return_affectation = $newVenue->forceAffectation($affectation, true);
                    //$datetime, $affectation->lit_id, $affectation->service_id);
                    if (is_string($return_affectation)) {
                        return $return_affectation;
                    }

                    $affectation = $return_affectation;
                }

                return $affectation;

            // Cas d'un retour pour une permission d'absence
            case "A22":
                $service_externe = CService::loadServiceExterne($sender->group_id);

                if (!$service_externe->_id) {
                    return "CService-externe-none";
                }

                // Recherche de l'affectation correspondant à une permission d'absence
                $search              = new CAffectation();
                $where               = [];
                $where["sejour_id"]  = "=  '$newVenue->_id'";
                $where["service_id"] = "=  '$service_externe->_id'";
                $where["effectue"]   = "=  '0'";
                $where["entree"]     = "<= '$datetime'";
                $where["sortie"]     = ">= '$datetime'";
                $search->loadObject($where);

                // Si on ne la retrouve pas on prend la plus proche
                if (!$search->_id) {
                    $where               = [];
                    $where["sejour_id"]  = "=  '$newVenue->_id'";
                    $where["service_id"] = "=  '$service_externe->_id'";
                    $where["effectue"]   = "=  '0'";

                    $search->loadObject($where);
                }

                $search->effectue         = 1;
                $search->sortie           = $datetime;
                $search->_eai_sender_guid = $sender->_guid;
                if ($msg = $search->store()) {
                    return $msg;
                }

                return $search;

            // Cas mutation
            case "A02":
                $affectation->entree = $datetime;
                $affectation->loadMatchingObject();

                // Si on ne retrouve pas une affectation
                // Création de l'affectation
                // et mettre à 'effectuee' la précédente si elle existe sinon création de celle-ci
                if (!$affectation->_id && $PV1_3) {
                    // Récupération du Lit et UFs
                    $this->getPL($PV1_3, $affectation, $newVenue);

                    $return_affectation = $newVenue->forceAffectation($affectation, true);
                    //$datetime, $affectation->lit_id, $affectation->service_id);
                    if (is_string($return_affectation)) {
                        return $return_affectation;
                    }

                    $affectation = $return_affectation;
                }

                break;

            // Cas modification
            case "Z99":
                if (!$movement) {
                    return null;
                }

                // Si on a une affectation associée, alors on charge celle-ci
                if ($movement->affectation_id) {
                    $affectation = $movement->loadRefAffectation();
                } else {
                    // On recherche l'affectation "courante"
                    // Si qu'une affectation sur le séjour
                    $newVenue->loadRefsAffectations();
                    if (count($newVenue->_ref_affectations) == 1) {
                        $affectation = reset($newVenue->_ref_affectations);
                    } else {
                        // On recherche l'affectation "courante"
                        $affectation = $newVenue->getCurrAffectation($datetime);
                    }

                    // Sinon on récupère et on met à jour la première affectation
                    if (!$affectation->_id) {
                        $affectation->sejour_id = $newVenue->_id;
                        $affectation->entree    = $newVenue->entree;
                        $affectation->sortie    = $newVenue->sortie;
                    }
                }

                break;

            case "A08":
                // On recherche l'affectation "courante"
                // Si qu'une affectation sur le séjour
                $newVenue->loadRefsAffectations();
                if (count($newVenue->_ref_affectations) == 1) {
                    $affectation = reset($newVenue->_ref_affectations);
                } else {
                    // On recherche l'affectation "courante"
                    $affectation = $newVenue->getCurrAffectation($datetime);
                }

                // Sinon on récupère et on met à jour la première affectation
                if (!$affectation->_id) {
                    $affectation->sejour_id = $newVenue->_id;
                    $affectation->entree    = $newVenue->entree;
                    $affectation->sortie    = $newVenue->sortie;
                }

                break;

            // Tous les autres cas on récupère et on met à jour la première affectation
            default:
                $newVenue->loadRefsAffectations();
                $affectation = $newVenue->_ref_first_affectation;
                if (!$affectation->_id) {
                    $affectation->sejour_id = $newVenue->_id;
                    $affectation->entree    = $newVenue->entree;
                    $affectation->sortie    = $newVenue->sortie;
                }
        }

        // Si pas d'UF/service/chambre/lit on retourne une affectation vide
        if (!$PV1_3) {
            if ($msgVenue = self::storeUFMedicaleSoinsSejour($data, $newVenue)) {
                return $msgVenue;
            }

            return $affectation;
        }

        if ($this->queryTextNode("PL.1", $PV1_3) == $sender->_configs["handle_PV1_3_null"]) {
            if ($msgVenue = self::storeUFMedicaleSoinsSejour($data, $newVenue)) {
                return $msgVenue;
            }

            return $affectation;
        }

        // Si pas de lit on affecte le service sur le séjour
        if (!$this->queryTextNode("PL.3", $PV1_3)) {
            $affectation_uf = new CAffectationUniteFonctionnelle();

            // On essaye de récupérer le service dans ce cas depuis l'UF d'hébergement
            $date_deb = $affectation->_id ? CMbDT::date($affectation->sortie) : CMbDT::date($newVenue->sortie);
            $date_fin = $affectation->_id ? CMbDT::date($affectation->entree) : CMbDT::date($newVenue->entree);
            $uf       = CUniteFonctionnelle::getUF(
                $this->queryTextNode("PL.1", $PV1_3),
                "hebergement",
                $newVenue->group_id,
                $date_deb,
                $date_fin
            );
            if ($uf->code && $uf->_id) {
                $affectation_uf->uf_id        = $uf->_id;
                $affectation_uf->object_class = "CService";
                $affectation_uf->loadMatchingObject();
            }

            // Dans le cas où l'on retrouve un service associé à l'UF d'hébergement
            if ($affectation_uf->_id) {
                $newVenue->service_id        = $affectation_uf->object_id;
                $newVenue->uf_hebergement_id = $affectation_uf->uf_id;
            }

            $uf_med                   = $this->mappingUFMedicale($data, $newVenue, $affectation);
            $newVenue->uf_medicale_id = $uf_med ? $uf_med->_id : null;

            $uf_soins              = $this->mappingUFSoins($data, $newVenue, $affectation);
            $newVenue->uf_soins_id = $uf_soins ? $uf_soins->_id : null;

            // On ne check pas la cohérence des dates des consults/intervs
            $newVenue->_skip_date_consistencies = true;
            $newVenue->_eai_sender_guid         = $sender->_guid;

            if ($msgVenue = self::storeUFMedicaleSoinsSejour($data, $newVenue)) {
                return $msgVenue;
            }

            // Si on a pas d'UF on retourne une affectation vide
            if (!$uf->_id || !$affectation_uf->_id) {
                return $affectation;
            }
        }

        // Récupération du Lit et UFs
        $this->getPL($PV1_3, $affectation, $newVenue);

        $uf_med                      = $this->mappingUFMedicale($data, $newVenue, $affectation);
        $affectation->uf_medicale_id = $uf_med ? $uf_med->_id : null;

        $uf_soins                 = $this->mappingUFSoins($data, $newVenue, $affectation);
        $affectation->uf_soins_id = $uf_soins ? $uf_soins->_id : null;

        $affectation->_eai_sender_guid = $sender->_guid;
        if ($msg = $affectation->store()) {
            return $msg;
        }

        return $affectation;
    }

    /**
     * Récupération de la location du patient
     *
     * @param DOMNode      $node        PV1 Node
     * @param CAffectation $affectation Affectation
     * @param CSejour      $newVenue    Séjour
     *
     * @return void
     * @throws Exception
     */
    function getPL($node, CAffectation $affectation, CSejour $newVenue = null)
    {
        if (!$node) {
            return;
        }

        $sender = $this->_ref_sender;

        // Récupération de la chambre
        $nom_chambre = $this->queryTextNode("PL.2", $node);
        $chambre     = new CChambre();

        // Récupération du lit
        $nom_lit = $this->queryTextNode("PL.3", $node);
        $lit     = new CLit();

        switch ($sender->_configs["handle_PV1_3"]) {
            // idex du service
            case 'idex':
                if ($nom_lit) {
                    $lit_id = CIdSante400::getMatch("CLit", $sender->_tag_lit, $nom_lit)->object_id;
                    $lit->load($lit_id);
                }

                break;
            // Dans tous les cas le nom du lit est celui que l'on reçoit du flux
            default:
                $where = $ljoin = [];

                if ($nom_chambre) {
                    $ljoin["service"]        = "service.service_id = chambre.service_id";
                    $where["chambre.nom"]    = " = '$nom_chambre'";
                    $where["chambre.annule"] = " = '0'";
                    $where["group_id"]       = " = '$sender->group_id'";

                    $chambre->escapeValues();
                    $chambre->loadObject($where, null, null, $ljoin);
                    $chambre->unescapeValues();
                }

                $where = $ljoin = [];
                if ($nom_lit) {
                    $ljoin["chambre"]           = "chambre.chambre_id = lit.chambre_id";
                    $ljoin["service"]           = "service.service_id = chambre.service_id";
                    $where["lit.nom"]           = " = '$nom_lit'";
                    $where["lit.annule"]        = " = '0'";
                    $where["service.cancelled"] = " = '0'";
                    $where["group_id"]          = " = '$sender->group_id'";
                    if ($chambre->_id) {
                        $where["chambre.chambre_id"] = " = '$chambre->_id'";
                    }

                    $lit->escapeValues();
                    $lit->loadObject($where, null, null, $ljoin);
                    $lit->unescapeValues();
                }
                break;
        }

        // Affectation du lit
        $affectation->lit_id = $lit->_id;

        // Affectation de l'UF hébergement
        $date_deb = $affectation->_id ? CMbDT::date($affectation->sortie) : CMbDT::date($newVenue->sortie);
        $date_fin = $affectation->_id ? CMbDT::date($affectation->entree) : CMbDT::date($newVenue->entree);
        $uf       = CUniteFonctionnelle::getUF(
            $this->queryTextNode("PL.1", $node),
            "hebergement",
            $newVenue->group_id,
            $date_deb,
            $date_fin
        );

        if (!$uf->_id) {
            return;
        }

        $affectation->uf_hebergement_id = $uf->_id;

        // Affectation du service (couloir)
        if (!$affectation->lit_id) {
            $affectation_uf               = new CAffectationUniteFonctionnelle();
            $affectation_uf->uf_id        = $uf->_id;
            $affectation_uf->object_class = "CService";
            $affectation_uf->loadMatchingObject();

            $affectation->service_id = $affectation_uf->object_id;
        }
    }

    /**
     * Enregistrement UF médicale et/ou soins sur le séjour
     *
     * @param array   $data     Datas
     * @param CSejour $newVenue Admit
     *
     * @return null
     * @throws Exception
     */
    function storeUFMedicaleSoinsSejour($data, $newVenue)
    {
        $sender = $this->_ref_sender;

        $uf_med                   = $this->mappingUFMedicale($data, $newVenue);
        $newVenue->uf_medicale_id = $uf_med ? $uf_med->_id : null;

        $uf_soins              = $this->mappingUFSoins($data, $newVenue);
        $newVenue->uf_soins_id = $uf_soins ? $uf_soins->_id : null;

        // On ne check pas la cohérence des dates des consults/intervs
        $newVenue->_skip_date_consistencies = true;
        $newVenue->_eai_sender_guid         = $sender->_guid;

        if ($msgVenue = $newVenue->store()) {
            return $msgVenue;
        }

        return null;
    }

    /**
     * Mapping de l'UF médicale
     *
     * @param array        $data        Datas
     * @param CSejour      $newVenue    Séjour
     * @param CAffectation $affectation Affectation
     *
     * @return CUniteFonctionnelle|null
     * @throws Exception
     */
    function mappingUFMedicale($data, CSejour $newVenue, CAffectation $affectation = null)
    {
        if (!array_key_exists("ZBE", $data)) {
            return null;
        }

        $uf_type = $this->_ref_sender->_configs["handle_ZBE_7"];
        // si le ZBE.7 possède l'uf de soins on prend le ZBE.8
        $number = $uf_type == "soins" ? "8" : "7";
        if (!($ZBE_7 = $this->queryNode("ZBE.$number", $data["ZBE"]))) {
            return null;
        }

        $date_deb = $affectation && $affectation->_id ? CMbDT::date($affectation->sortie) : CMbDT::date(
            $newVenue->sortie
        );
        $date_fin = $affectation && $affectation->_id ? CMbDT::date($affectation->entree) : CMbDT::date(
            $newVenue->entree
        );

        return CUniteFonctionnelle::getUF(
            $this->queryTextNode("XON.10", $ZBE_7),
            "medicale",
            $newVenue->group_id,
            $date_deb,
            $date_fin
        );
    }

    /**
     * Mapping de l'UF de soins
     *
     * @param array        $data        Datas
     * @param CSejour      $newVenue    Séjour
     * @param CAffectation $affectation Affectation
     *
     * @return CUniteFonctionnelle|null
     * @throws Exception
     */
    function mappingUFSoins($data, CSejour $newVenue, CAffectation $affectation = null)
    {
        if (!array_key_exists("ZBE", $data)) {
            return null;
        }

        $uf_type = $this->_ref_sender->_configs["handle_ZBE_8"];
        $number  = $uf_type == "medicale" ? "7" : "8";
        if (!($ZBE_8 = $this->queryNode("ZBE.$number", $data["ZBE"]))) {
            return null;
        }

        $date_deb = $affectation && $affectation->_id ? CMbDT::date($affectation->sortie) : CMbDT::date(
            $newVenue->sortie
        );
        $date_fin = $affectation && $affectation->_id ? CMbDT::date($affectation->entree) : CMbDT::date(
            $newVenue->entree
        );

        return CUniteFonctionnelle::getUF(
            $this->queryTextNode("XON.10", $ZBE_8),
            "soins",
            $newVenue->group_id,
            $date_deb,
            $date_fin
        );
    }

    /**
     * Enregistrement de la grossesse
     *
     * @param CSejour $newVenue Admit
     *
     * @return null|string|void
     * @throws Exception
     */
    function storeGrossesse(CSejour $newVenue)
    {
        $sender = $this->_ref_sender;

        if (!$sender->_configs["create_grossesse"]) {
            return null;
        }

        if ($newVenue->type_pec != "O") {
            return null;
        }

        $grossesse = $newVenue->loadRefGrossesse();

        if (!$grossesse->_id) {
            $patient   = $newVenue->loadRefPatient();
            $grossesse = $patient->loadLastGrossesse();
            if (!$grossesse->_id) {
                $grossesse->parturiente_id   = $newVenue->patient_id;
                $grossesse->group_id         = $newVenue->group_id;
                $grossesse->terme_prevu      = CMbDT::date($newVenue->sortie);
                $grossesse->_eai_sender_guid = $sender->_guid;
                if ($msg = $grossesse->store()) {
                    return $msg;
                }
            }
        }

        $newVenue->grossesse_id = $grossesse->_id;
        // On ne check pas la cohérence des dates des consults/intervs
        $newVenue->_skip_date_consistencies = true;
        $newVenue->_eai_sender_guid         = $sender->_guid;
        if ($msg = $newVenue->store()) {
            return $msg;
        }

        return null;
    }

    /**
     * Mapping et enregistrement de la naissance
     *
     * @param CSejour $newVenue Admit
     * @param array   $data     Datas
     *
     * @return string|null
     * @throws Exception
     */
    function mapAndStoreNaissance(CSejour $newVenue, $data)
    {
        if ($this->queryTextNode("PV1.4", $data["PV1"]) != "N") {
            return null;
        }

        // Récupération du séjour de la maman
        if (!$mother_AN = $this->getANMotherIdentifier($data["PID"])) {
            return CAppUI::tr("CHL7Event-E227");
        }

        $sender      = $this->_ref_sender;
        $idex_mother = CIdSante400::getMatch("CSejour", $sender->_tag_sejour, $mother_AN);
        if (!$idex_mother->_id) {
            return CAppUI::tr("CHL7Event-E228");
        }

        $sejour_mother = new CSejour();
        $sejour_mother->load($idex_mother->object_id);

        // Récupération de l'IPP de la maman
        if (!$mother_PI = $this->getPIMotherIdentifier($data["PID"])) {
            return CAppUI::tr("CHL7Event-E229");
        }

        if (CIdSante400::getMatch(
                "CPatient",
                $sender->_tag_patient,
                $mother_PI
            )->object_id != $sejour_mother->patient_id) {
            return CAppUI::tr("CHL7Event-E230");
        }

        $naissance                   = new CNaissance();
        $naissance->sejour_enfant_id = $newVenue->_id;
        $naissance->sejour_maman_id  = $sejour_mother->_id;
        $naissance->grossesse_id     = $sejour_mother->grossesse_id;
        $naissance->loadMatchingObject();

        $naissance->rang = $this->queryTextNode("PID.25", $data["PID"]);

        // On récupère l'entrée réelle ssi msg A01 pour indiquer l'heure de la naissance
        if ($this->_ref_exchange_hl7v2->code == "A01") {
            $naissance->date_time = $this->queryTextNode("PV1.44", $data["PV1"]);
        }

        // Notifier les autres destinataires autre que le sender
        $naissance->_eai_sender_guid = $sender->_guid;

        return $naissance->store();
    }

    /**
     * Handle event A02 - transfer a patient
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleA02(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        // Récupérer données de la mutation
        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Recherche du séjour par différents identifiants possibles
     *
     * @param CSejour $newVenue Admit
     * @param array   $data     Datas
     *
     * @return bool
     * @throws Exception
     */
    function admitFound(CSejour $newVenue, $data)
    {
        $sender  = $this->_ref_sender;
        $venueAN = $this->getVenueAN($sender, $data);

        $NDA = new CIdSante400();
        if ($venueAN) {
            $NDA = CIdSante400::getMatch("CSejour", $sender->_tag_sejour, $venueAN);
        }

        if ($NDA->_id) {
            $error_code = "";

            if ($this->isAmbiguousNDA($newVenue, $data, $NDA, $error_code)) {
                return false;
            }

            if ($newVenue->_id) {
                return true;
            }
        }

        $venueRI = CValue::read($data['admitIdentifiers'], "RI");
        if ($newVenue->load($venueRI)) {
            // Si on retrouve le séjour par notre identifiant mais qu'on reçoit un NDA
            CEAISejour::storeNDA($NDA, $newVenue, $sender);

            return true;
        }

        $venueVN = CValue::read($data['admitIdentifiers'], "VN");
        if ($venueVN) {
            return $this->getSejourByVisitNumber($newVenue, $data);
        }

        return false;
    }

    /**
     * Mapping et enregistrement de la venue
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function mapAndStoreVenue(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $sender         = $this->_ref_sender;
        $event_code     = $exchange_hl7v2->code;

        // Mapping du séjour
        $this->mappingVenue($data, $newVenue);

        // Notifier les autres destinataires autre que le sender
        $newVenue->_eai_sender_guid = $sender->_guid;
        // On ne check pas la cohérence des dates des consults/intervs
        $newVenue->_skip_date_consistencies = true;

        // On ne synchronise pas le séjour pour une modification dans un premier temps pour traiter le mouvement
        if ($event_code == "Z99") {
            $newVenue->_no_synchro_eai = true;
        }

        if ($msgVenue = $newVenue->store()) {
            return $exchange_hl7v2->setAckAR($ack, "E201", $msgVenue, $newVenue);
        }

        // Mapping du mouvement
        $return_movement = $this->mapAndStoreMovement($ack, $newVenue, $data);
        if (is_string($return_movement)) {
            return $return_movement;
        }
        $movement = $return_movement;

        // On re-synchronise le séjour ayant subi une modification
        if ($event_code == "Z99") {
            // Est-ce que le mouvement est bien le dernier ?
            // On prend ceux qui ne sont pas annulés
            $where["cancel"] = " = '0'";

            $newVenue->loadRefsMovements($where);

            if ($newVenue->_ref_last_movement->_id == $movement->_id) {
                // on affecte le praticien
                $newVenue->praticien_id = $this->_doctor_id;

                // Notifier les autres destinataires autre que le sender
                $newVenue->_eai_sender_guid = $sender->_guid;
                // On ne check pas la cohérence des dates des consults/intervs
                $newVenue->_skip_date_consistencies = true;

                // On réactive la synchro
                $newVenue->_no_synchro_eai = false;

                if ($msgVenue = $newVenue->store()) {
                    return $exchange_hl7v2->setAckAR($ack, "E201", $msgVenue, $newVenue);
                }
            }
        }

        // Mapping de l'affectation
        $return_affectation = $this->mapAndStoreAffectation($newVenue, $data, $movement);
        if (is_string($return_affectation)) {
            return $exchange_hl7v2->setAckAR($ack, "E208", $return_affectation, $newVenue);
        }
        $affectation = $return_affectation;

        // Attribution de l'affectation au mouvement
        if ($movement && $affectation && $affectation->_id) {
            $movement->affectation_id   = $affectation->_id;
            $movement->_eai_sender_guid = $sender->_guid;
            $movement->store();
            //if ($msg = $movement->store()) {
            //  return $exchange_hl7v2->setAckAR($ack, "E208", $msg, $newVenue);
            //}
        }

        // Dans le cas d'une grossesse
        if ($return_grossesse = $this->storeGrossesse($newVenue)) {
            return $exchange_hl7v2->setAckAR($ack, "E211", $return_grossesse, $newVenue);
        }

        // Création du VN, voir de l'objet
        if ($msgVN = $this->createObjectByVisitNumber($newVenue, $data)) {
            return $exchange_hl7v2->setAckAR($ack, "E210", $msgVN, $newVenue);
        }

        $codes   = ["I202", "I226"];
        $comment = CEAISejour::getComment($newVenue);

        return $exchange_hl7v2->setAckAA($ack, $codes, $comment, $newVenue);
    }

    /**
     * Handle event A03 - discharge/end visit
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleA03(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        // Récupérer données de la sortie
        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Handle event A04 - register a patient
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleA04(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création possible
        return $this->handleA05($ack, $newVenue, $data);
    }

    /**
     * Handle event A06 - change an outpatient to an inpatient
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleA06(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Handle event A07 - change an inpatient to an outpatient
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleA07(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Handle event A08 - update patient information
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleA08(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Handle event A11 - cancel admit / visit notification
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleA11(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        // Suppression de l'entrée réelle / mode d'entrée
        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Handle event A12 - cancel transfer
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleA12(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        // Suppression de l'affectation
        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Handle event A13 - cancel discharge / end visit
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleA13(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        // Suppression sortie réelle, mode de sortie, ...
        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Handle event A14 - pending admit
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleA14(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création possible
        return $this->handleA05($ack, $newVenue, $data);
    }

    /**
     * Handle event A15 - pending transfer
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleA15(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Handle event A16 - pending discharge
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleA16(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Handle event A21 - patient goes on a "leave of absence"
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleA21(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Handle event A22 - patient returns from a "leave of absence"
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleA22(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Handle event A25 - cancel pending discharge
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleA25(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Handle event A26 - cancel pending transfer
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleA26(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Handle event A27 - cancel pending admit
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleA27(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Handle event A38 - cancel pre-admit
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleA38(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Handle event A52 - cancel leave of absence for a patient
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleA52(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Handle event A53 - cancel patient returns from a leave of absence
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleA53(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Handle event A54 - change attending doctor
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleA54(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Handle event A55 - cancel change attending doctor
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleA55(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Handle event Z80 - changement d'UF médicale
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleZ80(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Handle event Z80 - annulation changement d'UF médicale
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleZ81(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Handle event Z84 - changement d'UF de soins
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleZ84(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Handle event Z85 - annulation changement d'UF de soins
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleZ85(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Handle event Z99 - admit information update
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CSejour            $newVenue Admit
     * @param array              $data     Datas
     *
     * @return string
     * @throws CHL7v2Exception
     */
    function handleZ99(CHL7Acknowledgment $ack, CSejour $newVenue, $data)
    {
        // Mapping venue - création impossible
        if (!$this->admitFound($newVenue, $data)) {
            return $this->_ref_exchange_hl7v2->setAckAR($ack, "E204", null, $newVenue);
        }

        return $this->mapAndStoreVenue($ack, $newVenue, $data);
    }

    /**
     * Trash NDA
     *
     * @return bool
     */
    function trashNDA()
    {
        return true;
    }

    /**
     * Récupération du PV1
     *
     * @param DOMNode $node     PV1 Node
     * @param CSejour $newVenue Admit
     * @param array   $data     Datas
     *
     * @return void
     * @throws Exception
     */
    function getPV1(DOMNode $node, CSejour $newVenue, $data = [])
    {
        // Classe de patient
        $this->getPatientClass($node, $newVenue, $data);

        // Type de l'admission
        $this->getAdmissionType($node, $newVenue);

        // Médecin responsable
        $this->getAttendingDoctor($node, $newVenue);

        // Médecin adressant
        $this->getReferringDoctor($node, $newVenue);

        // Médecin de famille
        $this->getConsultingDoctor($node, $newVenue);

        // Discipline médico-tarifaire
        $this->getHospitalService($node, $newVenue);

        // Mode d'entrée
        $this->getAdmitSource($node, $newVenue);

        // Code tarif su séjour
        $this->getFinancialClass($node, $newVenue);

        // Type d'activité, mode de traitement
        $this->getChargePriceIndicator($node, $newVenue);

        // Demande de chambre particulière
        $this->getCourtesyCode($node, $newVenue);

        // Mode d'entrée personnalisable - Combinaison du ZFM
        $this->getDischargeDisposition($node, $newVenue);

        // Etablissement de destination
        $this->getDischargedToLocation($node, $newVenue);

        // Statut du dossier administratif
        $this->getAccountStatus($node, $newVenue);

        // Entrée / Sortie réelle du séjour
        $this->getAdmitDischarge($node, $newVenue);

        // Numéro de rang
        $this->getAlternateVisitID($node, $newVenue);

        // Indicateur de visite
        $this->getVisitIndicator($node, $newVenue);
    }

    /**
     * Récupération de la classe du patient
     *
     * @param DOMNode $node     PV1 Node
     * @param CSejour $newVenue Admit
     * @param array   $data     Datas
     *
     * @return void
     * @throws Exception
     */
    function getPatientClass(DOMNode $node, CSejour $newVenue, $data = [])
    {
        $patient_class = CHL7v2TableEntry::mapFrom("4", $this->queryTextNode("PV1.2", $node));

        $type = $patient_class ? $patient_class : "comp";

        if ($data && array_key_exists("ZBE", $data)) {
            $uf_med = $this->mappingUFMedicale($data, $newVenue);
            if ($uf_med && $uf_med->type_sejour) {
                $type = $uf_med->type_sejour;
            }
        }

        $newVenue->type = $type;
    }

    /**
     * Récupération du type d'admission
     *
     * @param DOMNode $node     PV1 Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getAdmissionType(DOMNode $node, CSejour $newVenue)
    {
        $admission_type = $this->queryTextNode("PV1.4", $node);
        $sender         = $this->_ref_sender;

        if ($sender->_configs["handle_PV1_4"] === 'charge_price_indicator') {
            if (!$value = CHL7v2TableEntry::mapFrom("0007", $admission_type)) {
                return;
            }

            $charge           = new CChargePriceIndicator();
            $charge->code     = $value;
            $charge->actif    = 1;
            $charge->group_id = $sender->group_id;
            $charge->loadMatchingObject();

            // On affecte le type d'activité reçu sur le séjour
            $newVenue->charge_id = $charge->_id;

            // Type PEC
            $newVenue->type_pec = $charge->type_pec;

            // Si le type du séjour est différent de celui du type d'activité on modifie son type
            if ($charge->type && $charge->type != $newVenue->type) {
                $newVenue->type = $charge->type;
            }

            return;
        }

        // Gestion de l'accouchement maternité
        if ($admission_type == "L") {
            $newVenue->type_pec = "O";
        }
    }

    /**
     * Récupération du médecin responsable
     *
     * @param DOMNode $node     PV1 Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    private function getAttendingDoctor(DOMNode $node, CSejour $newVenue): void
    {
        $event_code = $this->_ref_exchange_hl7v2->code;
        $PV1_7      = $this->query("PV1.7", $node);

        // On ne récupère pas le praticien dans le cas où l'on a un séjour d'urgences et que la config est à non
        if ($newVenue->type == "urg" && !$this->_ref_sender->_configs["handle_PV1_7"]) {
            return;
        }

        $doctor_id = $this->getDoctor($PV1_7, new CMediusers());
        // On ne change pas le praticien si celui-ci existe sur le séjour et n'est pas présent dans le message reçu
        if (!$doctor_id && $newVenue->praticien_id) {
            return;
        }

        // Dans le cas ou la venue ne contient pas de medecin responsable
        // Attribution d'un medecin indeterminé
        if (!$doctor_id && !$newVenue->praticien_id) {
            $doctor_id = $this->createIndeterminateDoctor();
        }

        // On ne synchronise pas dans le cas d'une modification
        if ($event_code == "Z99") {
            $this->_doctor_id = $doctor_id;

            return;
        }

        $newVenue->praticien_id = $doctor_id;
    }

    /**
     * Création du "médecin" indéterminé
     *
     * @return integer
     * @throws Exception
     */
    function createIndeterminateDoctor()
    {
        $sender = $this->_ref_sender;

        $user                 = new CUser();
        $user->user_last_name = CAppUI::conf("hl7 indeterminateDoctor") . " $sender->group_id";
        if (!$user->loadMatchingObjectEsc()) {
            $mediuser                  = new CMediusers();
            $mediuser->_user_last_name = $user->user_last_name;

            return $this->createDoctor($mediuser, $sender->group_id);
        }

        return $user->loadRefMediuser()->_id;
    }

    /**
     * Récupération du médecin adressant
     *
     * @param DOMNode $node     PV1 Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    private function getReferringDoctor(DOMNode $node, CSejour $newVenue): void
    {
        $sender = $this->_ref_sender;
        $PV1_8  = $this->queryNodes("PV1.8", $node);
        if (!$PV1_8 || $PV1_8->length === 0) {
            return;
        }

        $medecin_id = $this->getDoctor($PV1_8, new CMedecin());
        switch ($sender->_configs["handle_PV1_8"]) {
            // Médecin traitant
            case 'traitant':
                $patient                   = $newVenue->loadRefPatient();
                $patient->medecin_traitant = $medecin_id;
                $patient->_eai_sender_guid = $sender->_guid;
                $patient->store();
                break;

            // Médecin adressant
            default:
                $newVenue->adresse_par_prat_id = $medecin_id;
                break;
        }
    }

    /**
     * Récupération du médecin de famille
     *
     * @param DOMNode $node     PV1 Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    private function getConsultingDoctor(DOMNode $node, CSejour $newVenue): void
    {
        $sender = $this->_ref_sender;
        $PV1_9  = $this->queryNodes("PV1.9", $node);
        if (!$PV1_9 || $PV1_9->length === 0) {
            return;
        }

        switch ($sender->_configs["handle_PV1_9"]) {
            // Médecin de famille - correspondant médical
            case 'famille':
                $medecin_id = $this->getDoctor($PV1_9, new CMedecin());
                if (!$medecin_id) {
                    return;
                }
                $correspondant             = new CCorrespondant();
                $patient                   = $newVenue->loadRefPatient();
                $correspondant->patient_id = $patient->_id;
                $correspondant->medecin_id = $medecin_id;
                if (!$correspondant->loadMatchingObjectEsc()) {
                    // Notifier les autres destinataires autre que le sender
                    $correspondant->_eai_sender_guid = $sender->_guid;
                    $correspondant->store();
                }
                break;

            default:
        }
    }

    /**
     * Récupération de la discipline médico-tarifaire
     *
     * @param DOMNode $node     PV1 Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getHospitalService(DOMNode $node, CSejour $newVenue)
    {
        $sender = $this->_ref_sender;
        $PV1_10 = $this->queryTextNode("PV1.10", $node);

        if (!$PV1_10) {
            return null;
        }

        // Hospital Service
        switch ($sender->_configs["handle_PV1_10"]) {
            // idex du service
            case 'service':
                $newVenue->service_id = CIdSante400::getMatch("CService", $sender->_tag_service, $PV1_10)->object_id;
                break;

            // finess
            case 'finess':
                return null;

            // Discipline médico-tarifaire
            default:
                $discipline = new CDiscipline();
                $discipline->load($PV1_10);

                $newVenue->discipline_id = $discipline->_id;
                break;
        }
    }

    /**
     * Récupération du mode d'entrée
     *
     * @param DOMNode $node     PV1 Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getAdmitSource(DOMNode $node, CSejour $newVenue)
    {
        if (!($admit_source = $this->queryTextNode("PV1.14", $node))) {
            return;
        }

        $sender = $this->_ref_sender;

        // Mode d'entrée personnalisable
        if (CAppUI::conf("dPplanningOp CSejour use_custom_mode_entree")) {
            $mode_entree           = new CModeEntreeSejour();
            $mode_entree->code     = $admit_source;
            $mode_entree->group_id = $sender->group_id;
            $mode_entree->actif    = 1;
            $mode_entree->loadMatchingObject();

            $newVenue->mode_entree_id = $mode_entree->_id;
        }

        // Admit source
        switch ($sender->_configs["handle_PV1_14"]) {
            // Combinaison du ZFM
            // ZFM.1 + ZFM.3
            case 'ZFM':
                $newVenue->mode_entree = $admit_source[0];
                if (strlen($admit_source) == 2) {
                    $newVenue->provenance = $admit_source[1];
                }

                break;

            default:
        }
    }

    /**
     * Récupération du code tarif du séjour
     *
     * @param DOMNode $node     PV1 Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getFinancialClass(DOMNode $node, CSejour $newVenue)
    {
        $sender = $this->_ref_sender;
        if ($sender->_configs["handle_PV1_20"] == "none") {
            return;
        }

        $systeme_presta = CAppUI::conf("dPhospi prestations systeme_prestations", "CGroups-" . $newVenue->group_id);
        if ($systeme_presta == "standard") {
            return;
        }

        // Uniquement pour les prestas expertes
        $prestation = explode("#", $this->queryTextNode("PV1.20", $node));

        $presta_name = CMbArray::get($prestation, 0);
        $item_name   = CMbArray::get($prestation, 1);

        $item_presta = new CItemPrestation();

        if ($item_name) {
            // Chargement de la prestation journalière
            $presta_journa      = new CPrestationJournaliere();
            $presta_journa->nom = $presta_name;
            $presta_journa->loadMatchingObject();

            $item_presta->object_class = "CPrestationJournaliere";
            $item_presta->object_id    = $presta_journa->_id;
        } else {
            $item_name = $presta_name;
        }

        // Chargement d'un item de prestation
        $item_presta->nom = $item_name;
        $item_presta->loadMatchingObject();

        if (!$item_presta->_id) {
            return;
        }

        $item_liaison = new CItemLiaison();

        $where["item_liaison.sejour_id"] = " = '$newVenue->_id'";

        $item_liaison->loadObject($where);

        if (!$item_liaison->_id) {
            $item_liaison->sejour_id     = $newVenue->_id;
            $item_liaison->prestation_id = $item_presta->object_id;
            $item_liaison->date          = CMbDT::date($newVenue->entree);
        }

        $item_liaison->item_realise_id  = $item_presta->_id;
        $item_liaison->_eai_sender_guid = $sender->_guid;

        $item_liaison->store();
    }

    /**
     * Récupération du type d'activité, mode de traitement
     *
     * @param DOMNode $node     PV1 Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getChargePriceIndicator(DOMNode $node, CSejour $newVenue)
    {
        $PV1_21 = $this->queryTextNode("PV1.21", $node);
        if (!$PV1_21) {
            return;
        }

        $sender = $this->_ref_sender;
        if ($sender->_configs["handle_PV1_4"] === 'charge_price_indicator') {
            return;
        }

        $charge           = new CChargePriceIndicator();
        $charge->code     = $PV1_21;
        $charge->actif    = 1;
        $charge->group_id = $sender->group_id;
        $charge->loadMatchingObject();

        if (!$charge->_id) {
            return;
        }

        // On affecte le type d'activité reçu sur le séjour
        $newVenue->charge_id = $charge->_id;

        // Type PEC
        $newVenue->type_pec = $charge->type_pec;

        // Si le type du séjour est différent de celui du type d'activité on modifie son type
        if ($charge->type && $charge->type != $newVenue->type) {
            $newVenue->type = $charge->type;
        }
    }

    /**
     * Récupération du demande de chambre particulière
     *
     * @param DOMNode $node     PV1 Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getCourtesyCode(DOMNode $node, CSejour $newVenue)
    {
        $value = $this->queryTextNode("PV1.22", $node);

        if ($value === null) {
            return;
        }

        $newVenue->chambre_seule = $this->getBoolean($value);
    }

    /**
     * Récupération de la circonstance de sortie
     *
     * @param DOMNode $node     PV1 Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getDischargeDisposition(DOMNode $node, CSejour $newVenue)
    {
        // Gestion des circonstances de sortie
        if (!($discharge_disposition = $this->queryTextNode("PV1.36", $node))) {
            return;
        }

        $sender = $this->_ref_sender;

        // Mode de sortie personnalisable
        if (CAppUI::conf("dPplanningOp CSejour use_custom_mode_sortie")) {
            $mode_sortie           = new CModeSortieSejour();
            $mode_sortie->code     = $discharge_disposition;
            $mode_sortie->group_id = $sender->group_id;
            $mode_sortie->actif    = 1;
            $mode_sortie->loadMatchingObject();

            $newVenue->mode_sortie_id = $mode_sortie->_id;
        }

        // Admit source
        switch ($sender->_configs["handle_PV1_36"]) {
            // Combinaison du ZFM
            // ZFM.2 + ZFM.4
            case 'ZFM':
                $newVenue->provenance = $discharge_disposition[0];
                if (strlen($discharge_disposition) == 2) {
                    $newVenue->destination = $discharge_disposition[1];
                }

                break;

            default:
        }
    }

    /**
     * Récupération de l'établissement de destination
     *
     * @param DOMNode $node     PV1 Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getDischargedToLocation(DOMNode $node, CSejour $newVenue)
    {
        if (!$finess = $this->queryTextNode("PV1.37/DLD.1", $node)) {
            return;
        }

        $etab_ext         = new CEtabExterne();
        $etab_ext->finess = $finess;
        if (!$etab_ext->loadMatchingObjectEsc()) {
            return;
        }

        $newVenue->etablissement_sortie_id = $etab_ext->_id;
    }

    /**
     * Récupération du statut du dossier administratif
     *
     * @param DOMNode $node     PV1 Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getAccountStatus(DOMNode $node, CSejour $newVenue)
    {
        $last_seance = $this->queryTextNode("PV1.41", $node);
        if ($last_seance == "D") {
            $newVenue->last_seance = "1";
        }
    }

    /**
     * Récupération de la date d'entrée réelle/prévue
     *
     * @param DOMNode $node     PV1 Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getAdmitDischarge(DOMNode $node, CSejour $newVenue)
    {
        $event_code = $this->_ref_exchange_hl7v2->code;

        $PV1_44 = $this->queryTextNode("PV1.44", $node);
        $PV1_45 = $this->queryTextNode("PV1.45", $node);

        // On récupère l'entrée réelle ssi msg == A01 || A04
        if ($event_code == "A01" || $event_code == "A04") {
            $newVenue->entree_reelle = $PV1_44;
        }

        // On récupére la sortie réelle ssi msg == A03
        if ($event_code == "A03") {
            $newVenue->sortie_reelle = $PV1_45;
        }

        // Dans tous les autres cas on synchronise l'entrée et la sortie réelle ssi on a déjà la donnée dans Mediboard
        if ($newVenue->entree_reelle) {
            $newVenue->entree_reelle = $PV1_44;
        }

        if ($newVenue->sortie_reelle) {
            $newVenue->sortie_reelle = $PV1_45;
        }

        // On récupére l'entrée réelle en entrée prévue ssi msg == A05
        if (($event_code == "A05") && !$newVenue->entree_reelle && !$newVenue->entree_prevue) {
            $newVenue->entree_prevue = $PV1_44;
        }

        // Cas spécifique de certains segments
        // A11 : on supprime la date d'entrée réelle && on met en trash le numéro de dossier
        if ($event_code == "A11") {
            $newVenue->entree_reelle = "";

            $where                          = [];
            $where["original_trigger_code"] = " = 'A05'";

            $movements = $newVenue->loadRefsMovements($where);
            if (empty($movements)) {
                $newVenue->_generate_NDA = false;

                $newVenue->trashNDA();
            }
        }

        // A38 : on met en trash le numéro de dossier
        if ($event_code == "A38") {
            $newVenue->_generate_NDA = false;

            $newVenue->trashNDA();
        }

        // A13 : on supprime la date de sortie réelle
        if ($event_code == "A13") {
            $newVenue->sortie_reelle = "";
        }
    }

    /**
     * Récupération de l'indicateur d'une venue
     *
     * @param DOMNode $node     PV1 Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getVisitIndicator(DOMNode $node, CSejour $newVenue)
    {
        if ($this->queryTextNode("PV1.51", $node) == "V") {
            $newVenue->hospit_de_jour = 1;
        }
    }

    /**
     * Récupération du PV2
     *
     * @param DOMNode $node     Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     */
    function getPV2(DOMNode $node, CSejour $newVenue)
    {
        // Entrée / Sortie prévue du séjour
        $this->getExpectedAdmitDischarge($node, $newVenue);

        // Visit description
        $this->getVisitDescription($node, $newVenue);

        // Mode de transport d'entrée
        $this->getModeArrivalCode($node, $newVenue);
    }

    /**
     * Récupération de la d'entrée prévue
     *
     * @param DOMNode $node     PV2 Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getExpectedAdmitDischarge(DOMNode $node, CSejour $newVenue)
    {
        $entree_prevue = $this->queryTextNode("PV2.8", $node);
        $sortie_prevue = $this->queryTextNode("PV2.9", $node);

        if (!$entree_prevue) {
            $entree_prevue = $newVenue->entree_reelle ? $newVenue->entree_reelle : $newVenue->entree_prevue;
        }
        $newVenue->entree_prevue = $entree_prevue;
        if (!$sortie_prevue && !$newVenue->sortie_prevue) {
            $addDateTime = CAppUI::gconf("dPplanningOp CSejour sortie_prevue " . $newVenue->type);
            switch ($addDateTime) {
                case "1/4":
                    $addDateTime = "00:15:00";
                    break;
                case "1/2":
                    $addDateTime = "00:30:00";
                    break;
                default:
                    $addDateTime = $addDateTime . ":00:00";
            }
            $newVenue->sortie_prevue =
                CMbDT::addDateTime(
                    $addDateTime,
                    $newVenue->entree_reelle ? $newVenue->entree_reelle : $newVenue->entree_prevue
                );
        } elseif (!$sortie_prevue && $newVenue->sortie_prevue) {
            // On ne modifie pas la sortie de Mediboard si on ne l'a pas dans le message
        } elseif ($sortie_prevue && preg_match("/^\d{4}-\d\d-\d\d( 00:00:00)?$/", $sortie_prevue)) {
            $newVenue->sortie_prevue = CMbDT::date($sortie_prevue) . " 00:00:00";
        } elseif ($sortie_prevue && preg_match("/\d{4}-\d\d-\d\d \d\d:\d\d:\d\d/", $sortie_prevue)) {
            $newVenue->sortie_prevue = $sortie_prevue;
        } else {
            $newVenue->sortie_prevue = $newVenue->sortie_reelle ? $newVenue->sortie_reelle : $newVenue->sortie_prevue;
        }

        // On récupère l'entrée et sortie réelle ssi !entree_prevue && !sortie_prevue
        $parentNode = $node->parentNode;
        if (!$newVenue->entree_prevue) {
            $newVenue->entree_prevue = $this->queryTextNode("PV1.44", $this->queryNode("PV1", $parentNode));
        }

        if (!$newVenue->sortie_prevue) {
            $newVenue->sortie_prevue = $this->queryTextNode("PV1.45", $this->queryNode("PV1", $parentNode));
        }

        // Si les dates entrées/sorties sont incohérentes
        $sender = $this->_ref_sender;
        if ($sender->_configs["control_date"] == "permissif") {
            $newVenue->entree_prevue = min($newVenue->entree_prevue, $newVenue->sortie_prevue);
            $newVenue->sortie_prevue = max($newVenue->entree_prevue, $newVenue->sortie_prevue);
        }
    }

    /**
     * Récupération de la description de la visite
     *
     * @param DOMNode $node     PV2 Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getVisitDescription(DOMNode $node, CSejour $newVenue)
    {
        $sender = $this->_ref_sender;

        switch ($sender->_configs["handle_PV2_12"]) {
            case "none":
                return null;

            default:
                $newVenue->libelle = $this->queryTextNode("PV2.12", $node);

                break;
        }
    }

    /**
     * Récupération du mode de transport d'entrée
     *
     * @param DOMNode $node     PV2 Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getModeArrivalCode(DOMNode $node, CSejour $newVenue)
    {
        $mode_arrival_code = $this->queryTextNode("PV2.38", $node);

        $newVenue->transport = CHL7v2TableEntry::mapFrom("0430", $mode_arrival_code);
    }

    /**
     * Récupération du segment ZFD
     *
     * @param DOMNode $node     Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getZFD(DOMNode $node, CSejour $newVenue)
    {
        $sender = $this->_ref_sender;

        // Date lunaire
        $jour_lunaire  = $this->queryTextNode("ZFD.1/NA.1", $node);
        $mois_lunaire  = $this->queryTextNode("ZFD.1/NA.2", $node);
        $annee_lunaire = $this->queryTextNode("ZFD.1/NA.3", $node);

        if ($jour_lunaire && $mois_lunaire && $annee_lunaire) {
            $patient                   = $newVenue->_ref_patient;
            $jour_lunaire              = str_pad($jour_lunaire, 2, 0, STR_PAD_LEFT);
            $mois_lunaire              = str_pad($mois_lunaire, 2, 0, STR_PAD_LEFT);
            $patient->naissance        = "$annee_lunaire-$mois_lunaire-$jour_lunaire";
            $patient->_eai_sender_guid = $sender->_guid;
            $patient->store();
        }
    }

    /**
     * Récupération du segment ZFM
     *
     * @param DOMNode $node     Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     */
    function getZFM(DOMNode $node, CSejour $newVenue)
    {
        // Mode entrée PMSI
        $this->getModeEntreePMSI($node, $newVenue);

        // Mode de sortie PMSI
        $this->getModeSortiePMSI($node, $newVenue);

        // Mode de provenance PMSI
        $this->getModeProvenancePMSI($node, $newVenue);

        // Mode de destination PMSI
        $this->getModeDestinationPMSI($node, $newVenue);
    }

    /**
     * Récupération du mode d'entrée PMSI
     *
     * @param DOMNode $node     ZFM Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getModeEntreePMSI(DOMNode $node, CSejour $newVenue)
    {
        $newVenue->mode_entree = $this->queryTextNode("ZFM.1", $node);
    }

    /**
     * Récupération du mode de sortie PMSI
     *
     * @param DOMNode $node     ZFM Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getModeSortiePMSI(DOMNode $node, CSejour $newVenue)
    {
        $newVenue->mode_sortie = CHL7v2TableEntry::mapFrom("9001", $this->queryTextNode("ZFM.2", $node));
    }

    /**
     * Récupération du mode de provenance PMSI
     *
     * @param DOMNode $node     ZFM Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getModeProvenancePMSI(DOMNode $node, CSejour $newVenue)
    {
        $ZFM_3 = $this->queryTextNode("ZFM.3", $node);
        if ($ZFM_3 == 0) {
            $ZFM_3 = null;
        }
        $newVenue->provenance = $ZFM_3;
    }

    /**
     * Récupération du mode de destination PMSI
     *
     * @param DOMNode $node     ZFM Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getModeDestinationPMSI(DOMNode $node, CSejour $newVenue)
    {
        $ZFM_4 = $this->queryTextNode("ZFM.4", $node);
        if ($ZFM_4 == 0) {
            $ZFM_4 = null;
        }
        $newVenue->destination = $ZFM_4;
    }

    /**
     * Récupération du segment ZFP
     *
     * @param DOMNode $node     Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getZFP(DOMNode $node, CSejour $newVenue)
    {
        $sender = $this->_ref_sender;

        // Catégorie socioprofessionnelle
        if ($csp = $this->queryTextNode("ZFP.2", $node)) {
            $patient                   = $newVenue->_ref_patient;
            $patient->csp              = $csp;
            $patient->_eai_sender_guid = $sender->_guid;
            $patient->store();
        }
    }

    /**
     * Récupération du segment ZFV
     *
     * @param DOMNode $node     Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getZFV(DOMNode $node, CSejour $newVenue)
    {
        // Etablissement de provenance
        $this->getEtablissementProvenance($node, $newVenue);
    }

    /**
     * Récupération de l'établissement de provenance
     *
     * @param DOMNode $node     ZFV Node
     * @param CSejour $newVenue Admit
     *
     * @return void
     * @throws Exception
     */
    function getEtablissementProvenance(DOMNode $node, CSejour $newVenue)
    {
        if (!$finess = $this->queryTextNode("ZFV.1/DLD.1", $node)) {
            return;
        }

        $etab_ext         = new CEtabExterne();
        $etab_ext->finess = $finess;
        if (!$etab_ext->loadMatchingObjectEsc()) {
            return;
        }

        $newVenue->etablissement_entree_id = $etab_ext->_id;
    }
}
