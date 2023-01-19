<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Vital;

use JsonSerializable;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class HealthInsurance extends AbstractEntity implements JsonSerializable
{
    use InsuranceTrait;

    /** @var int */
    protected $group;

    /** @var string */
    protected $id;

    /** @var string */
    protected $effective_guarantees;

    /** @var string */
    protected $treatment_indicator;

    /** @var string */
    protected $associated_services_type;

    /** @var string */
    protected $associated_services;

    /** @var Period */
    protected $health_insurance_periods_rights;

    /** @var int */
    protected $contract_type;

    /** @var string */
    protected $pec;

    /** @var bool */
    protected $paper_mode;

    /** @var bool */
    protected $rights_forcing;

    /** @var bool */
    protected $adri_origin;

    /** @var int */
    protected $type;

    /** @var bool */
    protected $is_c2s;

    // Next, specific to the Adri service

    /** @var string */
    protected $code_presentation_support;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getEffectiveGuarantees(): ?string
    {
        return $this->effective_guarantees;
    }

    public function getTreatmentIndicator(): ?string
    {
        return $this->treatment_indicator;
    }

    public function getAssociatedServices(): ?string
    {
        return $this->associated_services;
    }

    public function getHealthInsurancePeriodsRights(): ?Period
    {
        if (is_array($this->health_insurance_periods_rights)) {
            $this->health_insurance_periods_rights = reset($this->health_insurance_periods_rights);
        }

        return $this->health_insurance_periods_rights;
    }

    public function getContractType(): ?int
    {
        return $this->contract_type;
    }

    public function getPec(): ?string
    {
        return $this->pec;
    }

    public function getPaperMode(): ?bool
    {
        return $this->paper_mode;
    }

    public function getRightsForcing(): ?bool
    {
        return $this->rights_forcing;
    }

    /**
     * @return int
     */
    public function getGroup(): ?int
    {
        return $this->group;
    }

    /**
     * @return bool
     */
    public function getAdriOrigin(): ?bool
    {
        return $this->adri_origin;
    }

    /**
     * @return int
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getCodePresentationSupport(): ?string
    {
        return $this->code_presentation_support;
    }

    /**
     * @return string
     */
    public function getAssociatedServicesType(): ?string
    {
        return $this->associated_services_type;
    }

    /**
     * @return string
     */
    public function getAssociatedServicesContract(): ?string
    {
        return $this->associated_services_contract;
    }

    /**
     * @return string
     */
    public function getReferralStsCode(): ?string
    {
        return $this->referral_sts_code;
    }

    /**
     * @return string
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getIsC2S(): bool
    {
        $this->is_c2s = ($this->id === '88888888' || strpos($this->label, 'C2S') !== false);

        return $this->is_c2s;
    }

    public function jsonSerialize(): array
    {
        $period = null;
        if (is_array($this->health_insurance_periods_rights)) {
            $period = reset($this->health_insurance_periods_rights);
        } elseif ($this->health_insurance_periods_rights instanceof Period) {
            $period = $this->health_insurance_periods_rights;
        }

        $data =  [
            'group'                        => $this->group,
            'id'                           => utf8_encode($this->id ?? ''),
            'begin_date'                   => ($period && $period->getBeginDate())
                ? $period->getBeginDate()->format('Y-m-d') : "",
            'end_date'                     => ($period && $period->getEndDate())
                ? $period->getEndDate()->format('Y-m-d') : "",
            'label'                        => utf8_encode($this->label ?? ''),
            'associated_services'          => utf8_encode($this->associated_services ?? ''),
            'associated_services_type'     => utf8_encode($this->associated_services_type ?? ''),
            'associated_services_contract' => utf8_encode($this->associated_services_contract ?? ''),
            'effective_guarantees'         => utf8_encode($this->effective_guarantees ?? ''),
            'treatment_indicator'          => utf8_encode($this->treatment_indicator ?? ''),
            'contract_type'                => $this->contract_type,
            'pec'                          => utf8_encode($this->pec ?? ''),
            'paper_mode'                   => $this->paper_mode,
            'rights_forcing'               => $this->rights_forcing,
            'adri_origin'                  => $this->adri_origin,
            'type'                         => $this->type,
            'code_presentation_support'    => utf8_encode($this->code_presentation_support ?? ''),
            'referral_sts_code'            => utf8_encode($this->referral_sts_code ?? ''),
            'is_c2s'                       => $this->getIsC2S(),
        ];

        return $data;
    }
}
