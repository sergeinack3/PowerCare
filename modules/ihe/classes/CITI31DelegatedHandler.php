<?php
/**
 * @package Mediboard\Ihe
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ihe;

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CStoredObject;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Hl7\CReceiverHL7v2;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentZBE;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CMovement;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Maternite\CNaissance;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\Urgences\CRPU;

/**
 * Class CITI31DelegatedHandler
 * ITI31 Delegated Handler
 */
class CITI31DelegatedHandler extends CITIDelegatedHandler
{
    /**
     * @var array
     */
    static $handled = ["CSejour", "CAffectation"];
    /**
     * @var string
     */
    protected $profil = "PAM";
    /**
     * @var string
     */
    protected $message = "ADT";
    /**
     * @var string
     */
    protected $transaction = "ITI31";

    /**
     * @var array
     */
    static $inpatient = ["comp", "ssr", "psy", "seances", "consult", "ambu"];

    /**
     * @var array
     */
    static $outpatient = ["urg", "exte"];

    /**
     * Get patient class for admit inpatient
     *
     * @param CGroups $group Group
     *
     * @return array
     */
    static function getOutpatient(CGroups $group)
    {
        if ($outpatient_sejour_type = CAppUI::conf("ihe ITI outpatient_sejour_type", $group)) {
            return explode("|", $outpatient_sejour_type);
        }

        return self::$outpatient;
    }

    /**
     * Get patient class for register outpatient
     *
     * @param CGroups $group Group
     *
     * @return array
     */
    static function getInpatient(CGroups $group)
    {
        return self::$inpatient;
    }

    /**
     * @inheritDoc
     */
    static function isHandled(CStoredObject $mbObject)
    {
        return in_array($mbObject->_class, self::$handled);
    }

    /**
     * Trigger after event store
     *
     * @param CStoredObject $mbObject Object
     *
     * @return void
     */
    function onBeforeStore(CStoredObject $mbObject)
    {
        if (!$this->isHandled($mbObject)) {
            return false;
        }
    }

