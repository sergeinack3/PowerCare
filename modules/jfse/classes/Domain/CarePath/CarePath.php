<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\CarePath;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;

/**
 * Class CarePath
 */
final class CarePath extends AbstractEntity
{
    public const DECLARATION_YES = 1;
    public const DECLARATION_NO  = 2;

    /** @var int */
    protected $invoice_id;
    /** @var CarePathEnum */
    protected $indicator;
    /** @var DateTimeImmutable */
    protected $install_date;
    /** @var DateTimeImmutable */
    protected $poor_md_zone_install_date;
    /** @var bool|null */
    protected $declaration;
    /** @var CarePathDoctor */
    protected $doctor;
    /** @var CarePathStatusEnum */
    protected $status;

    /**
     * @return int
     */
    public function getInvoiceId(): ?int
    {
        return $this->invoice_id;
    }

    public function getIndicator(): ?CarePathEnum
    {
        return $this->indicator;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getInstallDate(): ?DateTimeImmutable
    {
        return $this->install_date;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getPoorMdZoneInstallDate(): ?DateTimeImmutable
    {
        return $this->poor_md_zone_install_date;
    }

    /**
     * @return bool|null
     */
    public function getDeclaration(): ?bool
    {
        return $this->declaration;
    }

    /**
     * @return CarePathDoctor
     */
    public function getDoctor(): ?CarePathDoctor
    {
        return $this->doctor;
    }

    /**
     * @return CarePathStatusEnum
     */
    public function getStatus(): ?CarePathStatusEnum
    {
        return $this->status;
    }
}
