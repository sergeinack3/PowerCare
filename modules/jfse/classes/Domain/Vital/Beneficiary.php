<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Vital;

use DateTimeImmutable;
use JsonSerializable;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\Adri\DeclaredWorkAccident;

class Beneficiary extends AbstractEntity implements JsonSerializable
{
    public const CHILD_QUALITY   = '6';
    public const INSURED_QUALITY = '0';

    /** @var int */
    protected $id;

    /** @var int */
    protected $group;

    /** @var int */
    protected $number;

    /** @var Patient */
    protected $patient;

    /** @var string */
    protected $certified_nir;

    /** @var string */
    protected $certified_nir_key;

    /** @var DateTimeImmutable|null */
    protected $nir_certification_date;

    /** @var string */
    protected $quality;

    /** @var string */
    protected $quality_label;

    /** @var AmoServicePeriod */
    protected $amo_service;

    /** @var string */
    protected $insc_number;

    /** @var string */
    protected $insc_key;

    /** @var string */
    protected $insc_error;

    /** @var string|null */
    protected $acs;

    /** @var string */
    protected $acs_label;

    /** @var Period[] */
    protected $amo_period_rights;

    /** @var CoverageCodePeriod[] */
    protected $coverage_code_periods;

    /** @var HealthInsurance */
    protected $health_insurance;

    /** @var AdditionalHealthInsurance */
    protected $additional_health_insurance;

    /** @var string */
    protected $integrator_id;

    // Next, specific to Adri
    /** @var string */
    protected $prescribing_physician_top;

    /** @var DeclaredWorkAccident */
    protected $declared_work_accidents;

    /** @var string Only used for sending the NIR at the initialization of the invoice */
    protected $nir;

    /** @var Insured Only used for initializing the invoice */
    protected $insured;

    /** @var bool */
    protected $apcv = false;

    /** @var ApCvContext */
    protected $apcv_context;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function getCertifiedNir(): ?string
    {
        return $this->certified_nir;
    }

    public function getCertifiedNirKey(): ?string
    {
        return $this->certified_nir_key;
    }

    public function getFullCertifiedNir(): ?string
    {
        return $this->certified_nir ? $this->certified_nir . $this->certified_nir_key : null;
    }

    public function getInscNumber(): ?string
    {
        return $this->insc_number;
    }