    /**
     * Trigger after event store
     *
     * @param CStoredObject $mbObject Object
     *
     * @return void|bool
     * @throws CMbException
     */
    function onAfterStore(CStoredObject $mbObject)
    {
        if (!$this->isHandled($mbObject)) {
            return false;
        }

        /** @var CReceiverHL7v2 $receiver */
        $receiver = $mbObject->_receiver;
        $receiver->getInternationalizationCode($this->transaction);

        if ($mbObject->_forwardRefMerging) {
            return null;
        }

        // Traitement Sejour
        if ($mbObject instanceof CSejour) {
            $sejour  = $mbObject;
            $patient = $sejour->loadRefPatient();

            // Si on ne souhaite explicitement pas de synchro
            if ($sejour->_no_synchro_eai) {
                return;
            }

            //  Si on ne g�re pas les s�jours du b�b� et qu'il s'agit d'une naissance on ne transmet pas le s�jour
            if (!$receiver->_configs["send_child_admit"] && $sejour->_naissance) {
                return;
            }

            // Si c'est le s�jour de pr�-admission et que c'est une naissance
            if ($sejour->_naissance && !$sejour->entree_reelle) {
                return;
            }

            // Si le group_id du s�jour est diff�rent de celui du destinataire
            if ($sejour->group_id != $receiver->group_id) {
                return;
            }

            // Cas o� l'on intercepte la modification du champ facturable du s�jour pour r�aliser
            // automatiquement les traitements sur les idex
            $bypass_test = false;
            if ($receiver->_configs["change_idex_for_admit"] && $receiver->_configs["send_no_facturable"] != "1"
                && $sejour->fieldModified("facturable")
            ) {
                $bypass_test = true;
            }

            // Destinataire g�re seulement les non facturables
            if (!$bypass_test && $receiver->_configs["send_no_facturable"] == "0" && $sejour->facturable) {
                return;
            }

            // Destinataire g�re seulement les facturables
            if (!$bypass_test && $receiver->_configs["send_no_facturable"] == "2" && !$sejour->facturable) {
                return;
            }

            // Passage du s�jour d'urgence en hospit, pas de g�n�ration de A06
            if ($sejour->_en_mutation) {
                return;
            }

            // Dans le cas o� le s�jour est annul� et que l'on ne modifie pas ce champ on ne g�n�re pas de message
            if ($sejour->annule && !$sejour->fieldModified("annule")) {
                return false;
            }

            // Si le type de s�jour n'est pas support� par ce destinataire
            if ($exclude_admit_type = $receiver->_configs["exclude_admit_type"]) {
                $exclude_admit_types = explode(",", $exclude_admit_type);
                foreach ($exclude_admit_types as $_exclude_admit_type) {
                    if ($_exclude_admit_type == $sejour->type) {
                        return;
                    }
                }
            }

            // On envoie pas le flux si le patient n'a pas AppFine sauf si le patient_id a �t� modifi� pour pouvoir l'annuler dans AppFine
            if (CModule::getActive("appFineClient")) {
                if ($receiver->_configs['send_evenement_to_mbdmp']) {
                    if (!CAppFineClient::checkRGPD($patient, $receiver->group_id)) {
                        return;
                    }
                }

                if ($receiver->_configs['send_evenement_to_mbdmp'] && !CAppFineClient::loadIdex($patient)->_id
                    && !$sejour->fieldModified("patient_id")
                ) {
                    return;
                }
            }

            $sejour->_ref_hl7_affectation = new CAffectation();
            $sejour->_ref_hl7_movement    = new CMovement();

            // Si on ne g�re les s�jours du b�b� on ne transmet pas s�jour si c'est un s�jour enfant
            if (!$receiver->_configs["send_child_admit"]) {
                $naissance                   = new CNaissance();
                $naissance->sejour_enfant_id = $sejour->_id;
                $naissance->loadMatchingObject();
                if ($naissance->_id) {
                    return;
                }
            }

            // Recherche si on est sur un s�jour de mutation
            $rpu                     = new CRPU();
            $rpu->mutation_sejour_id = $sejour->_id;
            $rpu->loadMatchingObject();

            if ($rpu->_id) {
                $sejour_rpu = $rpu->loadRefSejour();
                if (!$sejour->_cancel_hospitalization && $sejour_rpu->mode_sortie != "mutation") {
                    return;
                }
            }

            $current_affectation = null;
            $code                = null;

            // Cas o� :
            // * on est sur un s�jour d'urgences qui n'est pas le relicat
            // * on est en train de r�aliser la mutation
            /** @var CRPU $rpu */
            $rpu = $sejour->loadRefRPU();
            if ($rpu && $rpu->_id && $rpu->mutation_sejour_id && $rpu->sejour_id != $rpu->mutation_sejour_id && $sejour->fieldModified(
                    "mode_sortie",
                    "mutation"
                )
                && !$sejour->UHCD
            ) {
                $sejour = $rpu->loadRefSejourMutation();
                $sejour->loadRefPatient();
                $sejour->loadLastLog();
                $sejour->_receiver = $receiver;
                $code              = "A06";

                // On r�cup�re l'affectation courante qui n'a pas �t� transmise (affectation suite � la mutation)
                $affectation_hospi = $sejour->getCurrAffectation();
                // Dans le cas o� l'on en trouve pas, on va prendre la derni�re du s�jour
                if (!$affectation_hospi->_id) {
                    $sejour->loadRefsAffectations();
                    $affectation_hospi = $sejour->_ref_last_affectation;
                }
                $sejour->_ref_hl7_affectation = $affectation_hospi;
            } // Dans le cas d'une annulation d'hospitalisation
            elseif ($sejour->fieldModified("type", "urg") && $sejour->_cancel_hospitalization) {
                $sejour->loadRefPatient();
                $sejour->loadLastLog();
                $sejour->_receiver = $receiver;
                $code              = "A07";

                // On r�cup�re l'affectation courante qui n'a pas �t� transmise (affectation suite � la mutation)
                $affectation_hospi = $sejour->getCurrAffectation();
                // Dans le cas o� l'on en trouve pas, on va prendre la derni�re du s�jour
                if (!$affectation_hospi->_id) {
                    $sejour->loadRefsAffectations();
                    $affectation_hospi = $sejour->_ref_last_affectation;
                }
                $sejour->_ref_hl7_affectation = $affectation_hospi;
            } // On est sur le s�jour relicat, on ne synchronise aucun flux
            elseif ($rpu && $rpu->mutation_sejour_id && ($rpu->sejour_id != $rpu->mutation_sejour_id)) {
                return;
            }

            $code = $code ? $code : $this->getCodeSejour($sejour);

            // Dans le cas d'une hospit. de jour, � la cr�ation on doit envoyer un A01 et un A03
            if ((($sejour->_ref_last_log->type == "create") || $sejour->fieldModified("annule", "0")) &&
                ($sejour->_hdj_seance && (CAppUI::gconf("ihe ITI hospi_de_jour", $sejour->group_id) == "A01/A03"))) {
                $code = "A01";

                // Cas o� :
                // * on est l'initiateur du message
                // * le destinataire ne supporte pas le message
                if (!$this->isMessageSupported($this->message, $code, $receiver)) {
                    return;
                }

                if (!$sejour->_NDA) {
                    // G�n�ration du NDA dans le cas de la cr�ation, ce dernier n'�tait pas cr��
                    if ($msg = $sejour->generateNDA()) {
                        CAppUI::setMsg($msg, UI_MSG_ERROR);
                    }

                    $NDA = new CIdSante400();
                    $NDA->loadLatestFor($sejour, $receiver->_tag_sejour);
                    $sejour->_NDA = $NDA->id400;
                }

                $patient = $sejour->_ref_patient;
                $patient->loadIPP($receiver->group_id);
                if (!$patient->_IPP) {
                    if ($msg = $patient->generateIPP()) {
                        CAppUI::setMsg($msg, UI_MSG_ERROR);
                    }
                }

                $current_affectation = $sejour->getCurrAffectation();
                if (!$this->createMovement($code, $sejour, $current_affectation)) {
                    return;
                }

                // Envoi de l'�v�nement
                $this->sendITI($this->profil, $this->transaction, $this->message, $code, $sejour);

                $code = "A03";
            }

            // Dans le cas d'une annulation d'une hospit. de jour on doit envoyer un A13 et un A11
            if ($sejour->fieldModified("annule", "1") && $sejour->_hdj_seance &&
                (CAppUI::gconf("ihe ITI hospi_de_jour", $sejour->group_id) == "A01/A03")) {
                $code = "A13";

                // Cas o� :
                // * on est l'initiateur du message
                // * le destinataire ne supporte pas le message
                if (!$this->isMessageSupported($this->message, $code, $receiver)) {
                    return;
                }

                if (!$sejour->_NDA) {
                    // G�n�ration du NDA dans le cas de la cr�ation, ce dernier n'�tait pas cr��
                    if ($msg = $sejour->generateNDA()) {
                        CAppUI::setMsg($msg, UI_MSG_ERROR);
                    }

                    $NDA = new CIdSante400();
                    $NDA->loadLatestFor($sejour, $receiver->_tag_sejour);
                    $sejour->_NDA = $NDA->id400;
                }

                $patient = $sejour->_ref_patient;
                $patient->loadIPP($receiver->group_id);
                if (!$patient->_IPP) {
                    if ($msg = $patient->generateIPP()) {
                        CAppUI::setMsg($msg, UI_MSG_ERROR);
                    }
                }

                $current_affectation = $sejour->getCurrAffectation();
                if (!$this->createMovement($code, $sejour, $current_affectation)) {
                    return;
                }

                // Envoi de l'�v�nement
                $this->sendITI($this->profil, $this->transaction, $this->message, $code, $sejour);

                $code = "A11";
            }

            // Dans le cas d'une cr�ation et que l'on renseigne entr�e r�elle et sortie r�elle,
            // il est n�cessaire de cr�er deux flux (A01/A04 et A03)
            if ($sejour->_ref_last_log->type == "create" && $sejour->entree_reelle && $sejour->sortie_reelle) {
                // Patient externe / Admission hospitalis�
                $code = in_array($sejour->type, self::getOutpatient($sejour->loadRefEtablissement())) ? "A04" : "A01";

                // Cas o� :
                // * on est l'initiateur du message
                // * le destinataire ne supporte pas le message
                if (!$this->isMessageSupported($this->message, $code, $receiver)) {
                    return;
                }

                if (!$sejour->_NDA) {
                    // G�n�ration du NDA dans le cas de la cr�ation, ce dernier n'�tait pas cr��
                    if ($msg = $sejour->generateNDA()) {
                        CAppUI::setMsg($msg, UI_MSG_ERROR);
                    }

                    $NDA = new CIdSante400();
                    $NDA->loadLatestFor($sejour, $receiver->_tag_sejour);
                    $sejour->_NDA = $NDA->id400;
                }

                $patient = $sejour->_ref_patient;
                $patient->loadIPP($receiver->group_id);
                if (!$patient->_IPP) {
                    if ($msg = $patient->generateIPP()) {
                        CAppUI::setMsg($msg, UI_MSG_ERROR);
                    }
                }

                // Cas o� lors de l'entr�e r�elle j'ai une affectation qui n'a pas �t� envoy�e
                if ($sejour->fieldModified("entree_reelle") && !$sejour->_old->entree_reelle) {
                    $current_affectation = $sejour->getCurrAffectation();
                }

                if (!$this->createMovement($code, $sejour, $current_affectation)) {
                    return;
                }

                // Envoi de l'�v�nement
                $this->sendITI($this->profil, $this->transaction, $this->message, $code, $sejour);

                $code = "A03";
            }

            // Cas o� l'on intercepte la modification du champ facturable du s�jour pour r�aliser
            // automatiquement les traitements sur les idex
            if ($receiver->_configs["change_idex_for_admit"] && $receiver->_configs["send_no_facturable"] != "1"
                && $sejour->fieldModified("facturable")
            ) {
                // envoyer un message d'annulation A38 (si pr�-admission) ou A11 (si admission)
                $code = $sejour->_etat == "preadmission" ? "A38" : "A11";
                if (!$this->isMessageSupported($this->message, $code, $receiver)) {
                    return;
                }

                if (!$this->createMovement($code, $sejour, $current_affectation)) {
                    return;
                }

                // Envoi de l'�v�nement
                $this->sendITI($this->profil, $this->transaction, $this->message, $code, $sejour);

                // Mettre en trash le NDA
                $sejour->trashNDA();

                // R�g�n�rer un NDA pour le s�jour
                if ($msg = $sejour->generateNDA()) {
                    CAppUI::setMsg($msg, UI_MSG_ERROR);
                }

                $NDA = new CIdSante400();
                $NDA->loadLatestFor($sejour, $receiver->_tag_sejour);
                $sejour->_NDA = $NDA->id400;

                // Envoyer un message d'admission (A01 / A04) ou de pr�-admission (A05)
                $code = $sejour->_etat == "preadmission" ?
                    "A05" : (in_array(
                        $sejour->type,
                        self::getOutpatient($sejour->loadRefEtablissement())
                    ) ? "A04" : "A01");
            }

            if (!$code) {
                return;
            }

            // Cas o� :
            // * on est l'initiateur du message
            // * le destinataire ne supporte pas le message
            if (!$this->isMessageSupported($this->message, $code, $receiver)) {
                return;
            }

            if (!$sejour->_NDA) {
                // G�n�ration du NDA dans le cas de la cr�ation, ce dernier n'�tait pas cr��
                if ($msg = $sejour->generateNDA()) {
                    CAppUI::setMsg($msg, UI_MSG_ERROR);
                }

                $NDA = new CIdSante400();
                $NDA->loadLatestFor($sejour, $receiver->_tag_sejour);
                $sejour->_NDA = $NDA->id400;
            }

            $patient = $sejour->_ref_patient;
            $patient->loadIPP($receiver->group_id);
            if (!$patient->_IPP) {
                if ($msg = $patient->generateIPP()) {
                    CAppUI::setMsg($msg, UI_MSG_ERROR);
                }
            }

            // Cas o� lors de l'entr�e r�elle j'ai une affectation qui n'a pas �t� envoy�e
            if ($sejour->fieldModified("entree_reelle") && !$sejour->_old->entree_reelle) {
                $current_affectation = $sejour->getCurrAffectation();
                if (!$current_affectation || !$current_affectation->_id) {
                    $current_affectation = $sejour->loadRefFirstAffectation();
                }
            }

            if (!$this->createMovement($code, $sejour, $current_affectation)) {
                return;
            }

            // Envoi de l'�v�nement
            $this->sendITI($this->profil, $this->transaction, $this->message, $code, $sejour);
        }

        // Traitement Affectation
        if ($mbObject instanceof CAffectation) {
            $affectation = $mbObject;
            $current_log = $affectation->_ref_current_log;

            if (!$current_log || $affectation->_no_synchro_eai || !in_array($current_log->type, ["create", "store"])) {
                return;
            }

            // Affectation non li�e � un s�jour
            $sejour  = $affectation->_ref_sejour;
            $patient = $sejour->loadRefPatient();

            if (CModule::getActive("appFineClient")) {
                if ($receiver->_configs['send_evenement_to_mbdmp']) {
                    if (!CAppFineClient::checkRGPD($patient, $receiver->group_id)) {
                        return;
                    }
                }

                if ($receiver->_configs['send_evenement_to_mbdmp'] && !CAppFineClient::loadIdex($patient)->_id) {
                    return;
                }
            }

            if (!$sejour->_id) {
                return;
            }

            if ($sejour->annule) {
                return;
            }

            // Si le type de s�jour n'est pas support� par ce destinataire
            if ($exclude_admit_type = $receiver->_configs["exclude_admit_type"]) {
                $exclude_admit_types = explode(",", $exclude_admit_type);
                foreach ($exclude_admit_types as $_exclude_admit_type) {
                    if ($_exclude_admit_type == $sejour->type) {
                        return;
                    }
                }
            }

            // Destinataire g�re seulement les non facturables
            if ($receiver->_configs["send_no_facturable"] == "0" && $sejour->facturable) {
                return;
            }

            // Destinataire g�re seulement les facturables
            if ($receiver->_configs["send_no_facturable"] == "2" && !$sejour->facturable) {
                return;
            }

            // Premi�re affectation des urgences on ne la transmet pas, seulement pour l'�v�nement de bascule
            // Sauf si nous sommes dans un s�jour d'UHCD, nous g�n�rons un A02
            if ($affectation->_mutation_urg && !$sejour->last_UHCD) {
                return;
            }

            // Si on ne g�re les s�jours du b�b� on ne transmet pas l'affectation si c'est un s�jour enfant
            if (!$receiver->_configs["send_child_admit"]) {
                $naissance                   = new CNaissance();
                $naissance->sejour_enfant_id = $sejour->_id;
                $naissance->loadMatchingObject();
                if ($naissance->_id) {
                    return;
                }
            }

            // Pas d'envoie d'affectation si la patient n'est pas sortie des urgences
            $rpu                     = new CRPU();
            $rpu->mutation_sejour_id = $sejour->_id;
            $rpu->loadMatchingObject();

            if ($rpu->_id) {
                $sejour_rpu = $rpu->loadRefSejour();
                if (!$affectation->_mutation_urg && $sejour_rpu->mode_sortie != "mutation") {
                    return;
                }
            }

            // Pas d'envoie d'affectation pour les s�jours reliquats
            // Sauf si le s�jour est en UHCD
            $rpu = $sejour->loadRefRPU();
            if ($rpu && $rpu->mutation_sejour_id && ($rpu->sejour_id != $rpu->mutation_sejour_id) && !$sejour->UHCD) {
                return;
            }

            // Si le group_id du s�jour est diff�rent de celui du destinataire
            if ($sejour->group_id != $receiver->group_id) {
                return;
            }

            // On envoie pas les affectations pr�visionnelles
            if (!$receiver->_configs["send_provisional_affectation"] && $sejour->_etat == "preadmission") {
                return;
            }
            $first_affectation = $sejour->loadRefFirstAffectation();
            $sejour->loadOldObject();

            $code = $this->getCodeAffectation($affectation, $first_affectation);

            // Cas o� :
            // * on est l'initiateur du message
            // * le destinataire ne supporte pas le message
            if (!$this->isMessageSupported($this->message, $code, $receiver)) {
                return;
            }

            $sejour->loadRefPatient();
            $sejour->_receiver = $receiver;

            $patient = $sejour->_ref_patient;
            $patient->loadIPP($receiver->group_id);
            if (!$patient->_IPP) {
                if ($msg = $patient->generateIPP()) {
                    CAppUI::setMsg($msg, UI_MSG_ERROR);
                }
            }

            if (!$this->createMovement($code, $sejour, $affectation)) {
                return;
            }

            $service          = $affectation->loadRefService();
            $curr_affectation = $sejour->loadRefCurrAffectation();
            // On envoie pas de mouvement ant�rieur � la derni�re affectation
            if (($service->uhcd || $service->radiologie || $service->urgence) && $affectation->sortie < $curr_affectation->sortie) {
                return;
            }

            // Ne pas envoyer la sortie si le s�jour a une entr�e r�elle et si on modifie ou cr�� un affectation
            if (!$receiver->_configs["send_expected_discharge_with_affectation"] && $sejour->entree_reelle) {
                $sejour->sortie_prevue = null;
            }

            // Envoi de l'�v�nement
            $this->sendITI($this->profil, $this->transaction, $this->message, $code, $mbObject);
        }
    }

