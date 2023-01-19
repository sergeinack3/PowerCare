<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Convention;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

final class Convention extends AbstractEntity
{
    /** @var int */
    protected $convention_id;
    /** @var string */
    protected $signer_organization_number;
    /** @var string */
    protected $signer;
    /** @var string */
    protected $convention_type;
    /** @var string */
    protected $secondary_criteria;
    /** @var string */
    protected $agreement_type;
    /** @var string */
    protected $signer_organization_label;
    /** @var string */
    protected $amc_number;
    /** @var string */
    protected $amc_label;
    /** @var string */
    protected $statutory_operator;
    /** @var string */
    protected $routing_code;
    /** @var string */
    protected $host_id;
    /** @var string */
    protected $domain_name;
    /** @var string */
    protected $sts_referral_code;
    /** @var int */
    protected $group_convention_flag;
    /** @var int */
    protected $certificate_use_flag;
    /** @var int */
    protected $sts_disabled_flag;
    /** @var int */
    protected $cancel_management;
    /** @var int */
    protected $rectification_management;
    /** @var int */
    protected $convention_application;
    /** @var int */
    protected $systematic_application;
    /** @var string */
    protected $convention_application_date;
    /** @var int */
    protected $service;
    /** @var int */
    protected $teleservice;
    /** @var int */
    protected $group_id;
    /** @var int */
    protected $jfse_id;


    public function getConventionId(): ?int
    {
        return $this->convention_id;
    }

    public function getSignerOrganizationNumber(): ?string
    {
        return $this->signer_organization_number;
    }

    public function getConventionType(): ?string
    {
        return $this->convention_type;
    }

    public function getSecondaryCriteria(): ?string
    {
        return $this->secondary_criteria;
    }

    public function getAgreementType(): ?string
    {
        return $this->agreement_type;
    }

    public function getSignerOrganizationLabel(): ?string
    {
        return $this->signer_organization_label;
    }

    public function getAmcNumber(): ?string
    {
        return $this->amc_number;
    }

    public function getAmcLabel(): ?string
    {
        return $this->amc_label;
    }

    public function getStatutoryOperator(): ?string
    {
        return $this->statutory_operator;
    }

    public function getRoutingCode(): ?string
    {
        return $this->routing_code;
    }

    public function getHostId(): ?string
    {
        return $this->host_id;
    }

    public function getDomainName(): ?string
    {
        return $this->domain_name;
    }

    public function getStsReferralCode(): ?string
    {
        return $this->sts_referral_code;
    }

    public function getGroupConventionFlag(): ?int
    {
        return $this->group_convention_flag;
    }

    public function getCertificateUseFlag(): ?int
    {
        return $this->certificate_use_flag;
    }

    public function getStsDisabledFlag(): ?int
    {
        return $this->sts_disabled_flag;
    }

    public function getCancelManagement(): ?int
    {
        return $this->cancel_management;
    }

    public function getRectificationManagement(): ?int
    {
        return $this->rectification_management;
    }

    public function getConventionApplication(): ?int
    {
        return $this->convention_application;
    }

    public function getSystematicApplication(): ?int
    {
        return $this->systematic_application;
    }

    public function getConventionApplicationDate(): ?string
    {
        return $this->convention_application_date;
    }

    public function getGroupId(): ?string
    {
        return $this->group_id;
    }

    public function getJfseId(): ?string
    {
        return $this->jfse_id;
    }

    public function getSigner(): ?string
    {
        return $this->signer;
    }

    public function getService(): ?int
    {
        return $this->service;
    }

    public function getTeleservice(): ?int
    {
        return $this->teleservice;
    }
}
