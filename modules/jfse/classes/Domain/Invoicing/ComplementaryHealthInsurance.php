<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Invoicing;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\Convention\Convention;
use Ox\Mediboard\Jfse\Domain\Formula\Formula;
use Ox\Mediboard\Jfse\Domain\Vital\AdditionalHealthInsurance;
use Ox\Mediboard\Jfse\Domain\Vital\AmoServicePeriod;
use Ox\Mediboard\Jfse\Domain\Vital\HealthInsurance;

final class ComplementaryHealthInsurance extends AbstractEntity
{
    /** @var bool */
    protected $third_party_amo;

    /** @var int */
    protected $third_party_amc;

    /** @var bool - victime attentat */
    protected $attack_victim;

    /** @var bool */
    protected $third_party_sncf;

    /** @var AmoServicePeriod */
    protected $amo_service;

    /** @var HealthInsurance */
    protected $health_insurance;

    /** @var AdditionalHealthInsurance */
    protected $additional_health_insurance;

    /** @var Convention */
    protected $convention;

    /** @var Formula */
    protected $formula;

    /** @var Acs */
    protected $acs;

    /** @var ThirdPartyPaymentAssistant */
    protected $assistant;

    /**
     * @return int
     */
    public function getThirdPartyAmc(): int
    {
        return $this->third_party_amc;
    }

    /**
     * @return bool
     */
    public function getThirdPartyAmo(): ?bool
    {
        return $this->third_party_amo;
    }

    /**
     * @return bool
     */
    public function getAttackVictim(): ?bool
    {
        return $this->attack_victim;
    }

    /**
     * @return bool
     */
    public function getThirdPartySncf(): ?bool
    {
        return $this->third_party_sncf;
    }

    public function getAmoService(): ?AmoServicePeriod
    {
        return $this->amo_service;
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
     * @return Convention
     */
    public function getConvention(): ?Convention
    {
        return $this->convention;
    }

    /**
     * @return Formula
     */
    public function getFormula(): ?Formula
    {
        return $this->formula;
    }

    /**
     * @return Acs
     */
    public function getAcs(): ?Acs
    {
        return $this->acs;
    }

    /**
     * @return ThirdPartyPaymentAssistant
     */
    public function getAssistant(): ?ThirdPartyPaymentAssistant
    {
        return $this->assistant;
    }

    public function selectConvention(Convention $convention): self
    {
        $this->convention = $convention;

        return $this;
    }

    public function selectFormula(Formula $formula): self
    {
        $this->formula = $formula;

        return $this;
    }
}