    /**
     * Create movement
     *
     * @param string       $code        HL7 event code
     * @param CSejour      $sejour      Admit
     * @param CAffectation $affectation Affectation
     *
     * @return CMovement|mixed
     */
    function createMovement($code, CSejour $sejour, CAffectation $affectation = null)
    {
        if (!$code) {
            return null;
        }

        $insert = in_array($code, CHL7v2SegmentZBE::$actions["INSERT"]);
        $update = in_array($code, CHL7v2SegmentZBE::$actions["UPDATE"]);
        $cancel = in_array($code, CHL7v2SegmentZBE::$actions["CANCEL"]);
        if ($sejour->_cancel_hospitalization) {
            $insert = $update = false;
            $cancel = true;
        }

        $movement = new CMovement();
        // Initialise le mouvement
        $movement->sejour_id = $sejour->_id;

        $receiver = $sejour->_receiver;
        $configs  = $receiver->_configs;

        $affectation_id           = null;
        $update_first_affectation = false;
        $first_affectation        = $sejour->loadRefFirstAffectation();
        if ($affectation) {
            $current_log = $affectation->_ref_current_log;
            /** @var CService $service */
            $service = $affectation->loadRefService();

            // Si le service est de radiologie ou d'urgence ou
            // Dans le cas o� il s'agit de la premi�re affectation du s�jour et qu'on est en type "cr�ation" on ne recherche pas
            // un mouvement avec l'affectation, mais on va prendre le mouvement d'admission
            if ((($service->radiologie || $service->urgence) && !$affectation->uhcd) ||
                ($current_log && ($current_log->type == "create") && $first_affectation && ($first_affectation->_id == $affectation->_id))
            ) {
                switch ($configs["send_first_affectation"]) {
                    case 'Z99':
                        $affectation_id           = $affectation->_id;
                        $update_first_affectation = true;
                        $affectation              = null;
                        break;

                    default:
                        $movement->affectation_id = $affectation->_id;
                }
            } else {
                if ($affectation->uhcd) {
                    $affectation_id = $affectation->_id;
                } else {
                    if ($first_affectation && ($first_affectation->_id == $affectation->_id)) {
                        $update_first_affectation = true;
                    }

                    $affectation_id           = $affectation->_id;
                    $movement->affectation_id = $affectation->_id;
                }
            }
        }

        // Dans le cas d'un insert
        if ($insert) {
            // Le type correspond n�cessairement au type actuel du s�jour
            $movement_type = $sejour->getMovementType($code);
            if ($sejour->_hdj_seance && (CAppUI::gconf("ihe ITI hospi_de_jour", $sejour->group_id) == "A01/A03")) {
                if ($code == "A01") {
                    $movement_type = "ADMI";
                }
                if ($code == "A03") {
                    $movement_type = "SORT";
                }
            }
            if ($update_first_affectation) {
                $movement->affectation_id = $affectation_id;
            }
            $movement->movement_type         = $movement_type;
            $movement->original_trigger_code = $code;
            if (!$affectation && $affectation_id) {
                $affectation = new CAffectation();
                $affectation->load($affectation_id);
            }
            $movement->start_of_movement = $this->getStartOfMovement($code, $sejour, $affectation);

            // On ne recherche pas parmi les mouvements annul�s
            $movement->cancel = 0;
            $movement->loadMatchingObject();

            if ($msg = $movement->store()) {
                trigger_error($msg, E_USER_NOTICE);
            }

            return $sejour->_ref_hl7_movement = $movement;
        } elseif ($update) {
            // Dans le cas d'un update le type correspond � celui du trigger
            $movement_type = null;

            // Dans le cas
            // * d'une cr�ation / modification d'une premi�re affectation
            // * annulation du s�jour
            if ($update_first_affectation || $sejour->fieldModified("annule")) {
                $movement_type = $sejour->entree_reelle ? "ADMI" : "PADM";

                $original_trigger_code = null;
                // Cas d'une pr�-admission
                if (!$sejour->entree_reelle) {
                    $original_trigger_code = "A05";
                } else {
                    $original_trigger_code = $this->getOriginalCode($sejour);
                }

                $movement->original_trigger_code = $original_trigger_code;
            }

            // Je modifie un champ de l'admission : A05/A01/A04
            $fields_generate_A01_A04_A05 = explode(
                "|",
                CAppUI::gconf("ihe ITI fields_generate_A01_A04", $sejour->group_id)
            );
            foreach ($fields_generate_A01_A04_A05 as $_field) {
                if ($sejour->fieldModified("$_field")) {
                    $movement_type = $sejour->entree_reelle ? "ADMI" : "PADM";

                    $original_trigger_code = null;
                    if (!$sejour->entree_reelle) {
                        $original_trigger_code = "A05";
                    } elseif ($sejour->fieldModified("sortie_reelle")) {
                        $original_trigger_code = "A03";
                    } else {
                        $original_trigger_code = $this->getOriginalCode($sejour);
                    }

                    $movement->original_trigger_code = $original_trigger_code;
                    continue 1;
                }
            }

            // Mise � jour sortie r�elle
            if ($sejour->fieldModified("sortie_reelle")) {
                $movement_type = "SORT";
            }

            if (!$movement->affectation_id && !$movement_type) {
                $movement_type = $sejour->getMovementType($code);
            }

            // Dans le cas d'une s�ance, on a g�n�r� des A01/A03 sans entr�e/sortie r�elle
            if ($sejour->_hdj_seance && (CAppUI::gconf("ihe ITI hospi_de_jour", $sejour->group_id) == "A01/A03")) {
                $movement_type_temp = null;

                // Dans le cas d'une cr�ation / modification d'une premi�re affectation
                if ($update_first_affectation) {
                    $movement_type_temp = $sejour->entree_reelle ? "ADMI" : "PADM";
                }

                // Annulation de l'admission
                if ($sejour->_old->entree_reelle && !$sejour->entree_reelle) {
                    $movement_type_temp = "ADMI";
                }

                // Annulation de la sortie r�elle
                if ($sejour->_old->sortie_reelle && !$sejour->sortie_reelle) {
                    $movement_type_temp = "SORT";
                }

                // Je modifie un champ de l'admission : A05/A01/A04
                $fields_generate_A01_A04_A05 = explode(
                    "|",
                    CAppUI::gconf(
                        "ihe ITI fields_generate_A01_A04",
                        $sejour->group_id
                    )
                );
                foreach ($fields_generate_A01_A04_A05 as $_field) {
                    if ($sejour->fieldModified("$_field")) {
                        $movement_type_temp = $sejour->entree_reelle ? "ADMI" : "PADM";
                        continue 1;
                    }
                }
                $movement_type = $movement_type_temp ?: "SORT";
            }

            // Dans le cas d'un placement dans un service d'UHCD on va mettre � jour le A06
            if ($affectation && $affectation->loadRefService()->uhcd) {
                $movement->original_trigger_code = "A06";
                $movement->affectation_id        = $affectation->_id;
            }

            $movement->movement_type = $movement_type;
        } elseif ($cancel) {
            $original_trigger_code = CMbArray::get(CHL7v2SegmentZBE::$originalTriggerCodeFromCancel, $code);
            // Dans le cas du A11, on peut avoir un A01 ou un A04
            if ($original_trigger_code == "A01" && in_array(
                    $sejour->type,
                    self::getOutpatient($sejour->loadRefEtablissement())
                )) {
                $original_trigger_code = "A04";
            }

            if ($sejour->_cancel_hospitalization) {
                $original_trigger_code = "A06";
            }

            $movement->original_trigger_code = $original_trigger_code;
        }

        // On ne recherche pas parmi les mouvements annul�s
        if (!$cancel) {
            $movement->cancel = 0;
        }

        // Dans le cas d'une s�ance (A01/A03) et qu'on supprime
        if (($sejour->_hdj_seance && (CAppUI::gconf(
                        "ihe ITI hospi_de_jour",
                        $sejour->group_id
                    ) == "A01/A03")) && $cancel) {
            // Annulation de l'admission
            if ($sejour->_old->entree_reelle && !$sejour->entree_reelle) {
                $movement->movement_type = "ADMI";
            }

            // Annulation de la sortie r�elle
            if ($sejour->_old->sortie_reelle && !$sejour->sortie_reelle) {
                $movement->movement_type = "SORT";
            }
        }

        $order     = "affectation_id DESC";
        $movements = $movement->loadMatchingList($order);
        if ($update && empty($movements)) {
            // Dans le cas d'un s�jour pass� par les urgences (mutation), on recherche le trigger original sur un A04
            if (!$movement->_id && $movement->original_trigger_code == "A01") {
                $movement->original_trigger_code = "A04";
                $movements                       = $movement->loadMatchingList($order);
            }

            // On recherche sans l'affectation
            if (empty($movements)) {
                $movement->affectation_id = null;
                $movements                = $movement->loadMatchingList($order);
            }
        }

        if (!empty($movements)) {
            $movement = reset($movements);
        }

        // Dans le cas d'un update / cancel et que je n'ai pas de mouvement alors on retourne (cas anormal)
        if (($update || $cancel) && !$movement->_id) {
            return null;
        }

        if ($update) {
            if (!$affectation) {
                $affectation = new CAffectation();
                $affectation->load($movement->affectation_id);
            }
            $movement->start_of_movement = $this->getStartOfMovement(
                $movement->original_trigger_code,
                $sejour,
                $affectation
            );
        }

        // on annule un mouvement sauf dans le cas d'une annulation de mutation et que
        if ($cancel && !($code == "A12" && $movement->original_trigger_code != "A02")) {
            $movement->cancel = 1;
        }

        if ($affectation_id) {
            $movement->affectation_id = $affectation_id;
        }

        if (!$movement->original_trigger_code) {
            return null;
        }

        if ($msg = $movement->store()) {
            return null;
        }

        return $sejour->_ref_hl7_movement = $movement;
    }

