<?php
/**
 * @package Mediboard\Ihe
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\handlers;

use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CStoredObject;
use Ox\Core\Module\CModule;
use Ox\Erp\CabinetSIH\CCabinetSIH;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Hl7\CReceiverHL7v2;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\CPreferences;

/**
 * Class CORUDelegatedHandler
 * ORU Delegated Handler
 */
class CORUDelegatedHandler extends CHL7FilesDelegatedHandler
{
    /**
     * @var string
     */
    public $profil = "CHL7ORU";
    /**
     * @var string
     */
    public $message = "ORU";

    /**
     * Trigger after event store
     *
     * @param CStoredObject $mbObject Object
     *
     * @return bool
     * @throws CMbException
     */
    public function onAfterStore(CStoredObject $mbObject): bool
    {
        $docItem = $mbObject;
        if (!parent::onAfterStore($docItem)) {
            return false;
        }

        /** @var CReceiverHL7v2 $receiver */
        $receiver = $mbObject->_receiver;

        $code = 'R01';
        if (!$this->isMessageSupported($this->message, $code, $receiver, $this->profil)) {
            return false;
        }

        $target = $mbObject->loadTargetObject();
        // Cas de TAMM-SIH
        if (CModule::getActive('oxCabinet') && $receiver->type === CInteropActor::ACTOR_MEDIBOARD) {
            // Le fichier n'est pas sur un événement patient
            if (!$target instanceof CEvenementPatient) {
                return false;
            }

            // L'événement n'est pas synchro avec le SIH
            $idex = CCabinetSIH::loadIdex($target);
            if (!$idex->_id) {
                return false;
            }

            // On n'envoie pas l'annulation au SIH si le doc n'a pas été créé dans TAMM
            $idex_context_guid_sih = CCabinetSIH::loadIdex($mbObject, CCabinetSIH::CONTEXT_GUID_SIH_TAG);
            if ($idex_context_guid_sih && $idex_context_guid_sih->_id && $mbObject->annule == 1) {
                return false;
            }

            // Le praticien de l'événement n'utilise pas TAMM-SIH
            $praticien = $target->loadRefPraticien();
            $prefs     = CPreferences::getAllPrefs($praticien->_id, true);
            if (!CMbArray::get($prefs, 'useTAMMSIH')) {
                return false;
            }
        } elseif (
            !$target instanceof CSejour && !$target instanceof COperation && !$target instanceof CConsultation
            && !$target instanceof CPatient
        ) {
            return false;
        }

        // On envoie le flux R01
        $this->sendEvent($this->message, $code, $mbObject, $this->profil);

        return true;
    }

    /**
     * Trigger after event delete
     *
     * @param CStoredObject $mbObject Object
     *
     * @return bool
     */
    public function onAfterDelete(CStoredObject $mbObject): bool
    {
        /** @var CReceiverHL7v2 $receiver */
        $receiver = $mbObject->_receiver;

        /** @var CDocumentItem $docItem */
        $docItem = $mbObject->loadOldObject();
        if (!$docItem || !$docItem->_id) {
            return false;
        }

        $docItem->_receiver = $receiver;

        if (!parent::onAfterDelete($docItem)) {
            return false;
        }

        $code = 'R01';
        if (!$this->isMessageSupported($this->message, $code, $receiver, $this->profil)) {
            return false;
        }

        $target = $docItem->loadTargetObject();
        // Cas de TAMM-SIH
        if (CModule::getActive('oxCabinet') && $receiver->type === CInteropActor::ACTOR_MEDIBOARD) {
            // Le fichier n'est pas sur un événement patient
            if (!$target instanceof CEvenementPatient) {
                return false;
            }

            // L'événement n'est pas synchro avec le SIH
            $idex = CCabinetSIH::loadIdex($target);
            if (!$idex->_id) {
                return false;
            }

            // On n'envoie pas la suppression au SIH si le doc n'a pas été créé dans TAMM
            $idex_context_guid_sih = CCabinetSIH::loadIdex($docItem, CCabinetSIH::CONTEXT_GUID_SIH_TAG);
            if ($idex_context_guid_sih && $idex_context_guid_sih->_id) {
                return false;
            }

            // Le praticien de l'événement n'utilise pas TAMM-SIH
            $praticien = $target->loadRefPraticien();
            $prefs     = CPreferences::getAllPrefs($praticien->_id, true);
            if (!CMbArray::get($prefs, 'useTAMMSIH')) {
                return false;
            }
        } elseif (
            !$target instanceof CSejour && !$target instanceof COperation && !$target instanceof CConsultation
            && !$target instanceof CPatient
        ) {
            return false;
        }

        // On force le champ annule à 1
        $docItem->annule = 1;

        // On envoie le flux R01
        $this->sendEvent($this->message, $code, $docItem, $this->profil);

        return true;
    }
}
