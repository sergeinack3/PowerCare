<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Invoicing;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

final class Complement extends AbstractEntity
{
    /** @var string */
    protected $type;

    /** @var bool */
    protected $amo_third_party_payment;

    /** @var float */
    protected $pec_amount;

    /** @var float */
    protected $total;

    /** @var float */
    protected $amo_total;

    /** @var float */
    protected $patient_total;

    /** @var float */
    protected $amount_owed_amo;

    /** @var ComplementAct[] */
    protected $acts;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isAmoThirdPartyPayment(): bool
    {
        return $this->amo_third_party_payment;
    }

    /**
     * @return float
     */
    public function getPecAmount(): ?float
    {
        return $this->pec_amount;
    }

    /**
     * @return float
     */
    public function getTotal(): ?float
    {
        return $this->total;
    }

    /**
     * @return float
     */
    public function getAmoTotal(): ?float
    {
        return $this->amo_total;
    }

    /**
     * @return float
     */
    public function getPatientTotal(): ?float
    {
        return $this->patient_total;
    }

    /**
     * @return float
     */
    public function getAmountOwedAmo(): ?float
    {
        return $this->amount_owed_amo;
    }

    /**
     * @return ComplementAct[]
     */
    public function getActs(): ?array
    {
        return $this->acts;
    }
}