    /**
     * Get original code
     *
     * @param CSejour           $sejour      Admit
     * @param CAffectation|null $affectation Affectation
     *
     * @return string
     */
    function getOriginalCode(CSejour $sejour, CAffectation $affectation = null)
    {
        // Dans le cas d'un UHCD la modification porte sur l'admission aux urgences, le type du s�jour ayant chang�, et que l'on pas envoy� un A01
        if ($sejour->last_UHCD) {
            return "A04";
        }

        return in_array($sejour->type, self::getOutpatient($sejour->loadRefEtablissement())) ? "A04" : "A01";
    }

    /**
     * Get start of movement
     *
     * @param string       $code        HL7 event code
     * @param CSejour      $sejour      Admit
     * @param CAffectation $affectation Affectation
     * @param CMovement    $movement    Movement
     *
     * @return null|string
     */
    function getStartOfMovement($code, CSejour $sejour, CAffectation $affectation = null, CMovement $movement = null)
    {
        switch ($code) {
            // Admission hospitalis� / externe
            case 'A01':
            case 'A04':
                $sejour->_admit = true;

                // Date de l'admission
                return $sejour->entree;

            // Mutation : changement d'UF h�bergement
            case 'A02':
                if (!$affectation || !$affectation->_id) {
                    return CMbDT::dateTime();
                }

                return $affectation->entree;

            // Changement de statut externe ou urgence vers hospitalis�
            case 'A06':
                // Changement de statut hospitalis� ou urgence vers externe
            case 'A07':
                // Changement de m�decin responsable
            case 'A54':
                // Dans le cas d'une modification d'un mouvement, l'heure du mouvement est celle du mouvement initiateur
                if ($movement) {
                    return $movement->start_of_movement;
                }

                // Date du transfert
            if (!$affectation || !$affectation->_id) {
                return CMbDT::dateTime();
            }

                return $affectation->entree;

            // Absence provisoire (permission) et mouvement de transfert vers un plateau technique pour acte (<48h)
            case 'A21':
                // Changement d'UF m�dicale
            case 'Z80':
                // Changement d'UF de soins
            case 'Z84':
            if (!$affectation || !$affectation->_id) {
                return CMbDT::dateTime();
            }

                return $affectation->entree;

            // Retour d'absence provisoire (permission) et mouvement de transfert vers un plateau technique pour acte (<48h)
            case 'A22':
                if (!$affectation || !$affectation->_id) {
                    return CMbDT::dateTime();
                }

                return $affectation->sortie;

            // Sortie d�finitive
            case 'A03':
                // Date de la sortie
                return $sejour->sortie;

            // Pr�-admission
            case 'A05':
            case 'A14':
                // Date de la pr�-admission
                return $sejour->entree;

            // Sortie en attente
            case 'A16':
                // Date de la sortie
                return $sejour->sortie;

            default:
        }
    }

