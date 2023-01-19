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
use Ox\Mediboard\Jfse\Exceptions\DataModelException;

/**
 * Represents an Invoice in a Set (Lot)
 */
final class InvoiceSet extends AbstractEntity
{
    protected ?string $id;

    protected ?int $number;

    protected ?DateTimeImmutable $date;

    protected ?CJfseInvoice $data_model;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getNumber(): ?int
    {
        return $this->number;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * Loads the data object linked to the Jfse user's id
     *
     * @return CJfseInvoice
     *
     * @throws DataModelException
     */
    public function loadDataModel(): CJfseInvoice
    {
        if (!$this->data_model || !$this->data_model->_id) {
            $this->data_model          = new CJfseInvoice();
            $this->data_model->jfse_id = $this->id;

            try {
                $this->data_model->loadMatchingObjectEsc();
            } catch (Exception $e) {
                throw DataModelException::persistenceError($e->getMessage(), $e);
            }
        }

        return $this->data_model;
    }

    /**
     * Stores the given data object.
     *
     * Throws a UserExceptions in case of error
     *
     * @return bool
     *
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
     * @param InvoiceStatusEnum $status
     *
     * @return bool
     */
    public function updateDataModelStatus(InvoiceStatusEnum $status, ?string $reject_reason): bool
    {
        $this->loadDataModel();

        $result = true;
        if (
            $this->data_model->status !== $status->getValue()
            && $this->data_model->status !== InvoiceStatusEnum::PAID()->getValue()
            && $this->data_model->status !== InvoiceStatusEnum::PAYMENT_REJECTED()->getValue()
        ) {
            $this->data_model->status = $status->getValue();

            if ($this->data_model->status === InvoiceStatusEnum::REJECTED()->getValue() && $reject_reason) {
                $this->data_model->reject_reason = $reject_reason;
            }

            $result = $this->storeDataModel();
        }

        return $result;
    }
}
