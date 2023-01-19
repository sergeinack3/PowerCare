<?php
/**
 * @package Mediboard\Ihe
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ihe;

use Exception;
use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CStoredObject;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Mediboard\Patients\CCorrespondant;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientLink;
use Ox\Mediboard\Patients\CSourceIdentite;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class CITI30DelegatedHandler
 * ITI30 Delegated Handler
 */
class CITI30DelegatedHandler extends CITIDelegatedHandler
{
    /**
     * @var array
     */
    static $handled = [
        "CPatient",
        "CCorrespondantPatient",
        "CIdSante400",
        "CPatientLink",
        "CCorrespondant",
    ];

    /**
     * @var string
     */
    public $profil = "PAM";
    /**
     * @var string
     */
    public $message = "ADT";
    /**
     * @var string
     */
    public $transaction = "ITI30";

    /**
     * Trigger after event store
     *
     * @param CStoredObject $mbObject Object
     *
     * @return false
     * @throws CMbException
     */
    function onAfterStore(CStoredObject $mbObject)
    {
        if (!$this->isHandled($mbObject)) {
            return false;
        }

        if ($mbObject->_forwardRefMerging) {
            return false;
        }

        /** @var CInteropReceiver $receiver */
        $receiver = $mbObject->_receiver;
        $receiver->getInternationalizationCode($this->transaction);

        $code = null;

        if ($mbObject instanceof CCorrespondantPatient) {
            // Création/MAJ d'un correspondant patient
            if (!$mbObject->patient_id) {
                return false;
            }
            $mbObject            = $mbObject->loadRefPatient();
            $mbObject->_receiver = $receiver;

            if (CModule::getActive("appFineClient")) {
                if ($receiver->_configs['send_evenement_to_mbdmp'] && !CAppFineClient::loadIdex(
                        $mbObject,
                        $receiver->group_id
                    )->_id) {
                    return false;
                }
            }

            // Si le group_id du patient est différent de celui du destinataire
            if (CAppUI::isGroup() && $mbObject->group_id && ($mbObject->group_id != $receiver->group_id)) {
                return false;
            }

            $code    = "A31";
            $patient = $mbObject;
        } elseif ($mbObject instanceof CIdSante400) {
            // MAJ de l'IPP du patient
            $idex = $mbObject;

            // Concerne pas les patients / Pas en mode modification
            if ($idex->object_class != "CPatient" || !$idex->_old->_id) {
                return false;
            }

            // Pas un tag IPP
            if ($idex->tag != CPatient::getTagIPP()) {
                return false;
            }

            // Vraiment une modif de l'idex ?
            if ($idex->id400 == $idex->_old->id400) {
                return false;
            }

            $code = "A47";

            $patient = new CPatient();
            $patient->load($idex->object_id);
            $patient->_receiver = $receiver;

            $patient->_patient_elimine = clone $patient;

            // Affecte le nouvel IPP au patient
            $patient->_IPP = $idex->id400;

            // Affecte l'ancien IPP au "patient éliminé"
            $patient->_patient_elimine->_IPP = $idex->_old->id400;

            if (CModule::getActive("appFineClient")) {
                if ($receiver->_configs['send_evenement_to_mbdmp'] && !CAppFineClient::loadIdex(
                        $patient,
                        $receiver->group_id
                    )->_id) {
                    return false;
                }
            }
        } elseif ($mbObject instanceof CPatientLink) {
            // Création d'un lien de deux patients
            if ($mbObject->_ref_current_log->type !== "create") {
                return false;
            }
            $code = "A24";

            $patient = $mbObject->loadRefPatient1();

            $patient->_patient_link = $mbObject->loadRefPatient2();
            $patient->_receiver     = $receiver;

            // Si le group_id du patient est différent de celui du destinataire
            if (CAppUI::isGroup() && $patient->group_id && ($patient->group_id != $receiver->group_id)) {
                return false;
            }

            if (CModule::getActive("appFineClient")) {
                if ($receiver->_configs['send_evenement_to_mbdmp'] && !CAppFineClient::loadIdex(
                        $patient,
                        $receiver->group_id
                    )->_id) {
                    return false;
                }
            }
        } elseif ($mbObject instanceof CCorrespondant) {
            // Création/MAJ d'un médecin correspondant
            $code = "A31";

            $patient = $mbObject->loadRefPatient();
            if (!$patient || !$patient->_id) {
                return false;
            }

            $patient->_receiver              = $receiver;
            $patient->_current_correspondant = $mbObject;

            // Si le group_id du patient est différent de celui du destinataire
            if (CAppUI::isGroup() && $patient->group_id && ($patient->group_id != $receiver->group_id)) {
                return false;
            }

            if (CModule::getActive("appFineClient")) {
                if ($receiver->_configs['send_evenement_to_mbdmp'] && !CAppFineClient::loadIdex(
                        $patient,
                        $receiver->group_id
                    )->_id) {
                    return false;
                }
            }
        } elseif ($mbObject instanceof CPatient) {
            $patient = $mbObject;

            // Création / modification d'un patient
            /** @var CPatient $patient */
            if (CModule::getActive("appFineClient")) {
                if ($receiver->_configs['send_evenement_to_mbdmp'] && !CAppFineClient::loadIdex(
                        $patient,
                        $receiver->group_id
                    )->_id) {
                    return false;
                }
            }

            // Si le group_id du patient est différent de celui du destinataire
            if (CAppUI::isGroup() && $patient->group_id && ($patient->group_id != $receiver->group_id)) {
                return false;
            }

            if ($patient->_no_synchro_eai) {
                return false;
            }

            switch ($patient->_ref_current_log->type) {
                case "create":
                    $code = "A28";
                    break;
                case "store":
                    if ($receiver->_configs["send_patient_with_visit"]) {
                        $sejour = $patient->loadRefsSejours(["entree_reelle" => "IS NOT NULL"]);
                        if (count($sejour) < 1) {
                            $code = null;
                            break;
                        }
                    }

                    if ($receiver->_configs["send_patient_with_current_admit"]) {
                        // On charge seulement le séjour courant pour le patient
                        $sejours = $patient->getCurrSejour(null, $receiver->group_id);
                        if (!$sejours) {
                            break;
                        }

                        $sejour = reset($sejours);
                        if (!$sejour->_id) {
                            break;
                        }

                        $patient->_ref_sejour = $sejour;
                    }

                    // Dans le cas où l'on modifie une source d'identité sur le patient
                    if ($patient->fieldModified('source_identite_id')) {
                        $current_source_identite = $patient->loadRefSourceIdentite();

                        // Ancienne source d'identité
                        /** @var CPatient $patient_old */
                        $patient_old         = $patient->_old;
                        $source_identity_old = new CSourceIdentite();
                        $source_identity_old->load($patient_old->source_identite_id);

                        // Si on reste sur une source d'identité INSi
                        if ($current_source_identite->getModeObtention() === CSourceIdentite::MODE_OBTENTION_INSI) {
                            // On était sur une source INSi
                            if ($source_identity_old->getModeObtention() === CSourceIdentite::MODE_OBTENTION_INSI) {
                                $patient_ins_nir = $current_source_identite->_ref_patient_ins_nir;
                                // L'INS-NIR est un NIA, on va faire un A31 avec les 2 identifiants
                                // On change l'INS-NIR, on va faire un A47
                                if ($patient_ins_nir->_is_ins_nia) {
                                    $code = 'A31';
                                } else {
                                    $code                                   = 'A47';
                                    $patient->_disable_insi_identity_source = $source_identity_old;
                                }
                            } else {
                                $code = 'A31';
                            }
                        } elseif ($source_identity_old->getModeObtention() === CSourceIdentite::MODE_OBTENTION_INSI) {
                            // On repasse sur une source d'identité autre que INSi
                            $code = 'A47';

                            $patient->_disable_insi_identity_source = $source_identity_old;
                        }
                    }

                    if (!$code) {
                        // Dans tous les autres cas il s'agit d'une modification
                        $code = ($receiver->_configs["send_update_patient_information"] == "A08") ? "A08" : "A31";
                    }

                    break;

                default:
                    $code = null;
            }
        }

        if (!$code) {
            return false;
        }

        if (!$this->isMessageSupported($this->message, $code, $receiver)) {
            return false;
        }

        if (!$patient->_IPP) {
            $patient->loadIPP($receiver->group_id);
        }

        // Contrôle si l'IPP du patient existe bien dans l'établissement
        if ($patient->_IPP) {
            $idex_temp = CIdSante400::getMatch(
                $patient->_class,
                $patient->getTagIPP($receiver->group_id),
                $patient->_IPP,
                $patient->_id
            );
            if (!$idex_temp->_id) {
                $patient->_IPP = null;
            }
        }

        if (!$patient->_IPP) {
            // Génération de l'IPP dans le cas de la création, ce dernier n'était pas créé
            if ($msg = $patient->generateIPP($receiver->group_id)) {
                CAppUI::setMsg($msg, UI_MSG_ERROR);
            }
        }

        // Envoi pas les patients qui n'ont pas d'IPP
        if (!$receiver->_configs["send_all_patients"] && !$patient->_IPP) {
            return false;
        }

        $this->sendITI($this->profil, $this->transaction, $this->message, $code, $patient);

        $patient->_IPP = null;

        return true;
    }