    /**
     * Get bascule HL7 event code
     *
     * @param CSejour $from Admit from
     * @param CSejour $to   Admit to
     *
     * @return string
     */
    function getBasculeCode(CSejour $from, CSejour $to)
    {
        $outpatient         = self::getOutpatient($from->loadRelGroup());
        $consult_outpatient = false;
        if (CMbArray::get($outpatient, "consult")) {
            $consult_outpatient = true;
        }

        $matrix = [    // comp/M   comp/C   comp/O   bebe/*   ambu/*   urg/*   seances/* exte/*   consult/*
            "comp/M"    => [null, "Z99", "Z99", "Z99", "Z99", "A07", "Z99", "A07", $consult_outpatient ? "A07" : "Z99"],
            "comp/C"    => ["Z99", null, "Z99", "Z99", "Z99", "A07", "Z99", "A07", $consult_outpatient ? "A07" : "Z99"],
            "comp/O"    => ["Z99", "Z99", null, "Z99", "Z99", "A07", "Z99", "A07", $consult_outpatient ? "A07" : "Z99"],
            "bebe/*"    => ["A06", "A06", "A06", null, "A06", "A07", "A06", "A07", $consult_outpatient ? "A07" : "Z99"],
            "ambu/*"    => ["Z99", "Z99", "Z99", "Z99", null, "A07", "Z99", "A07", $consult_outpatient ? "A07" : "Z99"],
            "urg/*"     => ["A06", "A06", "A06", "A06", "A06", null, "A06", "Z99", $consult_outpatient ? "Z99" : "A06"],
            "seances/*" => ["Z99", "Z99", "Z99", "Z99", "Z99", "A07", null, "A07", $consult_outpatient ? "A07" : "Z99"],
            "exte/*"    => ["A06", "A06", "A06", "A06", "A06", "Z99", "A06", null, $consult_outpatient ? "Z99" : "A06"],
            "consult/*" => [
                // comp/M
                $consult_outpatient ? "A06" : "Z99",
                // comp/C
                $consult_outpatient ? "A06" : "Z99",
                // comp/O
                $consult_outpatient ? "A06" : "Z99",
                // bebe/*
                $consult_outpatient ? "A06" : "Z99",
                // ambu/*
                $consult_outpatient ? "A06" : "Z99",
                // urg/*
                $consult_outpatient ? "Z99" : "A07",
                // seances/*
                $consult_outpatient ? "A06" : "Z99",
                // exte/*
                $consult_outpatient ? "Z99" : "A07",
                // consult/*
                null,
            ],
        ];

        $from->completeField("type", "type_pec");
        $type_from     = $from->type;
        $type_pec_from = $from->type_pec;

        $to->completeField("type", "type_pec");
        $type_to     = $to->type;
        $type_pec_to = $to->type_pec;

        // psy || ssr == seances
        if (in_array($type_from, ["psy", "ssr"])) {
            $type_from = "seances";
        }
        if (in_array($type_to, ["psy", "ssr"])) {
            $type_to = "seances";
        }

        /* // TODO prendre en compte les sejours de type nouveau n�
        $naissances = $from->loadRefsNaissances();
        foreach ($naissances as $_naissance) {
          if ($naissances->sejour_bebe_id == $from->_id) {
            $type_from = "bebe";
            break;
          }
        }*/

        $row = CMbArray::first($matrix, ["$type_from/$type_pec_from", "$type_from/*"]);
        if (!$row) {
            return $this->getModificationAdmitCode($from->_receiver);
        }

        $columns = array_flip(array_keys($matrix));
        $col_num = CMbArray::first($columns, ["$type_to/$type_pec_to", "$type_to/*"]);

        if ($columns === null) {
            return $this->getModificationAdmitCode($from->_receiver);
        }

        if ($row[$col_num] == "Z99") {
            return $this->getModificationAdmitCode($from->_receiver);
        }

        return $row[$col_num];
    }

