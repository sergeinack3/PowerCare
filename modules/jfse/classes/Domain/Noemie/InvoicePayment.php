<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Noemie;

use DateTimeImmutable;
use Exception;
use Ox\Mediboard\Jfse\DataModels\CJfseInvoice;
use Ox\Mediboard\Jfse\DataModels\CJfseInvoicePayment;
use Ox\Mediboard\Jfse\DataModels\CJfsePayment;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Exceptions\DataModelException;

/**
 * Represents the part of a payment that concerns a specific invoice
 */
final class InvoicePayment extends AbstractEntity
{
    protected ?DateTimeImmutable $date;

    /** @var bool Indicate that the AMO part of the invoice has been paid */
    protected bool $amo_part_paid = false;

    /** @var bool Indicate that the AMC part of the invoice has been paid */
    protected bool $amc_part_paid = false;

    /** @var float The amount actually paid by the AMO */
    protected float $amount_amo_paid = 0.0;

    /** @var float The amount actually paid by the AMC */
    protected float $amount_amc_paid = 0.0;

    /** @var float The amount the practitioner asked the AMO */
    protected float $amount_amo_asked = 0.0;

    /** @var float The amount the practitioner asked the AMC */
    protected float $amount_amc_asked = 0.0;

    /** @var string|null The label of the AMO organism (usually a CPAM center number) */
    protected ?string $amo_label;

    /** @var string|null The label of the AMC organism (usually the name of the AMC) */
    protected ?string $amc_label;

    /** @var int|null The number of the invoice concerned by the payment */
    protected ?int $invoice_number;

    protected ?string $beneficiary_last_name;

    protected ?string $beneficiary_first_name;

    /** @var string|null The main label of the payment */
    protected ?string $label;

    /** @var string|null An optional secondary label */
    protected ?string $secondary_label;

    /** @var float|null The total amount paid by the organism */
    protected ?float $total_amount;

    /** @var CJfsePayment|null The data model of the payment */
    protected ?CJfsePayment $payment_data_model;

    /** @var CJfseInvoicePayment|null The data model of theinvoice payment */
    protected ?CJfseInvoicePayment $data_model;

    /**
     * @return DateTimeImmutable|null
     */
    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @return bool
     */
    public function isAmoPartPaid(): bool
    {
        return $this->amo_part_paid;
    }

    /**
     * @return bool
     */
    public function isAmcPartPaid(): bool
    {
        return $this->amc_part_paid;
    }

    /**
     * @return float
     */
    public function getAmountAmoPaid(): float
    {
        return $this->amount_amo_paid;
    }

    /**
     * @return float
     */
    public function getAmountAmcPaid(): float
    {
        return $this->amount_amc_paid;
    }

    /**
     * @return float
     */
    public function getAmountAmoAsked(): float
    {
        return $this->amount_amo_asked;
    }

    /**
     * @return float
     */
    public function getAmountAmcAsked(): float
    {
        return $this->amount_amc_asked;
    }

    /**
     * @return string|null
     */
    public function getAmoLabel(): ?string
    {
        return $this->amo_label;
    }

    /**
     * @return string|null
     */
    public function getAmcLabel(): ?string
    {
        return $this->amc_label;
    }

    /**
     * @return int|null
     */
    public function getInvoiceNumber(): ?int
    {
        return $this->invoice_number;
    }

    /**
     * @return string|null
     */
    public function getBeneficiaryLastName(): ?string
    {
        return $this->beneficiary_last_name;
    }

    /**
     * @return string|null
     */
    public function getBeneficiaryFirstName(): ?string
    {
        return $this->beneficiary_first_name;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @return string|null
     */
    public function getSecondaryLabel(): ?string
    {
        return $this->secondary_label;
    }

    /**
     * @return float|null
     */
    public function getTotalAmount(): ?float
    {
        return $this->total_amount;
    }

    /**
     * @return CJfseInvoicePayment|null
     */
    public function getDataModel(): ?CJfseInvoicePayment
    {
        return $this->data_model;
    }

    /**
     * @return bool
     * @throws DataModelException
     */
    protected function storeDataModel(): bool
    {
        try {
            if ($error = $this->data_model->store()) {
                throw DataModelException::persistenceError($error);
            }
        } catch (Exception $e) {
            throw DataModelException::persistenceError($e->getMessage(), $e);
        }

        return true;
    }

    /**
     * @return bool
     * @throws DataModelException
     */
    protected function storePaymentDataModel(): bool
    {
        try {
            if ($error = $this->payment_data_model->store()) {
                throw DataModelException::persistenceError($error);
            }
        } catch (Exception $e) {
            throw DataModelException::persistenceError($e->getMessage(), $e);
        }

        return true;
    }

    /**
     * Load the CJfsePayment, and creates it if it does not exist already
     *
     * @param CJfseInvoice $invoice
     *
     * @return CJfsePayment
     * @throws DataModelException
     */
    protected function getPaymentDataModel(CJfseInvoice $invoice): CJfsePayment
    {
        $this->payment_data_model = new CJfsePayment();
        $this->payment_data_model->jfse_user_id = $invoice->jfse_user_id;

        $jfse_id = '';
        if ($this->getDate()) {
            $jfse_id = $this->getDate()->format('Ymd');
            $this->payment_data_model->date = $this->getDate()->format('Y-m-d');
        }

        if ($this->getAmoLabel()) {
            $jfse_id .= $this->getAmoLabel();
            $this->payment_data_model->organism = $this->getAmoLabel();
        } elseif ($this->getAmcLabel()) {
            $jfse_id .= $this->getAmcLabel();
            $this->payment_data_model->organism = $this->getAmcLabel();
        }

        $this->payment_data_model->jfse_id = $jfse_id . $this->getTotalAmount();

        $this->payment_data_model->amount = $this->getTotalAmount();
        $this->payment_data_model->label = $this->getLabel() . ' ' . $this->getSecondaryLabel();

        try {
            $this->payment_data_model->loadMatchingObjectEsc();
        } catch (Exception $e) {
        }

        if (!$this->payment_data_model->_id) {
            $this->storePaymentDataModel();
        }

        return $this->payment_data_model;
    }

    /**
     * @param CJfseInvoice $invoice
     *
     * @return bool
     * @throws DataModelException
     */
    public function createDataModel(CJfseInvoice $invoice): bool
    {
        $this->getPaymentDataModel($invoice);

        $this->data_model = new CJfseInvoicePayment();
        $this->data_model->invoice_id = $invoice->_id;
        $this->data_model->payment_id = $this->payment_data_model->_id;

        try {
            $this->data_model->loadMatchingObject();
        } catch (Exception $e) {
        }

        $this->data_model->amount_amc = $this->amount_amc_paid;
        $this->data_model->amount_amo = $this->amount_amo_paid;
        $this->data_model->total = $this->amount_amo_paid + $this->amount_amc_paid;

        return $this->storeDataModel();
    }
}