    /**
     * @inheritDoc
     */
    static function isHandled(CStoredObject $mbObject)
    {
        return in_array($mbObject->_class, self::$handled);
    }

    /**
     * Trigger before event merge
     *
     * @param CStoredObject $mbObject Object
     *
     * @return bool
     */
    function onBeforeMerge(CStoredObject $mbObject)
    {
        if (!$this->isHandled($mbObject)) {
            return false;
        }

        return true;
    }

    /**
     * Trigger when merge failed
     *
     * @param CStoredObject $mbObject Object
     *
     * @return bool
     * @throws Exception
     */
    function onMergeFailure(CStoredObject $mbObject)
    {
        if (!$this->isHandled($mbObject)) {
            return false;
        }

        // On va réatribuer les idexs en cas de problème dans la fusion
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

                // Réattribution sur l'objet non supprimé
                $patient_eliminee = $infos_fus["patientElimine"];
                $idex->object_id  = $patient_eliminee->_id;

                $idex->tag = $tag_name;
                $idex->store();
            }
        }

        return true;
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

        if ($mbObject instanceof CPatient) {
            $patient = $mbObject;
            $patient->check();
            $patient->updateFormFields();

            $receiver = $mbObject->_receiver;
            $receiver->getInternationalizationCode($this->transaction);

            if (CModule::getActive("appFineClient")) {
                foreach ($patient->_fusion as $group_id => $infos_fus) {
                    if ($receiver->group_id != $group_id) {
                        continue;
                    }

                    if (!$receiver->_configs['send_evenement_to_mbdmp']) {
                        continue;
                    }

                    $patient1_appFine = CMbArray::get($infos_fus, "patient1_appFine");
                    $patient2_appFine = CMbArray::get($infos_fus, "patient2_appFine");
                    $patient_elimine  = CMbArray::get($infos_fus, "patientElimine");

                    // Cas 0 identifiant AppFine : Aucune notification envoyée
                    // Cas 1 : les deux patients ont AppFine => on ne fait rien sur AppFine
                    if ((!$patient1_appFine && !$patient2_appFine) || ($patient1_appFine && $patient2_appFine)) {
                        continue;
                    }

                    // Cas 2 identifiant AppFine sur un patient : Message A31 ou A47 (changement d'identifiants)
                    if ($patient1_appFine xor $patient2_appFine) {
                        if ($patient2_appFine) {
                            $patient->_patient_elimine = $patient_elimine;

                            $code = 'A47';
                        }

                        if ($patient1_appFine) {
                            $code = 'A31';
                        }

                        if (!$this->isMessageSupported($this->message, $code, $receiver)) {
                            continue;
                        }

                        $this->sendITI($this->profil, $this->transaction, $this->message, $code, $patient);
                        continue;
                    }
                }
            }

            foreach ($patient->_fusion as $group_id => $infos_fus) {
                if ($receiver->group_id != $group_id) {
                    continue;
                }

                if ($receiver->_configs['send_evenement_to_mbdmp']) {
                    continue;
                }

                $patient1_ipp = $patient->_IPP = $infos_fus["patient1_ipp"];

                $patient_eliminee = $infos_fus["patientElimine"];
                $patient2_ipp     = $patient_eliminee->_IPP = $infos_fus["patient2_ipp"];

                // Cas 0 IPP : Aucune notification envoyée
                if (!$patient1_ipp && !$patient2_ipp) {
                    continue;
                }

                // Cas 1 IPP : Pas de message de fusion mais d'une modification du patient
                if ($patient1_ipp xor $patient2_ipp) {
                    if ($patient2_ipp) {
                        $patient->_IPP = $patient2_ipp;
                    }

                    if ($receiver->_configs["send_patient_with_visit"]) {
                        /** @var CPatient $mbObject */
                        $sejour = $patient->loadRefsSejours(["entree_reelle" => "IS NOT NULL"]);
                        if (count($sejour) < 1) {
                            $code = null;
                            continue;
                        }
                    }

                    if ($receiver->_configs["send_patient_with_current_admit"]) {
                        // On charge seulement le séjour courant pour le patient
                        $sejours = $patient->getCurrSejour(null, $receiver->group_id);
                        if (!$sejours) {
                            continue;
                        }

                        $sejour = reset($sejours);
                        if (!$sejour->_id) {
                            continue;
                        }

                        $patient->_ref_sejour = $sejour;
                    }

                    $code = ($receiver->_configs["send_update_patient_information"] == "A08") ? "A08" : "A31";
                    if (!$this->isMessageSupported($this->message, $code, $receiver)) {
                        return false;
                    }

                    $this->sendITI($this->profil, $this->transaction, $this->message, $code, $patient);
                    continue;
                }

                // Cas 2 IPPs : Message de fusion
                if ($patient1_ipp && $patient2_ipp) {
                    $patient->_patient_elimine = $patient_eliminee;

                    if (!$this->isMessageSupported($this->message, "A40", $receiver)) {
                        return false;
                    }

                    $this->sendITI($this->profil, $this->transaction, $this->message, "A40", $patient);
                    continue;
                }
            }
        }

        return true;
    }

    /**
     * Trigger before event delete
     *
     * @param CStoredObject $mbObject Object
     *
     * @return bool
     * @throws Exception
     */
    function onBeforeDelete(CStoredObject $mbObject)
    {
        if (!$this->isHandled($mbObject)) {
            return false;
        }
        if ($mbObject instanceof CPatientLink) {
            $mbObject->_link_1 = $mbObject->loadRefPatient1();
            $mbObject->_link_2 = $mbObject->loadRefPatient2();
        }

        return true;
    }

    /**
     * Trigger after event delete
     *
     * @param CStoredObject $mbObject Object
     *
     * @return bool
     * @throws CMbException
     */
    function onAfterDelete(CStoredObject $mbObject)
    {
        if (!$this->isHandled($mbObject)) {
            return false;
        }

        $code = null;
        // On gère la suppression des correspondants patient
        if ($mbObject instanceof CCorrespondantPatient) {
            $receiver = $mbObject->_receiver;
            $receiver->getInternationalizationCode($this->transaction);

            $patient            = $mbObject->loadRefPatient();
            $patient->_receiver = $receiver;

            if ($receiver->_configs["send_patient_with_current_admit"]) {
                // On charge seulement le séjour courant pour le patient
                $sejours = $patient->getCurrSejour(null, $receiver->group_id);
                if (!$sejours) {
                    return false;
                }

                $sejour = reset($sejours);
                if (!$sejour->_id) {
                    return false;
                }

                $patient->_ref_sejour = $sejour;
            }

            $code = ($receiver->_configs["send_update_patient_information"] == "A08") ? "A08" : "A31";
        } elseif ($mbObject instanceof CPatientLink) { // Suppression d'un lien de deux patients
            $receiver = $mbObject->_receiver;
            $receiver->getInternationalizationCode($this->transaction);

            $code = "A37";

            $patient                        = $mbObject->_link_1;
            $patient->_old                  = $mbObject->_link_2;
            $id                             = $mbObject->_link_2->_id;
            $patient->_old->patient_link_id = $id;

            $patient->_receiver = $receiver;
        } elseif ($mbObject instanceof CCorrespondant) { // Suppression d'un correspond
            // Création/MAJ d'un médecin correspondant
            $code = "A31";

            $receiver = $mbObject->_receiver;
            $receiver->getInternationalizationCode($this->transaction);

            $patient = $mbObject->loadRefPatient();
            if (!$patient || !$patient->_id) {
                return false;
            }

            $patient->_receiver = $receiver;
            // On stocke temporairement le correspondant médical supprimé
            $patient->_delete_correspondant = $mbObject;
        }

        if (!$code) {
            return false;
        }

        // Si le group_id du patient est différent de celui du destinataire
        if (CAppUI::isGroup() && $patient->group_id && ($patient->group_id != $receiver->group_id)) {
            return false;
        }

        if (!$this->isMessageSupported($this->message, $code, $receiver)) {
            return false;
        }

        if (CModule::getActive("appFineClient")) {
            if ($receiver->_configs['send_evenement_to_mbdmp'] && !CAppFineClient::loadIdex(
                    $patient,
                    $receiver->group_id
                )->_id) {
                return false;
            }
        }

        // Envoi pas les patients qui n'ont pas d'IPP
        if (!$receiver->_configs["send_all_patients"] && !$patient->_IPP) {
            return false;
        }

        $this->sendITI($this->profil, $this->transaction, $this->message, $code, $patient);

        $patient->_IPP = null;

        return true;
    }
}
