<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Vital;

use DateTimeImmutable;
use JsonSerializable;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;

/**
 * Fund: Caisse (d'assurance maladie)
 * Managing: Gestion (Centre de gestion)
 */
class VitalCard extends AbstractEntity implements JsonSerializable
{
    /** @var int */
    protected $group;

    /** @var int */
    protected $mode131;

    /** @var int */
    protected $selected_beneficiary_number;

    /** @var string */
    protected $type;

    /** @var string */
    protected $serial_number;

    /** @var DateTimeImmutable */
    protected $expiration_date;

    /** @var string */
    protected $ruf1_administration_data;

    /** @var string */
    protected $ruf2_administration_data;

    /** @var string */
    protected $administration_data;

    /** @var string */
    protected $ruf_bearer_type;

    /** @var Insured */
    protected $insured;

    /** @var string */
    protected $ruf_family_data;

    /** @var string */
    protected $fund_label;

    /** @var string */
    protected $managing_label;

    /** @var AmoFamily */
    protected $amo_family_service;

    /** @var WorkAccident[] */
    protected $work_accident_data;

    /** @var Beneficiary[] */
    protected $beneficiaries;

    /** @var bool */
    protected $apcv;

    /** @var ApCvContext */
    protected $apcv_context;

    /** @var bool */
    protected $cps_absent;

    public function hasBeneficiaries(): bool
    {
        return $this->beneficiaries && count($this->beneficiaries) > 0;
    }

    public function countBeneficiaries(): int
    {
        return is_array($this->beneficiaries) ? count($this->beneficiaries) : 0;
    }

    /**
     * @return Beneficiary[]
     */
    public function getBeneficiaries(): array
    {
        return $this->beneficiaries;
    }

    public function getFirstBeneficiary(): Beneficiary
    {
        return reset($this->beneficiaries);
    }

    public function getInsured(): ?Insured
    {
        return $this->insured;
    }

    public function getExpirationDate(): ?DateTimeImmutable
    {
        return $this->expiration_date;
    }

    public function getRufFamilyData(): string
    {
        return $this->ruf_family_data;
    }

    public function getFundLabel(): string
    {
        return $this->fund_label;
    }

    public function getManagingLabel(): string
    {
        return $this->managing_label;
    }

    public function getCpsAbsent(): ?bool
    {
        return $this->cps_absent;
    }

    public function setCpsAbsent(bool $absent_cps): self
    {
        $this->cps_absent = $absent_cps;
        return $this;
    }

    public function getSelectedBeneficiary(string $birth_date, string $birth_rank, string $quality): ?Beneficiary
    {
        foreach ($this->beneficiaries as $beneficiary) {
            $patient = $beneficiary->getPatient();

            if (
                $patient->getBirthDate() === $birth_date
                && $patient->getBirthRank() === $birth_rank
                && (int)$beneficiary->getQuality() === (int)$quality
            ) {
                return $beneficiary;
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isApcv(): bool
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

    public function selectBeneficiary(int $index): ?Beneficiary
    {
        $beneficiary = null;
        if (isset($this->beneficiaries[$index])) {
            $beneficiary = $this->beneficiaries[$index];
        }

        return $beneficiary;
    }

    public function jsonSerialize(): array
    {
        return [
            'cps_absent'    => $this->cps_absent,
            "nir"           => $this->getFullNir(),
            "beneficiaries" => $this->beneficiaries,
        ];
    }

    public function getFullNir(): string
    {
        return $this->insured->getNir() . $this->insured->getNirKey();
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getSerialNumber(): string
    {
        return $this->serial_number;
    }
}
