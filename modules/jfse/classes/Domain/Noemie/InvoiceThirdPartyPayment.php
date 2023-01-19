<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Noemie;

use DateTimeImmutable;
use Exception;
use Ox\Mediboard\Jfse\DataModels\CJfseInvoice;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\Invoicing\InvoiceStatusEnum;
use Ox\Mediboard\Jfse\Exceptions\Invoice\InvoiceException;

/**
 * Represents an Invoice that was made with a third party payment.
 * It contains the expected amount, and the paid amount by the different organisms
 */
class InvoiceThirdPartyPayment extends AbstractEntity
{
    protected ?int $invoice_number;

    protected ?string $invoice_id;

    /** @var int|null The type of invoice (the jfse doc doesn't list the different possible types) */
    protected ?int $type;

    protected ?DateTimeImmutable $date;

    protected ?string $beneficiary_last_name;

    protected ?string $beneficiary_first_name;

    protected ?string $beneficiary_nir;

    protected ?string $practitioner_last_name;

    protected ?string $practitioner_first_name;

    protected ?string $invoicing_number;

    protected float $amount = 0.0;

    protected ?string $forced_state;

    protected ?string $amo_organism;

    protected ?string $amc_organism;

    protected bool $amo_third_party_payment = false;

    protected bool $amc_third_party_payment = false;

    protected float $expected_amount = 0.0;

    protected float $beneficiary_amount = 0.0;

    protected float $expected_amo_amount = 0.0;

    protected float $expected_amc_amount = 0.0;

    protected ?InvoiceThirdPartyPaymentStatusEnum $status;

    protected ?InvoiceThirdPartyPaymentStatusEnum $status_amo;

    protected ?InvoiceThirdPartyPaymentStatusEnum $status_amc;

    protected float $paid_amount = 0.0;

    protected float $unpaid_amount = 0.0;

    /** @var InvoicePayment[] */
    protected array $payments;

    /** @var PaymentRejection[] */
    protected array $rejections;

    protected ?CJfseInvoice $data_model;

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
    public function getInvoiceId(): ?string
    {
        return $this->invoice_id;
    }

    /**
     * @return int|null
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
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
    public function getBeneficiaryNir(): ?string
    {
        return $this->beneficiary_nir;
    }

    /**
     * @return string|null
     */
    public function getPractitionerLastName(): ?string
    {
        return $this->practitioner_last_name;
    }

    /**
     * @return string|null
     */
    public function getPractitionerFirstName(): ?string
    {
        return $this->practitioner_first_name;
    }

    /**
     * @return string|null
     */
    public function getInvoicingNumber(): ?string
    {
        return $this->invoicing_number;
    }

    /**
     * @return float|null
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * @return string|null
     */
    public function getForcedState(): ?string
    {
        return $this->forced_state;
    }

    /**
     * @return string|null
     */
    public function getAmoOrganism(): ?string
    {
        return $this->amo_organism;
    }

    /**
     * @return string|null
     */
    public function getAmcOrganism(): ?string
    {
        return $this->amc_organism;
    }

    /**
     * @return bool
     */
    public function isAmoThirdPartyPayment(): bool
    {
        return $this->amo_third_party_payment;
    }

    /**
     * @return bool
     */
    public function isAmcThirdPartyPayment(): bool
    {
        return $this->amc_third_party_payment;
    }

    /**
     * @return float
     */
    public function getExpectedAmount(): float
    {
        return $this->expected_amount;
    }

    /**
     * @return float
     */
    public function getBeneficiaryAmount(): float
    {
        return $this->beneficiary_amount;
    }

    /**
     * @return float
     */
    public function getExpectedAmoAmount(): float
    {
        return $this->expected_amo_amount;
    }

    /**
     * @return float
     */
    public function getExpectedAmcAmount(): float
    {
        return $this->expected_amc_amount;
    }

    /**
     * @return InvoiceThirdPartyPaymentStatusEnum|null
     */
    public function getStatus(): ?InvoiceThirdPartyPaymentStatusEnum
    {
        return $this->status;
    }

    /**
     * @return InvoiceThirdPartyPaymentStatusEnum|null
     */
    public function getStatusAmo(): ?InvoiceThirdPartyPaymentStatusEnum
    {
        return $this->status_amo;
    }

    /**
     * @return InvoiceThirdPartyPaymentStatusEnum|null
     */
    public function getStatusAmc(): ?InvoiceThirdPartyPaymentStatusEnum
    {
        return $this->status_amc;
    }

    /**
     * @return float
     */
    public function getPaidAmount(): float
    {
        return $this->paid_amount;
    }

    /**
     * @return float
     */
    public function getUnpaidAmount(): float
    {
        return $this->unpaid_amount;
    }

    /**
     * @return InvoicePayment[]
     */
    public function getPayments(): array
    {
        return $this->payments;
    }

    /**
     * @return PaymentRejection[]
     */
    public function getRejections(): array
    {
        return $this->rejections;
    }

    /**
     * @return bool
     */
    public function loadDataModel(): bool
    {
        try {
            $this->data_model = CJfseInvoice::getFromJfseId($this->invoice_id);

            if (!$this->data_model->_id) {
                $this->data_model = null;
            }
        } catch (Exception $e) {
            $this->data_model = null;
        }

        return !is_null($this->data_model);
    }

    /**
     * @return void
     */
    public function setDataModelStatus(): void
    {
        switch ($this->status) {
            case InvoiceThirdPartyPaymentStatusEnum::PAID():
                $this->data_model->status = InvoiceStatusEnum::PAID()->getValue();
                break;
            case InvoiceThirdPartyPaymentStatusEnum::REJECTED():
                $this->data_model->status = InvoiceStatusEnum::PAYMENT_REJECTED()->getValue();
                break;
            default:
        }
    }

    /**
     * Stores the given data object.
     *
     * Throws a UserExceptions in case of error
     *
     * @return bool
     *
     * @throws InvoiceException
     */
    protected function storeDataModel(): bool
    {
        try {
            if ($error = $this->data_model->store()) {
                throw InvoiceException::persistenceError($error);
            }
        } catch (Exception $e) {
            throw InvoiceException::persistenceError($e->getMessage(), $e);
        }

        return true;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function updateDataModel(): bool
    {
        $this->setDataModelStatus();
        $this->data_model->third_party_payment = '1';
        return $this->storeDataModel();
    }

    /**
     * @return void
     */
    public function setDataModelRejectReason(): void
    {
        $label = '';
        foreach ($this->getRejections() as $rejection) {
            $label .= $rejection->getOrganismType() . ': ' . $rejection->getLabel();
        }

        $this->data_model->reject_reason = $label;

        $this->storeDataModel();
    }

    /**
     * @return CJfseInvoice|null
     */
    public function getDataModel(): ?CJfseInvoice
    {
        return $this->data_model;
    }
}
