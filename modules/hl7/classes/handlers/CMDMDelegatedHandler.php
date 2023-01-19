<?php
/**
 * @package Mediboard\Ihe
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\handlers;

use Ox\Core\CMbException;
use Ox\Core\CStoredObject;
use Ox\Interop\Hl7\CReceiverHL7v2;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CMDMDelegatedHandler
 * MDM Delegated Handler
 */
class CMDMDelegatedHandler extends CHL7FilesDelegatedHandler
{
    /**
     * @var string
     */
    public $profil = "CHL7MDM";
    /**
     * @var string
     */
    public $message = "MDM";

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
        $receiver = $docItem->_receiver;

        switch ($docItem->_ref_current_log->type) {
            case "create":
                // Envoi d'un document initial
                $code = "T02";
                break;
            default:
                // Notification de changement du statut du document, accompagnée du document en question
                $code = "T04";
                break;
        }

        // Dans le cas où le document change de version on va transmettre un message T10
        if ($docItem instanceof CCompteRendu && $docItem->fieldModified('version')) {
            $code = "T10";
        }

        if (!$this->isMessageSupported($this->message, $code, $receiver, $this->profil)) {
            return false;
        }

        $target = $docItem->loadTargetObject();
        if (!$target || !$target->_id) {
            return false;
        }
        if (
            !$target instanceof CSejour && !$target instanceof COperation && !$target instanceof CConsultation
            && !$target instanceof CPatient
        ) {
            return false;
        }

        // On envoie le flux T02/T04/T10
        $this->sendEvent($this->message, $code, $docItem, $this->profil);

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

        $code = 'T04';
        if (!$this->isMessageSupported($this->message, $code, $receiver, $this->profil)) {
            return false;
        }

        $target = $docItem->loadTargetObject();
        if (
            !$target instanceof CSejour && !$target instanceof COperation && !$target instanceof CConsultation
            && !$target instanceof CPatient
        ) {
            return false;
        }

        // On force le champ annule à 1
        $docItem->annule = 1;

        // On envoie le flux T04
        $this->sendEvent($this->message, $code, $docItem, $this->profil);

        return true;
    }
}
