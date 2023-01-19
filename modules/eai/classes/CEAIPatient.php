<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbRange;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\Sante400\CIncrementer;

/**
 * Class CEAIPatient
 * Patient utilities EAI
 */
class CEAIPatient extends CEAIMbObject
{
    /**
     * Recording the external identifier of the CIP
     *
     * @param CIdSante400    $idex            Object id400
     * @param CInteropSender $sender          Sender
     * @param int            $idSourcePatient External identifier
     * @param CPatient       $newPatient      Patient
     *
     * @return null|string null if successful otherwise returns and error message
     */
    public static function storeID400CIP(
        CIdSante400 $idex,
        CInteropSender $sender,
        $idSourcePatient,
        CPatient $newPatient
    ) {
        //Paramétrage de l'id 400
        $idex->object_class = "CPatient";
        $idex->tag          = $sender->_tag_patient;
        $idex->id400        = $idSourcePatient;
        $idex->object_id    = $newPatient->_id;
        $idex->_id          = null;

        return $idex->store();
    }

    /**
     * Recording IPP
     *
     * @param CIdSante400    $IPP     Object id400
     * @param CPatient       $patient Patient
     * @param CInteropSender $sender  Sender
     *
     * @return null|string null if successful otherwise returns and error message
     */
    public static function storeIPP(CIdSante400 $IPP, CPatient $patient, CInteropSender $sender)
    {
        /* Gestion du numéroteur */
        $group = new CGroups();
        $group->load($sender->group_id);
        $group->loadConfigValues();

        // Dans le cas d'une identité non qualifiée, on peut forcer de ne pas générer un IPP
        if ($patient->status === 'VIDE' && !CMbArray::get($sender->_configs, "generate_IPP_unqualified_identity")) {
            return null;
        }

        // Purge de l'IPP existant sur le patient et on le remplace par le nouveau
        if ($sender->_configs && $sender->_configs["purge_idex_movements"]) {
            // On charge l'IPP courant du patient
            $patient->loadIPP($sender->group_id);

            $ref_IPP = $patient->_ref_IPP;

            if ($ref_IPP) {
                // Si l'IPP actuel est identique à celui qu'on reçoit on ne fait rien
                if ($ref_IPP->id400 == $IPP->id400) {
                    return null;
                }

                // On passe l'IPP courant en trash
                $ref_IPP->tag              = CAppUI::conf("dPpatients CPatient tag_ipp_trash") . $ref_IPP->tag;
                $ref_IPP->_eai_sender_guid = $sender->_guid;
                $ref_IPP->store();

                $patient->trashIPP($ref_IPP);
            }

            // On sauvegarde le nouveau
            $IPP->tag              = $sender->_tag_patient;
            $IPP->object_class     = "CPatient";
            $IPP->object_id        = $patient->_id;
            $IPP->_eai_sender_guid = $sender->_guid;

            return $IPP->store();
        }

        // Génération de l'IPP ?
        /* @todo sip_idex_generator doit être remplacé par isIPPSupplier */
        if ($sender->_configs && !$group->_configs["sip_idex_generator"]) {
            if (!$IPP->id400) {
                return null;
            }

            $IPP_temp = CIdSante400::getMatchFor($patient, $sender->_tag_patient);
            // Dans le cas où l'on est sur le même idex
            if ($IPP_temp->_id && ($IPP->id400 === $IPP_temp->id400)) {
                return null;
            } elseif ($IPP_temp->_id && ($IPP->id400 !== $IPP_temp->id400)) {
                // Dans le cas contraire, on passe l'IPP courant en trash
                $IPP_temp->_eai_sender_guid = $sender->_guid;
                $patient->trashIPP($IPP_temp);
            }
            $IPP->object_id = $patient->_id;
            $IPP->_eai_sender_guid = $sender->_guid;

            return $IPP->store();
        } else {
            $IPP_temp = CIdSante400::getMatch("CPatient", $sender->_tag_patient, null, $patient->_id);

            // Pas d'IPP passé
            if (!$IPP->id400) {
                if ($IPP_temp->_id) {
                    return null;
                }

                if (!CIncrementer::generateIdex($patient, $sender->_tag_patient, $sender->group_id)) {
                    return CAppUI::tr("CEAIPatient-error-generate-idex");
                }

                return null;
            } else {
                // Si j'ai déjà un identifiant
                if ($IPP_temp->_id) {
                    // On passe l'IPP courant en trash
                    $IPP_temp->tag              = CAppUI::conf("dPpatients CPatient tag_ipp_trash") . $IPP_temp->tag;
                    $IPP_temp->_eai_sender_guid = $sender->_guid;
                    $IPP_temp->store();
                }

                $incrementer = $sender->loadRefGroup()->loadDomainSupplier("CPatient");
                if ($incrementer && $incrementer->manage_range && !CMbRange::in(
                        $IPP->id400,
                        $incrementer->range_min,
                        $incrementer->range_max
                    )) {
                    return CAppUI::tr("CEAIPatient-idex-not-in-the-range");
                }

                $IPP->object_id        = $patient->_id;
                $IPP->_eai_sender_guid = $sender->_guid;

                return $IPP->store();
            }
        }
    }