    /**
     * Get admit HL7 event code
     *
     * @param CSejour $sejour Admit
     *
     * @return null|string
     */
    function getCodeSejour(CSejour $sejour)
    {
        $current_log = $sejour->loadLastLog();
        if (!in_array($current_log->type, ["create", "store"])) {
            return null;
        }

        $receiver = $sejour->_receiver;
        $configs  = $receiver->_configs;

        $sejour->loadOldObject();

        // Cas d'une pr�-admission
        if ($sejour->_etat == "preadmission") {
            // Cas d'une annulation ?
            if ($sejour->fieldModified("annule", "1")) {
                // Dans le cas d'une hospit. de jour on retourne l'annulation de l'admission
                if ($sejour->_hdj_seance && (CAppUI::gconf("ihe ITI hospi_de_jour", $sejour->group_id) == "A01/A03")) {
                    return "A11";
                }

                return "A38";
            }

            // Annulation de l'admission
            if ($sejour->_old->entree_reelle && !$sejour->entree_reelle) {
                return "A11";
            }

            // Cr�ation d'une pr�-admission
            if ($current_log->type == "create") {
                // Pending admit
                if ($configs["iti31_pending_event_management"] && $sejour->recuse == -1) {
                    return "A14";
                }

                return "A05";
            }

            // Cancel the pending admission
            if ($configs["iti31_pending_event_management"] && $sejour->recuse == -1 && $sejour->fieldModified(
                    "annule",
                    "1"
                )) {
                return "A27";
            }

            // Cas d'un r�tablissement d'annulation ?
            if ($sejour->fieldModified("annule", "0") && ($sejour->_old->annule == 1)) {
                return "A05";
            }

            // R�attribution dossier administratif
            if ($sejour->fieldModified("patient_id")) {
                return "A44";
            }

            if (!$configs["modification_before_admit"] && !$sejour->entree_reelle) {
                return;
            }

            // Simple modification ?
            return $this->getModificationAdmitCode($sejour->_receiver);
        }

        // Cas d'un s�jour en cours (entr�e r�elle)
        if ($sejour->_etat == "encours") {
            // Cas d'une annulation
            if ($sejour->fieldModified("annule", "1")) {
                return "A11";
            }

            // Annulation de la sortie r�elle
            if ($sejour->_old->sortie_reelle && !$sejour->sortie_reelle) {
                return "A13";
            }

            // Admission faite
            $sejour_old = $sejour->_old;
            if ($sejour->fieldModified("entree_reelle") && !$sejour_old->entree_reelle
                || $sejour->entree_reelle && !$sejour_old->entree_reelle
            ) {
                // Dans le cas d'une hospit. de jour on retourne une modification l'entr�e r�elle a d�j� �t� envoy�e
                if ($sejour->_hdj_seance && (CAppUI::gconf("ihe ITI hospi_de_jour", $sejour->group_id) == "A01/A03")) {
                    return $this->getModificationAdmitCode($sejour->_receiver);
                }

                // Patient externe
                if (in_array($sejour->type, self::getOutpatient($sejour->loadRefEtablissement()))) {
                    return "A04";
                }

                // Admission hospitalis�
                return "A01";
            }

            // Confirmation de sortie
            if ($sejour->fieldFirstModified("confirme")) {
                return "A16";
            }

            // Annulation confirmation de sortie
            if ($sejour->fieldEmptyValued("confirme")) {
                return "A25";
            }

            // Bascule du type et type_pec
            if ($sejour->fieldModified("type")) {
                $sejour->_old->_receiver = $sejour->_receiver;

                return $this->getBasculeCode($sejour->_old, $sejour);
            }

            // Changement du m�decin responsable
            if ($sejour->fieldModified("praticien_id")) {
                $first_log = $sejour->loadFirstLog();

                $praticien_id = $sejour->getValueAtDate($first_log->date, "praticien_id");

                $send_change_attending_doctor = $configs["send_change_attending_doctor"];
                // Annulation du m�decin responsable
                if ($sejour->praticien_id == $praticien_id) {
                    return (($send_change_attending_doctor == "A54") ? "A55" : $this->getModificationAdmitCode(
                        $receiver
                    ));
                }

                return (($send_change_attending_doctor == "A54") ? "A54" : $this->getModificationAdmitCode($receiver));
            }

            // R�attribution dossier administratif
            if ($sejour->fieldModified("patient_id")) {
                return "A44";
            }

            // Cas d'une r�tablissement on simule une nouvelle admission
            if ($sejour->fieldModified("annule", "0")) {
                // Patient externe
                if (in_array($sejour->type, self::getOutpatient($sejour->loadRefEtablissement()))) {
                    return "A04";
                }

                // Admission hospitalis�
                return "A01";
            }

            // Notification sur le transfert
            if ($configs["iti31_pending_event_management"]
                && $sejour->fieldModified("mode_sortie")
                && $sejour->mode_sortie == "transfert"
            ) {
                return "A15";
            }

            // Annulation de la notification sur le transfert
            if ($configs["iti31_pending_event_management"]
                && $sejour->_old->mode_sortie
                && $sejour->_old->mode_sortie == "transfert"
                && !$sejour->mode_sortie
            ) {
                return "A26";
            }

            // On ne transmet pas les modifications d'un s�jour d�s lors que celui-ci a une entr�e r�elle
            if (!$receiver->_configs["send_change_after_admit"]) {
                return null;
            }

            // Simple modification ?
            return $this->getModificationAdmitCode($sejour->_receiver);
        }

        // Cas d'un s�jour cl�tur� (sortie r�elle)
        if ($sejour->_etat == "cloture") {
            // Cas d'une annulation ?
            if ($sejour->fieldModified("annule", "1")) {
                return "A13";
            }

            // R�attribution dossier administratif
            if ($sejour->fieldModified("patient_id")) {
                return "A44";
            }

            // Sortie r�elle renseign�e
            if ($sejour->fieldModified("sortie_reelle") && !$sejour->_old->sortie_reelle) {
                // Dans le cas d'une hospit. de jour on retourne une modification l'entr�e r�elle a d�j� �t� envoy�e
                if ($sejour->_hdj_seance && (CAppUI::gconf("ihe ITI hospi_de_jour", $sejour->group_id) == "A01/A03")) {
                    return $this->getModificationAdmitCode($sejour->_receiver);
                }

                return "A03";
            }

            // On ne transmet pas les modifications d'un s�jour d�s lors que celui-ci a une entr�e r�elle
            if (!$receiver->_configs["send_change_after_admit"]) {
                return null;
            }

            // Simple modification ?
            return $this->getModificationAdmitCode($sejour->_receiver);
        }

        return null;
    }

