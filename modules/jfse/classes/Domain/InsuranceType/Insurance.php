<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\InsuranceType;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\InsuranceType\FmfInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\MaternityInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\MedicalInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\WorkAccidentInsurance;

/**
 * Represents the insurance data, as send by Jfse
 * @package Ox\Mediboard\Jfse\Domain\InsuranceType
 */
final class Insurance extends AbstractEntity
{
    /** @var int */
    protected $selected_insurance_type;

    /** @var MedicalInsurance */
    protected $medical_insurance;

    /** @var MaternityInsurance  */
    protected $maternity_insurance;

    /** @var WorkAccidentInsurance */
    protected $work_accident_insurance;

    /** @var FmfInsurance */
    protected $fmf_insurance;

    /**
     * @return int
     */
    public function getSelectedInsuranceType(): int
    {
        return $this->selected_insurance_type;
    }

    /**
     * @return WorkAccidentInsurance
     */
    public function getWorkAccidentInsurance(): WorkAccidentInsurance
    {
        return $this->work_accident_insurance;
    }

    /**
     * @return MaternityInsurance
     */
    public function getMaternityInsurance(): MaternityInsurance
    {
        return $this->maternity_insurance;
    }

    /**
     * @return FmfInsurance
     */
    public function getFmfInsurance(): FmfInsurance
    {
        return $this->fmf_insurance;
    }

    /**
     * @return MedicalInsurance
     */
    public function getMedicalInsurance(): MedicalInsurance
    {
        return $this->medical_insurance;
    }
}