    /**
     * Recording RI sender
     *
     * @param string         $RI_sender Idex value
     * @param CPatient       $patient   Patient
     * @param CInteropSender $sender    Sender
     *
     * @return null|string null if successful otbug: création d'un idex sans tag si on a pas de domaine associé au
     *                     senderherwise returns and error message
     */
    public static function storeRISender($RI_sender, CPatient $patient, CInteropSender $sender)
    {
        $domain = $sender->loadRefDomain();
        if (!$domain->_id) {
            return;
        }

        $idex = new CIdSante400();
        $idex->setObject($patient);
        $idex->tag   = $domain->tag;
        $idex->id400 = $RI_sender;

        return $idex->store();
    }

    /**
     * Recording other identifiers
     *
     * @param array    $identifiers Collection identifiers
     * @param CPatient $patient     Patient
     *
     * @return void
     */
    public static function storeOtherIdentifiers($identifiers, CPatient $patient)
    {
        foreach ($identifiers as $tag => $value) {
            $idex = new CIdSante400();
            $idex->setObject($patient);
            $idex->tag = $tag;
            $idex->loadMatchingObject();
            $idex->id400 = $value;
            $idex->store();
        }
    }

    /**
     * Recording patient
     *
     * @param CPatient       $newPatient  Patient
     * @param CInteropSender $sender      Sender
     * @param bool           $generateIPP Generate IPP ?
     * @param array          $data        Data
     *
     * @return null|string null if successful otherwise returns and error message
     */
    public static function storePatient(CPatient $newPatient, CInteropSender $sender, $generateIPP = false, $data = [])
    {
        // Notifier les autres destinataires autre que le sender
        $newPatient->_eai_sender_guid = $sender->_guid;
        // @todo gérer le rebond vers les tiers par le routeur
        $newPatient->_no_synchro_eai = true;
        $newPatient->_generate_IPP   = $generateIPP;

        // Dans le cas d'une identité non qualifiée, on peut forcer de ne pas générer un IPP
        if ($newPatient->status === 'VIDE' && !CMbArray::get($sender->_configs, "generate_IPP_unqualified_identity")) {
            $newPatient->_generate_IPP = false;
        }

        $responsable_compte     = $newPatient->_responsable_compte;
        $correspond_responsable = $newPatient->_correspond_responsable;

        if ($msg = $newPatient->store()) {
            if ($sender->_configs && $sender->_configs["repair_patient"]) {
                $newPatient->repair();
            }

            // Notifier les autres destinataires autre que le sender
            $newPatient->_eai_sender_guid = $sender->_guid;
            $newPatient->_generate_IPP    = $generateIPP;

            if ($msg = $newPatient->store()) {
                return $msg;
            }
        }

        $newPatient->_responsable_compte     = $responsable_compte;
        $newPatient->_correspond_responsable = $correspond_responsable;
    }

    /**
     * Recording patient
     *
     * @param CPatient    $newPatient Patient
     * @param CIdSante400 $IPP        Object id400
     *
     * @return null|string null if successful otherwise returns and error message
     */
    public static function storePatientSIP(CPatient $newPatient, $IPP)
    {
        $newPatient->_IPP = $IPP;

        return $newPatient->store();
    }
}