    /**
     * Get affectation HL7 event code
     *
     * @param CAffectation $affectation       Affectation
     * @param CAffectation $first_affectation First affectation
     *
     * @return null|string
     */
    function getCodeAffectation(CAffectation $affectation, CAffectation $first_affectation = null)
    {
        $current_log = $affectation->_ref_current_log;
        if (!in_array($current_log->type, ["create", "store"])) {
            return null;
        }

        $receiver = $affectation->_receiver;
        $configs  = $receiver->_configs;
        $service  = $affectation->loadRefService();

        $code = "A02";
        if ($service->urgence || $service->radiologie || $service->uhcd) {
            $code = $this->getModificationAdmitCode($receiver);
        }

        if ($current_log->type == "create") {
            /* Affectation dans un service externe */
            if ($service->externe) {
                return "A21";
            }

            // Dans le cas o� il s'agit de la premi�re affectation du s�jour on ne fait pas une mutation mais une modification
            if ($first_affectation && ($first_affectation->_id == $affectation->_id)) {
                switch ($configs["send_first_affectation"]) {
                    case 'Z99':
                        return $this->getModificationAdmitCode($receiver);
                    default:
                        return $code;
                }
            }

            // Cr�ation d'une affectation
            switch ($configs["send_transfer_patient"]) {
                case 'Z99':
                    return $this->getModificationAdmitCode($receiver);
                default:
                    return $code;
            }
        }

        /* Affectation dans un service externe effectu�e */
        if ($service->externe && !$affectation->_old->effectue && $affectation->effectue) {
            return "A22";
        }

        /* Affectation dans un service externe effectu�e */
        if ($service->externe && $affectation->_old->effectue && !$affectation->effectue) {
            return "A53";
        }

        /* Changement d'UF M�dicale */
        if ($affectation->_old->uf_medicale_id && $affectation->fieldModified("uf_medicale_id")) {
            /* @todo G�rer le cas o� : cr�ation d'une nouvelle affectation && UM1 # UM2 */
            switch ($configs["send_change_medical_responsibility"]) {
                case 'Z80':
                    return "Z80";
                case 'A02':
                    return "A02";
                default:
                    return $this->getModificationAdmitCode($receiver);
            }
        }

        /* Changement d'UF de Soins */
        if ($affectation->_old->uf_soins_id && $affectation->fieldModified("uf_soins_id")) {
            /* @todo G�rer le cas o� : cr�ation d'une nouvelle affectation && US1 # US2 */
            switch ($configs["send_change_nursing_ward"]) {
                case 'Z84':
                    return "Z84";
                case 'A02':
                    return "A02";
                default:
                    return $this->getModificationAdmitCode($receiver);
            }
        }

        // Modifcation d'une affectation
        return $this->getModificationAdmitCode($affectation->_receiver);
    }

    /**
     * Get birth HL7 event code
     *
     * @param CSejour $sejour Admit
     *
     * @return null|string
     */
    function getCodeBirth(CSejour $sejour)
    {
        // Cas d'une pr�-admission
        if ($sejour->_etat == "preadmission") {
            return "A05";
        }

        // Patient externe
        if (in_array($sejour->type, self::getOutpatient($sejour->loadRefEtablissement()))) {
            return "A04";
        }

        // Admission hospitalis�
        return "A01";
    }

    /**
     * Get affectation HL7 event code
     *
     * @param CReceiverHL7v2 $receiver Receiver HL7v2
     *
     * @return string
     */
    function getModificationAdmitCode(CReceiverHL7v2 $receiver)
    {
        switch ($receiver->_i18n_code) {
            // Cas de l'extension fran�aise : Z99
            case "FRA":
                $code = "Z99";
                break;
            // Cas internationnal : A08
            default:
                $code = $receiver->_configs["modification_admit_code"];
        }

        return $code;
    }

    /**
     * Trigger before event merge
     *
     * @param CStoredObject $mbObject Object
     *
     * @return bool
     * @throws CMbException
     */
    function onBeforeMerge(CStoredObject $mbObject)
    {
        if (!$this->isHandled($mbObject)) {
            return false;
        }

        /** @var CSejour $sejour */
        $sejour = $mbObject;
        /** @var CInteropReceiver $receiver */
        $receiver = $sejour->_receiver;
        $receiver->getInternationalizationCode($this->transaction);

        if (CModule::getActive("appFineClient")) {
            $sejour->loadRefPatient();
            if ($receiver->_configs['send_evenement_to_mbdmp']) {
                if (!CAppFineClient::checkRGPD($sejour->_ref_patient, $receiver->group_id)) {
                    return false;
                }
            }

            if ($receiver->_configs['send_evenement_to_mbdmp'] && !CAppFineClient::loadIdex(
                    $sejour->_ref_patient
                )->_id) {
                return false;
            }
        }

        // Si le type de s�jour n'est pas support� par ce destinataire
        if ($exclude_admit_type = $receiver->_configs["exclude_admit_type"]) {
            $exclude_admit_types = explode(",", $exclude_admit_type);
            foreach ($exclude_admit_types as $_exclude_admit_type) {
                if ($_exclude_admit_type == $sejour->type) {
                    return false;
                }
            }
        }

        foreach ($sejour->_fusion as $group_id => $infos_fus) {
            if ($receiver->group_id != $group_id) {
                continue;
            }

            $sejour1_nda = $sejour->_NDA = $infos_fus["sejour1_nda"];

            /** @var CSejour $sejour_elimine */
            $sejour_elimine = $infos_fus["sejourElimine"];
            $sejour2_nda    = $sejour_elimine->_NDA = $infos_fus["sejour2_nda"];
            $receiver->loadConfigValues();

            // Cas 2 NDA : Suppression du deuxi�me s�jour
            if ($sejour1_nda && $sejour2_nda) {
                if ($receiver->_configs["send_a42_onmerge"]) {
                    continue;
                }
                // Dans la pr�-admission : A38
                if ($sejour_elimine->_etat == "preadmission") {
                    $code = "A38";
                } // En admission / cl�tur� : A11
                else {
                    $code = "A11";
                }

                $sejour_elimine->_receiver = $receiver;
                $sejour_elimine->loadRefPatient();

                if (!$this->isMessageSupported($this->message, $code, $receiver)) {
                    continue;
                }

                // Destinataire g�re seulement les non facturables
                if ($receiver->_configs["send_no_facturable"] == "0" && $sejour_elimine->facturable) {
                    continue;
                }

                // Destinataire g�re seulement les facturables
                if ($receiver->_configs["send_no_facturable"] == "2" && !$sejour_elimine->facturable) {
                    continue;
                }

                $this->createMovement($code, $sejour_elimine);

                $this->sendITI($this->profil, $this->transaction, $this->message, $code, $sejour_elimine);

                continue;
            }
        }
    }

    /**
     * Trigger when merge failed
     *
     * @param CStoredObject $mbObject Object
     *
     * @return bool
     */
    function onMergeFailure(CStoredObject $mbObject)
    {
        if (!$this->isHandled($mbObject)) {
            return false;
        }

        if (!$mbObject instanceof CSejour) {
            return false;
        }

        // On va r�atribuer les idexs en cas de probl�me dans la fusion
        foreach ($mbObject->_fusion as $group_id => $infos_fus) {
            if (!$infos_fus || !array_key_exists("idexs_changed", $infos_fus)) {
                return false;
            }

            foreach ($infos_fus["idexs_changed"] as $idex_id => $tag_name) {
                $idex = new CIdSante400();
                $idex->load($idex_id);

                if (!$idex->_id) {
                    continue;
                }

                // R�attribution sur l'objet non supprim�
                $sejour_elimine  = $infos_fus["sejourElimine"];
                $idex->object_id = $sejour_elimine->_id;

                $idex->tag = $tag_name;
                $idex->store();
            }
        }
    }

