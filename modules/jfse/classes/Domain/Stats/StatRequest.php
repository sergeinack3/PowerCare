<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Stats;

use DateTime;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class StatRequest extends AbstractEntity
{
    public const AMOUNT_DAYS_LAST_TRANSMISSION = 1;
    public const AMOUNT_PENDING_INVOICES       = 2;
    public const TOTAL_REJECTED_INVOICES       = 3;
    public const AMOUNT_INVOICES_BETWEEN_DATES = 4;

    /** @var int */
    protected $choice;

    /** @var DateTime */
    protected $begin;

    /** @var DateTime */
    protected $end;

    public function getChoice(): int
    {
        return $this->choice;
    }

    public function getBegin(): ?DateTime
    {
        return $this->begin;
    }

    public function getEnd(): ?DateTime
    {
        return $this->end;
    }
}
