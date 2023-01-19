<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Invoicing;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

/**
 * Contains information on which parts of the Invoice User Interface must be displayed to the user
 *
 * @package Ox\Mediboard\Jfse\Domain\Invoicing
 */
final class InvoiceUserInterface extends AbstractEntity
{
    /** @var bool */
    protected $proof_amo;

    /** @var bool */
    protected $alsace_moselle;

    /** @var bool */
    protected $beneficiary;

    /** @var bool */
    protected $prescriber;

    /** @var bool */
    protected $ame;

    /** @var bool */
    protected $maternity_exoneration;

    /** @var bool */
    protected $sncf;

    /** @var bool */
    protected $amc_third_party_payment;

    /** @var bool */
    protected $pharmacy;

    /** @var bool */
    protected $care_path;

    /** @var bool */
    protected $ccam_acts;

    /** @var bool */
    protected $medical_acts;

    /** @var bool */
    protected $cnda_mode;

    /** @var bool */
    protected $acts_lock;

    /** @var bool */
    protected $amendment_27_consultation_help;

    /** @var bool */
    protected $amendment_27_referring_physician;

    /** @var bool */
    protected $amendment_27_enforceable_tariff;

    /** @var bool */
    protected $recompute_clc;

    /** @var bool */
    protected $adri_activation;

    /** @var bool */
    protected $imti_activation;

    /** @var bool */
    protected $amc_directory_activation;

    /** @var bool */
    protected $display_pav;

    /** @var bool */
    protected $anonymize;

    /** @var bool */
    protected $display_treatment_type = false;

    /**
     * @return bool
     */
    public function getProofAmo(): bool
    {
        return $this->proof_amo;
    }

    /**
     * @return bool
     */
    public function getAlsaceMoselle(): bool
    {
        return $this->alsace_moselle;
    }

    /**
     * @return bool
     */
    public function getBeneficiary(): bool
    {
        return $this->beneficiary;
    }

    /**
     * @return bool
     */
    public function getPrescriber(): bool
    {
        return $this->prescriber;
    }

    /**
     * @return bool
     */
    public function getAme(): bool
    {
        return $this->ame;
    }

    /**
     * @return bool
     */
    public function getMaternityExoneration(): bool
    {
        return $this->maternity_exoneration;
    }

    /**
     * @return bool
     */
    public function getSncf(): bool
    {
        return $this->sncf;
    }

    /**
     * @return bool
     */
    public function getAmcThirdPartyPayment(): bool
    {
        return $this->amc_third_party_payment;
    }

    /**
     * @return bool
     */
    public function getPharmacy(): bool
    {
        return $this->pharmacy;
    }

    /**
     * @return bool
     */
    public function getCarePath(): bool
    {
        return $this->care_path;
    }

    /**
     * @return bool
     */
    public function getCcamActs(): bool
    {
        return $this->ccam_acts;
    }

    /**
     * @return bool
     */
    public function getMedicalActs(): bool
    {
        return $this->medical_acts;
    }

    /**
     * @return bool
     */
    public function getCndaMode(): bool
    {
        return $this->cnda_mode;
    }

    /**
     * @return bool
     */
    public function getActsLock(): bool
    {
        return $this->acts_lock;
    }

    /**
     * @return bool
     */
    public function getAmendment27ConsultationHelp(): bool
    {
        return $this->amendment_27_consultation_help;
    }

    /**
     * @return bool
     */
    public function getAmendment27ReferringPhysician(): bool
    {
        return $this->amendment_27_referring_physician;
    }

    /**
     * @return bool
     */
    public function getAmendment27EnforceableTariff(): bool
    {
        return $this->amendment_27_enforceable_tariff;
    }

    /**
     * @return bool
     */
    public function getRecomputeClc(): bool
    {
        return $this->recompute_clc;
    }

    /**
     * @return bool
     */
    public function getAdriActivation(): bool
    {
        return $this->adri_activation;
    }

    /**
     * @return bool
     */
    public function getImtiActivation(): bool
    {
        return $this->imti_activation;
    }

    /**
     * @return bool
     */
    public function getAmcDirectoryActivation(): bool
    {
        return $this->amc_directory_activation;
    }

    /**
     * @return bool
     */
    public function getDisplayPav(): bool
    {
        return $this->display_pav;
    }

    /**
     * @return bool
     */
    public function getAnonymize(): ?bool
    {
        return $this->anonymize;
    }

    /**
     * @param bool $anonymize
     *
     * @return self
     */
    public function setAnonymize(bool $anonymize): self
    {
        $this->anonymize = $anonymize;
        return $this;
    }

    /**
     * @return bool
     */
    public function getDisplayTreatmentType(): ?bool
    {
        return $this->display_treatment_type;
    }

    /**
     * @param bool $display
     *
     * @return self
     */
    public function setDisplayTreatmentType(bool $display): self
    {
        $this->display_treatment_type = $display;
        return $this;
    }
}
