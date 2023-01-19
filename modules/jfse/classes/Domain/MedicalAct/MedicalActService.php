<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\MedicalAct;

use Ox\Core\CAppUI;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Ccam\CActe;
use Ox\Mediboard\Jfse\ApiClients\MedicalActClient;
use Ox\Mediboard\Jfse\DataModels\CJfseAct;
use Ox\Mediboard\Jfse\DataModels\CJfseInvoice;
use Ox\Mediboard\Jfse\Domain\AbstractService;
use Ox\Mediboard\Jfse\Domain\Formula\Formula;
use Ox\Mediboard\Jfse\Domain\Invoicing\Invoice;
use Ox\Mediboard\Jfse\Mappers\InvoicingMapper;
use Ox\Mediboard\Jfse\Mappers\MedicalActMapper;
use Ox\Mediboard\Lpp\CActeLPP;

class MedicalActService extends AbstractService
{
    /** @var MedicalActClient */
    protected $client;

    protected const ACT_AGGREGATION_MESSAGE_TYPE_ID = 'M362';

    /** @var string[] Contains a list of NGAP codes that must not be sent to JFSE */
    protected static array $forbidden_ngap_codes = [
        /* The PAV must be handled directly by JFSE, who will determine if it must be added or not */
        'PAV'
    ];

    public function __construct(MedicalActClient $client = null)
    {
        $this->client = $client ?? new MedicalActClient();
    }

    public function setMedicalAct(
        string $invoice_id,
        CActe $act,
        Formula $formula = null,
        InsuranceAmountForcing $amo_amount_forcing = null,
        InsuranceAmountForcing $amc_amount_forcing = null
    ): bool {
        if (!self::checkSendAct($act)) {
            return false;
        }

        $medical_act = MedicalActMapper::medicalActFromCActe($act);

        $data_model = null;
        if ($act->countBackRefs('jfse_act_link')) {
            /** @var CJfseAct $data_model */
            $data_model = $act->loadUniqueBackRef('jfse_act_link');
            $medical_act->setId($data_model->jfse_id);
            $medical_act->loadDataModel();
        }

        if ($formula) {
            $medical_act->setFormula($formula);
        }

        if ($amo_amount_forcing) {
            $medical_act->setAmoAmountForcing($amo_amount_forcing);
        }

        if ($amc_amount_forcing) {
            $medical_act->setAmcAmountForcing($amc_amount_forcing);
        }

        $invoice = InvoicingMapper::getInvoiceFromResponse(
            $this->client->setMedicalAct($invoice_id, $medical_act)
        );

        if ($invoice->hasMessageWithTypeId(self::ACT_AGGREGATION_MESSAGE_TYPE_ID) && $act instanceof CActeNGAP) {
            $this->handleActAggregation($invoice, $act);
        } else {
            /* We create the data model of the medical act */
            foreach ($invoice->getMedicalActs() as $medical_act) {
                if ($medical_act->getExternalId() === $act->_guid && !$act->countBackRefs('jfse_act_link')) {
                    $medical_act->createDataModel($invoice->getId());

                    /* We nullify the _old because if the method is called in the handler after the creation,
                       a duplicate primary key SQL error will be thrown */
                    $act->_old = null;
                    $this->updateCActePricingFromMedicalAct($medical_act, $act);
                }
            }
        }

        return true;
    }

    /**
     * @param CJfseInvoice $invoice_data_model
     * @param string       $act_id
     *
     * @return bool
     */
    public function deleteMedicalAct(CJfseInvoice $invoice_data_model, string $act_id): bool
    {
        if ($invoice_data_model->isPending()) {
            $this->client->deleteAct($invoice_data_model->jfse_id, $act_id);
        }

        $act = MedicalAct::hydrate(['id' => $act_id]);
        return $act->deleteDataModel();
    }

