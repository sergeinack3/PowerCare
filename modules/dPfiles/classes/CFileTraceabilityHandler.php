<?php

/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Exception;
use Ox\Core\CStoredObject;
use Ox\Core\Handlers\ObjectHandler;
use Ox\Core\Module\CModule;
use Ox\Interop\Dmp\CDMPSas;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;

/**
 * Domain handler
 */
class CFileTraceabilityHandler extends ObjectHandler
{
    /** @var string[] */
    protected static $handled = ["CFile", "CCompteRendu"];

    /** @var bool */
    public $create  = false;

    /**
     * @inheritdoc
     */
    public static function isHandled(CStoredObject $object)
    {
        return in_array($object->_class, self::$handled);
    }

    /**
     * @inheritdoc
     */
    public function onBeforeStore(CStoredObject $object)
    {
        if (!$this->isHandled($object)) {
            return false;
        }

        if (!$object->_id) {
            $this->create = true;
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function onAfterStore(CStoredObject $object): bool
    {
        if (!$this->isHandled($object)) {
            return false;
        }

        /** @var CDocumentItem $docItem */
        $docItem = $object;

        // Si on vient de retirer le type doc dmp => on enlève les traces
        if (CModule::getActive('dmp')) {
            $old_object = $docItem->loadOldObject();
            if ($old_object && $old_object->_id && $old_object->type_doc_dmp && !$docItem->type_doc_dmp) {
                CFileTraceability::deleteTrace($docItem, CDMPSas::getTag());
            }
        }

        if ($docItem->annule) {
            CFileTraceability::deleteTrace($docItem);

            return false;
        }

        // Document non finalisé dans Mediboard
        if (!$docItem->send) {
            return false;
        }

        // Si pas de catégorie on ne peut pas créer de trace
        if (!$docItem->file_category_id) {
            return false;
        }

        $file_category = $docItem->loadRefCategory();

        // Si la catégorie n'est pas éligible à une remontée d'alerte
        if (!$file_category->send_auto) {
            return false;
        }

        $where                                      = [];
        $where["files_category_to_receiver.active"] = "= '1'";
        if (!$file_category->countRelatedReceivers($where) > 0) {
            return false;
        }

        $target = $docItem->loadTargetObject();
        if (
            !$target instanceof CSejour && !$target instanceof CConsultation
            && !$target instanceof CConsultAnesth && !$target instanceof COperation
            && !$target instanceof CPrescription
        ) {
            return false;
        }

        // Dans le cas d'un compte-rendu, il faut envoyer au sas que si c'est une création ou que le contenu a changé
        if ($docItem instanceof CCompteRendu && $docItem->_old->_id && !$docItem->fieldModified('version')) {
            return false;
        }

        foreach ($file_category->loadRefRelatedReceivers($where) as $_related_receivers) {
            /** @var CFilesCategoryToReceiver $_related_receivers */
            $receiver = $_related_receivers->loadRefReceiver();

            if (!$receiver->_id) {
                continue;
            }
            CFileTraceability::createTrace($docItem, $receiver);
        }

        return true;
    }
}
