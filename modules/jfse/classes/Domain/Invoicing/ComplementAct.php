<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Invoicing;

use DateTime;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;

final class ComplementAct extends AbstractEntity
{
    /** @var DateTime */
    protected $date;

    /** @var string */
    protected $code;

    /** @var float */
    protected $total;

    /** @var float */
    protected $amo_amount;

    /** @var float */
    protected $patient_amount;

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
     * @return float
     */
    public function getTotal(): ?float
    {
        return $this->total;
    }

    /**
     * @return float
     */
    public function getAmoAmount(): ?float
    {
        return $this->amo_amount;
    }

    /**
     * @return float
     */
    public function getPatientAmount(): ?float
    {
        return $this->patient_amount;
    }
}
