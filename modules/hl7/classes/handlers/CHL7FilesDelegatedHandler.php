<?php
/**
 * @package Mediboard\Ihe
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\handlers;

use Ox\Core\CStoredObject;
use Ox\Interop\Hl7\CReceiverHL7v2;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CDocumentItem;

/**
 * Class CHL7FilesDelegatedHandler
 * HL7 files delegated Handler
 */
class CHL7FilesDelegatedHandler extends CHL7DelegatedHandler
{
    /** @var string[] Classes eligible for handler */
    protected static $handled = ['CFile', 'CCompteRendu'];

    /**
     * Trigger after event store
     *
     * @param CStoredObject $mbObject Object
     *
     * @return bool
     */
    public function onAfterStore(CStoredObject $mbObject): bool
    {
        /** @var CDocumentItem $docItem */
        $docItem = $mbObject;

        if (!parent::onAfterStore($docItem)) {
            return false;
        }

        if (!$this->canSendFile($docItem)) {
            return false;
        }

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
        /** @var CDocumentItem $docItem */
        $docItem = $mbObject;

        if (!parent::onAfterDelete($docItem)) {
            return false;
        }

        if (!$this->canSendFile($docItem)) {
            return false;
        }

        return true;
    }

    /**
     * @param CDocumentItem|CStoredObject $docItem
     *
     * @return bool
     */
    private function canSendFile(CStoredObject $docItem): bool
    {
        /** @var CReceiverHL7v2 $receiver */
        $receiver = $docItem->_receiver;

        if (!$docItem->send) {
            return false;
        }

        if ($docItem->_no_synchro_eai) {
            return false;
        }

        // Dans le cas d'un modèle pour le compte-rendu
        if ($docItem instanceof CCompteRendu && !$docItem->object_id) {
            return false;
        }

        if ($receiver->_configs['files_mode_sas']) {
            return false;
        }

        // Dans le cas d'une suppression du fichier, on va chercher la target sur le OldObject
        $target = $docItem->loadTargetObject();
        if (!$target || !$target->_id) {
            return false;
        }

        return true;
    }
}
