<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Stats;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class StatResult extends AbstractEntity
{
    /** @var int */
    protected $amount_days_last_transmission;

    /** @var DateTimeImmutable */
    protected $date_last_transmission;

    /** @var int */
    protected $amount_invoices_pending_transmission;

    /** @var float */
    protected $total_invoices_rejected;

    /** @var int */
    protected $amount_invoices;

    /** @var float */
    protected $total_invoices;

    public function getAmountDaysLastTransmission(): ?int
    {
        return $this->amount_days_last_transmission;
    }

    public function getDateLastTransmission(): ?DateTimeImmutable
    {
        return $this->date_last_transmission;
    }

    public function getAmountInvoicesPendingTransmission(): ?int
    {
        return $this->amount_invoices_pending_transmission;
    }

    public function getTotalInvoicesRejected(): ?float
    {
        return $this->total_invoices_rejected;
    }

    public function getAmountInvoices(): ?int
    {
        return $this->amount_invoices;
    }

    public function getTotalInvoices(): ?float
    {
        return $this->total_invoices;
    }
}