    /**
     * Trigger after event merge
     *
     * @param CStoredObject $mbObject Object
     *
     * @return bool
     * @throws CMbException
     */
    function onAfterMerge(CStoredObject $mbObject)
    {
        if (!$this->isHandled($mbObject)) {
            return false;
        }

        /** @var CSejour $sejour */
        $sejour = $mbObject;
        /** @var CInteropReceiver $receiver */
        $receiver = $sejour->_receiver;
        $receiver->getInternationalizationCode($this->transaction);

        if (CModule::getActive("appFineClient")) {
            $sejour->loadRefPatient();
            if ($receiver->_configs['send_evenement_to_mbdmp']) {
                if (!CAppFineClient::checkRGPD($sejour->_ref_patient, $receiver->group_id)) {
                    return false;
                }
            }

            if ($receiver->_configs['send_evenement_to_mbdmp'] && !CAppFineClient::loadIdex(
                    $sejour->_ref_patient
                )->_id) {
                return false;
            }
        }

        // Si le type de s�jour n'est pas support� par ce destinataire
        if ($exclude_admit_type = $receiver->_configs["exclude_admit_type"]) {
            $exclude_admit_types = explode(",", $exclude_admit_type);
            foreach ($exclude_admit_types as $_exclude_admit_type) {
                if ($_exclude_admit_type == $sejour->type) {
                    return false;
                }
            }
        }

        foreach ($sejour->_fusion as $group_id => $infos_fus) {
            if ($receiver->group_id != $group_id) {
                continue;
            }

            $sejour1_nda = $sejour->_NDA = $infos_fus["sejour1_nda"];

            /** @var CSejour $sejour_elimine */
            $sejour_elimine = $infos_fus["sejourElimine"];
            $sejour2_nda    = $sejour_elimine->_NDA = $infos_fus["sejour2_nda"];

            // Suppression de tous les mouvements du s�jours � �liminer
            $movements = $sejour_elimine->loadRefsMovements();
            foreach ($movements as $_movement) {
                $_movement->cancel = 1;
                $_movement->store();
            }

            // Cas 0 NDA : Aucune notification envoy�e
            if (!$sejour1_nda && !$sejour2_nda) {
                continue;
            }

            // Cas 1 NDA : Pas de message de fusion mais d'une modification de s�jour
            if ($sejour1_nda xor $sejour2_nda) {
                if ($sejour2_nda) {
                    $sejour->_NDA = $sejour2_nda;
                }

                $code = $this->getModificationAdmitCode($receiver);
                if (!$this->isMessageSupported($this->message, $code, $receiver)) {
                    continue;
                }

                $this->createMovement($code, $sejour);

                $sejour->loadRefPatient();

                $this->sendITI($this->profil, $this->transaction, $this->message, $code, $sejour);
                continue;
            }

            $receiver->loadConfigValues();
            // Cas 2 NDA : message de fusion si la configuration est activ�
            if ($sejour1_nda && $sejour2_nda) {
                $code = $receiver->_configs["send_a42_onmerge"] ? "A42" : $this->getModificationAdmitCode($receiver);
                if (!$this->isMessageSupported($this->message, $code, $receiver)) {
                    continue;
                }

                $this->createMovement($code, $sejour);
                $sejour->loadRefPatient();
                $sejour->_sejour_elimine = $sejour_elimine;
                $this->sendITI($this->profil, $this->transaction, $this->message, $code, $sejour);
                continue;
            }
        }
    }

    /**
     * Trigger before event delete
     *
     * @param CStoredObject $mbObject Object
     *
     * @return null|bool
     * @throws CMbException
     */
    function onBeforeDelete(CStoredObject $mbObject)
    {
        if (!$this->isHandled($mbObject)) {
            return false;
        }

        $receiver = $mbObject->_receiver;
        $receiver->getInternationalizationCode($this->transaction);

        // Traitement Affectation
        if ($mbObject instanceof CAffectation) {
            $affectation = $mbObject;

            if ($affectation->_no_synchro_eai) {
                return false;
            }

            $sejour  = $affectation->loadRefSejour();
            $patient = $sejour->loadRefPatient();
            $patient->loadIPP($sejour->group_id);

            if (!$sejour->_id || $sejour->_etat == "preadmission") {
                return false;
            }

            // Si le type de s�jour n'est pas support� par ce destinataire
            if ($exclude_admit_type = $receiver->_configs["exclude_admit_type"]) {
                $exclude_admit_types = explode(",", $exclude_admit_type);
                foreach ($exclude_admit_types as $_exclude_admit_type) {
                    if ($_exclude_admit_type == $sejour->type) {
                        return;
                    }
                }
            }

            // Si le group_id du s�jour est diff�rent de celui du destinataire
            if ($sejour->group_id != $receiver->group_id) {
                return false;
            }

            if ($sejour->annule) {
                return false;
            }

            if (CModule::getActive("appFineClient")) {
                if ($receiver->_configs['send_evenement_to_mbdmp']) {
                    if (!CAppFineClient::checkRGPD($patient, $receiver->group_id)) {
                        return false;
                    }
                }

                if ($receiver->_configs['send_evenement_to_mbdmp'] && !CAppFineClient::loadIdex($patient)->_id) {
                    return false;
                }
            }

            $current_affectation = $sejour->loadRefFirstAffectation();

            /* Annulation de l'affectation dans un service externe */
            $service = $affectation->loadRefService();
            if ($service->externe) {
                // Affectation effectu�e
                if ($affectation->effectue) {
                    $code = "A53";
                } else {
                    $code = "A52";
                }
            } // Si c'est la premi�re affectation (pas de mutation) il faut attendre le delete pour que le PV1.3 soit complet
            elseif ($affectation->_id == $current_affectation->_id) {
                return false;
            } else {
                // Annulation (suppression) d'une affectation
                $code = "A12";
            }

            if (!$code) {
                return false;
            }

            // Cas o� :
            // * on est l'initiateur du message
            // * le destinataire ne supporte pas le message
            if (!$this->isMessageSupported($this->message, $code, $receiver)) {
                return false;
            }

            $sejour->_receiver = $receiver;

            if (!$this->createMovement($code, $sejour, $affectation)) {
                return false;
            }

            // Envoi de l'�v�nement
            $this->sendITI($this->profil, $this->transaction, $this->message, $code, $mbObject);

            return true;
        }
    }

    /**
     * Trigger after event delete
     *
     * @param CStoredObject $mbObject Object
     *
     * @return null|bool
     * @throws CMbException
     */
    function onAfterDelete(CStoredObject $mbObject)
    {
        if (!$this->isHandled($mbObject)) {
            return false;
        }

        $receiver = $mbObject->_receiver;
        $receiver->getInternationalizationCode($this->transaction);

        // Traitement Affectation
        if ($mbObject instanceof CAffectation) {
            $affectation = $mbObject;

            $sejour  = $affectation->loadRefSejour();
            $patient = $sejour->loadRefPatient();
            $patient->loadIPP($sejour->group_id);

            if (CModule::getActive("appFineClient")) {
                if ($receiver->_configs['send_evenement_to_mbdmp']) {
                    if (!CAppFineClient::checkRGPD($patient, $receiver->group_id)) {
                        return;
                    }
                }

                if ($receiver->_configs['send_evenement_to_mbdmp'] && !CAppFineClient::loadIdex($patient)->_id) {
                    return false;
                }
            }

            // Si le type de s�jour n'est pas support� par ce destinataire
            if ($exclude_admit_type = $receiver->_configs["exclude_admit_type"]) {
                $exclude_admit_types = explode(",", $exclude_admit_type);
                foreach ($exclude_admit_types as $_exclude_admit_type) {
                    if ($_exclude_admit_type == $sejour->type) {
                        return;
                    }
                }
            }

            if (!$sejour->_id) {
                return false;
            }

            if ($sejour->annule) {
                return false;
            }

            // Si le group_id du s�jour est diff�rent de celui du destinataire
            if ($sejour->group_id != $receiver->group_id) {
                return false;
            }

            $current_affectation = $sejour->loadRefFirstAffectation();
            if ($current_affectation && $current_affectation->_id) {
                return false;
            }

            $code = "Z99";

            // Cas o� :
            // * on est l'initiateur du message
            // * le destinataire ne supporte pas le message
            if (!$this->isMessageSupported($this->message, $code, $receiver)) {
                return false;
            }

            $sejour->_receiver = $receiver;
            $sejour->loadOldObject();

            if (!$this->createMovement($code, $sejour, $affectation)) {
                return false;
            }

            // Envoi de l'�v�nement
            $this->sendITI($this->profil, $this->transaction, $this->message, $code, $mbObject);

            return true;
        }
    }
}
