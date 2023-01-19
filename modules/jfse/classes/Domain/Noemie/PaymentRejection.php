<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Noemie;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;

/**
 * Represent a rejected payment for an invoice that await a third party payment
 */
final class PaymentRejection extends AbstractEntity
{
    protected ?DateTimeImmutable $date;

    /** @var string|null The type of organism that rejected the payment (AMO or AMC) */
    protected ?string $organism_type;

    protected ?string $code;

    protected ?string $label;

    protected ?string $level;

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
    public function getOrganismType(): ?string
    {
        return $this->organism_type;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
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
    public function getLevel(): ?string
    {
        return $this->level;
    }
}
