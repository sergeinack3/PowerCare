<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Vital;

trait InsuranceTrait
{
    /** @var string */
    protected $associated_services_contract;

    /** @var string */
    protected $referral_sts_code;

    /** @var string */
    protected $label;

    /**
     * @return string
     */
    public function getAssociatedServicesContract(): ?string
    {
        return $this->associated_services_contract;
    }

    /**
     * @return int
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
}
