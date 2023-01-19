<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Noemie;

use DateTimeImmutable;
use Exception;
use Ox\Mediboard\Jfse\DataModels\CJfsePayment;
use Ox\Mediboard\Jfse\DataModels\CJfseUser;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Exceptions\DataModelException;

/**
 * Represents a payment made by an organism (CPAM or an insurance) to reimburse the practitioner,
 * or to make a punctual payment (for example for handling the care of aged patient for 3 months, or for the ROSP)
 */
final class Payment extends AbstractEntity
{
    protected ?string $id;

    /** @var DateTimeImmutable|null The date on which the payment was made by the organism */
    protected ?DateTimeImmutable $date;

    /** @var string|null The main label of the payment */
    protected ?string $label;

    /** @var string|null An optional secondary label */
    protected ?string $secondary_label;

    /** @var string|null The name or number of the organism that made the payment */
    protected ?string $organism;

    /** @var float|null The amount paid by the organism */
    protected ?float $amount;

    /** @var array The detailed list of invoices concerned by the payment */
    protected array $invoice_payments = [];

    /** @var CJfsePayment|null The data model of the payment */
    protected ?CJfsePayment $data_model;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
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
     * @return string|null
     */
    public function getOrganism(): ?string
    {
        return $this->organism;
    }

    /**
     * @return float|null
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * @param array $payments
     *
     * @return $this
     */
    public function setInvoicePayments(array $payments): self
    {
        $this->invoice_payments = $payments;

        return $this;
    }

    /**
     * @return InvoicePayment[]
     */
    public function getInvoicePayments(): array
    {
        return $this->invoice_payments;
    }

    /**
     * @return CJfsePayment|null
     */
    public function getDataModel(): ?CJfsePayment
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
     * @param CJfseUser $user
     *
     * @return bool
     */
    public function createDataModel(CJfseUser $user): bool
    {
        $this->data_model = new CJfsePayment();
        $this->data_model->jfse_id = $this->id;
        $this->data_model->jfse_user_id = $user->_id;

        try {
            $this->data_model->loadMatchingObject();
        } catch (Exception $e) {
        }

        if ($this->date) {
            $this->data_model->date = $this->date->format('Y-m-d');
        }

        $this->data_model->label = $this->label;
        $this->data_model->organism = $this->organism;
        $this->data_model->amount = $this->amount;

        return $this->storeDataModel();
    }
}
