<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\InsuranceType;

use DateTimeImmutable;

/**
 * Class COffSickInsuranceType
 *
 * @package Ox\Mediboard\Jfse\ViewModels
 */
final class WorkAccidentInsurance extends AbstractInsurance
{
    public const CODE = 2;

    /** @var DateTimeImmutable */
    protected $date;
    /** @var bool */
    protected $has_physical_document;
    /** @var string */
    protected $number;
    /** @var string */
    protected $organisation_support;
    /** @var bool */
    protected $is_organisation_identical_amo;
    /** @var int */
    protected $organisation_vital;
    /** @var bool */
    protected $shipowner_support;
    /** @var float */
    protected $amount_apias;

    /**
     * @return DateTimeImmutable
     */
    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getNumber(): ?string
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getOrganisationSupport(): ?string
    {
        return $this->organisation_support;
    }

    /**
     * @return bool
     */
    public function getIsOrganisationIdenticalAmo(): ?bool
    {
        return $this->is_organisation_identical_amo;
    }

    /**
     * @return int
     */
    public function getOrganisationVital(): ?int
    {
        return $this->organisation_vital;
    }

    /**
     * @return float
     */
    public function getAmountApias(): ?float
    {
        return $this->amount_apias;
    }

    /**
     * @return bool
     */
    public function getHasPhysicalDocument(): ?bool
    {
        return $this->has_physical_document;
    }

    /**
     * @return bool
     */
    public function getShipownerSupport(): ?bool
    {
        return $this->shipowner_support;
    }
}
