<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Invoicing;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\PrescribingPhysician\Physician;
use Ox\Mediboard\Jfse\Domain\PrescribingPhysician\PhysicianOriginEnum;

/**
 * Class Prescription
 *
 * @package Ox\Mediboard\Jfse\Domain\Invoicing
 */
final class Prescription extends AbstractEntity
{
    /** @var Physician */
    protected $prescriber;

    /** @var string */
    protected $invoice_id;

    /** @var DateTimeImmutable */
    protected $date;

    /** @var PhysicianOriginEnum */
    protected $origin;

    public function getPrescriber(): ?Physician
    {
        return $this->prescriber;
    }

    public function getInvoiceId(): ?string
    {
        return $this->invoice_id;
    }

    public function setInvoiceId(string $invoice_id): self
    {
        $this->invoice_id = $invoice_id;

        return $this;
    }

    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    public function getOrigin(): ?PhysicianOriginEnum
    {
        return $this->origin;
    }
}
