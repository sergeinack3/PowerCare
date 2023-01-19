<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Invoicing;

use DateTime;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;

final class InsuredParticipationAct extends AbstractEntity
{
    /** @var DateTime */
    protected $date;

    /** @var string */
    protected $code;

    /** @var int */
    protected $index;

    /** @var bool */
    protected $add_insured_participation;

    /** @var bool */
    protected $amo_amount_reduction;

    /** @var float */
    protected $amount;

    /**
     * @return DateTime
     */
    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @return int
     */
    public function getIndex(): ?int
    {
        return $this->index;
    }

    /**
     * @return bool
     */
    public function getAddInsuredParticipation(): ?bool
    {
        return $this->add_insured_participation;
    }

    /**
     * @return bool
     */
    public function getAmoAmountReduction(): ?bool
    {
        return $this->amo_amount_reduction;
    }

    /**
     * @return float
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }
}
