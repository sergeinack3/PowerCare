<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Noemie;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;

/**
 * Represents an acknowledgement for a set, sent by the CPAM
 * It can either indicate that the invoices of the set have been processed by the CPAM,
 * or that there is an anomaly, either in the set, or in one of the invoices
 */
final class Acknowledgement extends AbstractEntity
{
    /** @var AcknowledgementTypeEnum It is mandatory to hydrate the Acknowledgement with a type */
    protected AcknowledgementTypeEnum $type;

    /** @var string|null An optional acknowledgement id (The id is not given by Jfse for the positive acks) */
    protected ?string $id;

    /** @var string|null The id of the set concerned by the acknowledgement */
    protected ?string $set_id;

    /** @var string|null The number of the set concerned by the acknowledgement */
    protected ?string $set_number;

    /** @var DateTimeImmutable|null The date on which the set was sent */
    protected ?DateTimeImmutable $set_date;

    /** @var DateTimeImmutable|null The date on which the set was sent */
    protected ?DateTimeImmutable $ack_date;

    protected ?string $label;

    protected ?string $rejected_invoice_number;

    /**
     * @return AcknowledgementTypeEnum
     */
    public function getType(): AcknowledgementTypeEnum
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getSetId(): ?string
    {
        return $this->set_id;
    }

    /**
     * @return string|null
     */
    public function getSetNumber(): ?string
    {
        return $this->set_number;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getSetDate(): ?DateTimeImmutable
    {
        return $this->set_date;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getAckDate(): ?DateTimeImmutable
    {
        return $this->ack_date;
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
    public function getRejectedInvoiceNumber(): ?string
    {
        return $this->rejected_invoice_number;
    }
}
