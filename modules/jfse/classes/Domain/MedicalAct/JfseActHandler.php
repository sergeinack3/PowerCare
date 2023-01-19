<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\MedicalAct;

use Ox\Core\CAppUI;
use Ox\Core\CStoredObject;
use Ox\Core\Handlers\ObjectHandler;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Ccam\CActe;
use Ox\Mediboard\Jfse\DataModels\CJfseAct;
use Ox\Mediboard\Jfse\DataModels\CJfseInvoice;
use Ox\Mediboard\Jfse\Domain\Invoicing\InvoicingService;
use Ox\Mediboard\Jfse\Exceptions\JfseException;
use Ox\Mediboard\Jfse\Utils;

final class JfseActHandler extends ObjectHandler
{
    /** @var string[] */
    public static $handled = ['CActeCCAM', 'CActeLPP', 'CActeNGAP'];

    /**
     * @inheritdoc
     */
    public static function isHandled(CStoredObject $object): ?bool
    {
        if (!CModule::getActive("jfse")) {
            return false;
        }

        return in_array($object->_class, self::$handled) && !$object->_ignore_eai_handlers;
    }

    /**
    * @see parent::onAfterStore()
    */
    public function onAfterStore(CStoredObject $object): bool
    {
        /** @var CActe $object */
        if (
            !$this->isHandled($object) || !$object->_ref_current_log || CAppUI::pref('LogicielFSE') != 'jfse'
            || !$object->_id || $object->object_class !== 'CConsultation'
        ) {
            return false;
        }

        /** @var CConsultation $consultation */
        $consultation = $object->loadTargetObject();
        if ($consultation->countBackRefs('jfse_invoices')) {
            if ($object->_ref_current_log->type === 'create') {
                $invoice_data_model = InvoicingService::getFirstPendingInvoiceFromConsultation($consultation);
            } else {
                /** @var CJfseAct $link */
                $link = $object->loadUniqueBackRef('jfse_act_link');
                $invoice_data_model = $link->loadInvoice();
                if (!$invoice_data_model->isPending()) {
                    return false;
                }
            }

            /* We set the user Jfse id in the cache for the authorisation token */
            Utils::setJfseUserIdFromConsultation($consultation);

            $service = new MedicalActService();

            try {
                $service->setMedicalAct($invoice_data_model->jfse_id, $object);
            } catch (JfseException $e) {
                return false;
            }
        }

        return true;
    }

    public function onBeforeDelete(CStoredObject $object): bool
    {
        if (
            !$this->isHandled($object)
            || !$object->_id || $object->object_class !== 'CConsultation'
        ) {
            return false;
        }

        if ($object->countBackRefs('jfse_act_link')) {
            /** @var CJfseAct $act_data_model */
            $act_data_model = $object->loadUniqueBackRef('jfse_act_link');
            $invoice_data_model = $act_data_model->loadInvoice();

            /* We set the user Jfse id in the cache for the authorisation token */
            $object->completeField('executant_id');
            Utils::setJfseUserIdFromMediuser($object->loadRefExecutant());

            if ($invoice_data_model->_id) {
                $service = new MedicalActService();

                try {
                    $service->deleteMedicalAct($invoice_data_model, $act_data_model->jfse_id);
                } catch (JfseException $e) {
                    return false;
                }
            } else {
                $act_data_model->delete();
            }
        }

        return true;
    }
}