    /**
     * @param MedicalAct $act
     * @param Invoice    $invoice
     *
     * @return bool
     * @throws \Exception
     */
    public function createCActeFromMedicalAct(MedicalAct $act, Invoice $invoice): bool
    {
        $cacte = MedicalActMapper::getCActeFromMedicalAct($act);
        $data_model = $invoice->loadDataModel();
        $consultation = $data_model->loadConsultation();

        $cacte->executant_id = $consultation->getExecutantId();
        $cacte->object_class = $consultation->_class;
        $cacte->object_id = $consultation->_id;
        $cacte->loadMatchingObject();

        if (!$cacte->_id) {
            $consultation->getActeExecution();
            $cacte->execution = $consultation->_acte_execution;
            $cacte->_preserve_montant    = true;
            $cacte->_ignore_eai_handlers = true;
            $cacte->store();
        } elseif ($cacte->countBackRefs('jfse_act_link')) {
            return true;
        }

        $jfse_act = new CJfseAct();

        /* In case of complements added for free medical cares, the ids are empty */
        if ($act->getId() == '') {
            $jfse_act->jfse_id = CJfseAct::generateId();
        } else {
            $jfse_act->jfse_id = $act->getId();
        }
        $jfse_act->jfse_invoice_id = $data_model->_id;
        $jfse_act->act_class = $cacte->_class;
        $jfse_act->act_id = $cacte->_id;
        $jfse_act->store();

        return isset($jfse_act->_id);
    }

    public function updateCActeFromMedicalAct(MedicalAct $act, Invoice $invoice): void
    {
        $data_model = $invoice->loadDataModel();
        $act_link = CJfseAct::getFromJfseId($act->getId(), $data_model->_id);
        $cacte = $act_link->loadAct();
        $this->updateCActePricingFromMedicalAct($act, $cacte);
    }

    private function updateCActePricingFromMedicalAct(MedicalAct $act, CActe $cacte): void
    {
        $cacte->_ignore_eai_handlers = true;

        if ($act->getIsLpp() && $cacte instanceof CActeLPP) {
            $cacte->montant_final       = $act->getLppBenefit()->getTotalPriceRef();
            $cacte->montant_base        = $act->getLppBenefit()->getUnitPriceRef();
            $cacte->montant_depassement = $act->getPricing()->getExceedingAmount();
            $cacte->montant_total       = $act->getLppBenefit()->getTotalPriceTtc();
        } else {
            $cacte->montant_depassement = $act->getPricing()->getExceedingAmount();
            $cacte->montant_base        = $act->getPricing()->getInvoiceTotal()
                - $act->getPricing()->getExceedingAmount();
        }

        $cacte->store();
    }

    private function handleActAggregation(Invoice $invoice, CActeNGAP $act): void
    {
        $medical_act_external_ids = [];
        foreach ($invoice->getMedicalActs() as $medical_act) {
            $medical_act_external_ids[] = $medical_act->getExternalId();
        }

        /* If the guid of the act is not in the list of act's external id from the invoice,
           it means that the act has been aggregated */
        if (!in_array(str_replace('-', ' ', $act->_guid), $medical_act_external_ids)) {
            foreach ($invoice->getMedicalActs() as $medical_act) {
                if (
                    $medical_act->getActCode() === $act->code
                    && (int)$medical_act->getQuantity() === ((int)$act->quantite + 1)
                    && (float)$medical_act->getCoefficient() === (float)$act->coefficient
                ) {
                    /** @var CActeNGAP $aggregated_act */
                    $aggregated_act = CActeNGAP::loadFromGuid(str_replace(' ', '-', $medical_act->getExternalId()));
                    if ($aggregated_act->_id) {
                        $aggregated_act->_ignore_eai_handlers = true;
                        $aggregated_act->quantite = $medical_act->getQuantity();

                        $aggregated_act->montant_depassement = $medical_act->getPricing()->getExceedingAmount();
                        $aggregated_act->montant_base = $medical_act->getPricing()->getInvoiceTotal()
                            - $medical_act->getPricing()->getExceedingAmount();

                        if (!$aggregated_act->store()) {
                            $act->_ignore_eai_handlers = true;
                            $act->delete();
                            CAppUI::setMsg('CJfseActView-msg-acts_aggregation', UI_MSG_OK);
                        }
                    }
                }
            }
        }
    }

    /**
     * Checks if the given act can be sent to JFSE or not
     *
     * @param CActe $act
     *
     * @return bool
     */
    public static function checkSendAct(CActe $act): bool
    {
        return !$act instanceof CActeNGAP || self::isActNgapCodeAllowed($act->code);
    }

    /**
     * Checks if the given codes must be handled directly by JFSE or not
     *
     * @param string $code
     *
     * @return bool
     */
    public static function isActNgapCodeAllowed(string $code): bool
    {
        return !in_array($code, self::$forbidden_ngap_codes);
    }
}