    public function getInscKey(): ?string
    {
        return $this->insc_key;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function getQuality(): ?string
    {
        return $this->quality;
    }

    public function getQualityLabel(): ?string
    {
        return $this->quality_label;
    }

    public function getNirCertificationDate(): ?DateTimeImmutable
    {
        return $this->nir_certification_date;
    }

    public function getNir(): ?string
    {
        return $this->nir;
    }

    public function getInsured(): ?Insured
    {
        return $this->insured;
    }

    public function setInsured(Insured $insured): self
    {
        $this->insured = $insured;
        return $this;
    }

    /**
     * @return int
     */
    public function getGroup(): ?int
    {
        return $this->group;
    }

    /**
     * @return string
     */
    public function getInscError(): ?string
    {
        return $this->insc_error;
    }

    /**
     * @return string
     */
    public function getAcsLabel(): ?string
    {
        return $this->acs_label;
    }

    /**
     * @return Period[]
     */
    public function getAmoPeriodRights(): array
    {
        return $this->amo_period_rights;
    }

    /**
     * @return CoverageCodePeriod[]
     */
    public function getCoverageCodePeriods(): array
    {
        return $this->coverage_code_periods;
    }

    /**
     * @return HealthInsurance
     */
    public function getHealthInsurance(): ?HealthInsurance
    {
        return $this->health_insurance;
    }

    /**
     * @return AdditionalHealthInsurance
     */
    public function getAdditionalHealthInsurance(): ?AdditionalHealthInsurance
    {
        return $this->additional_health_insurance;
    }

    /**
     * @return string
     */
    public function getPrescribingPhysicianTop(): ?string
    {
        return $this->prescribing_physician_top;
    }

    /**
     * @return DeclaredWorkAccident[]
     */
    public function getDeclaredWorkAccidents(): ?array
    {
        return $this->declared_work_accidents;
    }

    public function hasOpenAmoRights(): ?bool
    {
        $last_rights = $this->getLastAmoRight();

        if (
            !$last_rights
            || $last_rights->getBeginDate() < new DateTimeImmutable()
            || $last_rights->getEndDate() > new DateTimeImmutable()
        ) {
            // If begin date is higher than today's date or end date is lower than today's date, return false
            return false;
        }

        return true;
    }

    /**
     * Rights are sorted by dates and the most recent Period is returned
     *
     * @return Period
     */
    public function getLastAmoRight(): ?Period
    {
        $amo_rights = $this->amo_period_rights ?? [];

        usort(
            $amo_rights,
            function (Period $a1, Period $a2): int {
                return $a1->getBeginDate() <=> $a2->getBeginDate();
            }
        );

        return (reset($amo_rights)) ?: null;
    }

    public function getCurrentCoverage(): ?CoverageCodePeriod
    {
        $now = new DateTimeImmutable();
        if (is_array($this->coverage_code_periods) && count($this->coverage_code_periods) === 1) {
            return reset($this->coverage_code_periods);
        } elseif (is_array($this->coverage_code_periods)) {
            foreach ($this->coverage_code_periods as $code_period) {
                if (($code_period->getBeginDate() <= $now) && $code_period->getEndDate() >= $now) {
                    return $code_period;
                }
            }
        }

        return null;
    }

    public function getAmoService(): ?AmoServicePeriod
    {
        return $this->amo_service;
    }

    public function getAcsType(): string
    {
        switch ($this->getAcs()) {
            case 2:
                return 'a';
            case 3:
                return 'b';
            case 4:
                return 'c';
            default:
                return '';
        }
    }

    public function getAcs(): ?string
    {
        return $this->acs;
    }

    /**
     * @return string
     */
    public function getIntegratorId(): ?string
    {
        return $this->integrator_id;
    }

    /**
     * @return bool
     */
    public function getApcv(): bool
    {
        return $this->apcv;
    }

    /**
     * @return ApCvContext
     */
    public function getApcvContext(): ?ApCvContext
    {
        return $this->apcv_context;
    }

    public function setApCvContext(ApCvContext $context): self
    {
        $this->apcv_context = $context;
        $this->apcv = true;

        return $this;
    }

    public function jsonSerialize(): array
    {
        $data = [
            'id'                        => $this->id,
            'number'                    => $this->number,
            'certified_nir'             => $this->certified_nir,
            'certified_nir_key'         => $this->certified_nir_key,
            'insc_number'               => $this->insc_number,
            'insc_key'                  => $this->insc_key,
            'quality'                   => utf8_encode($this->quality),
            'quality_label'             => utf8_encode($this->quality_label),
            'nir'                       => $this->nir,
            'insured'                   => $this->insured,
            'group'                     => utf8_encode($this->group),
            'acs_label'                 => utf8_encode($this->acs_label ?? ''),
            'amo_period_rights'         => $this->getLastAmoRight(),
            'coverage_code_periods'     => $this->coverage_code_periods,
            'prescribing_physician_top' => $this->prescribing_physician_top,
            'amo_service'               => $this->amo_service,
            'acs'                       => $this->acs ? '1' : '0',
            'acs_type'                  => $this->acs ? $this->getAcsType() : null,
            'integrator_id'             => $this->integrator_id,
            "patient"                   => $this->patient,
        ];

        if ($this->health_insurance) {
            $data['health_insurance'] = $this->health_insurance;
        } elseif ($this->additional_health_insurance) {
            $data['additional_health_insurance'] = $this->additional_health_insurance;
        }

        if ($this->declared_work_accidents) {
            $data['declared_work_accidents'] = $this->declared_work_accidents;
        }

        return $data;
    }
}
