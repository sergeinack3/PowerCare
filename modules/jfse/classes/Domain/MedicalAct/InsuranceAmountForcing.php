<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\MedicalAct;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class InsuranceAmountForcing extends AbstractEntity
{
    /** @var string */
    protected $type;

    /** @var int */
    protected $choice;

    /** @var float */
    protected $computed_insurance_part;

    /** @var float */
    protected $modified_insurance_part;

    /**
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getChoice(): int
    {
        return $this->choice;
    }

    /**
     * @return float
     */
    public function getComputedInsurancePart(): float
    {
        return $this->computed_insurance_part;
    }

    /**
     * @return float
     */
    public function getModifiedInsurancePart(): float
    {
        return $this->modified_insurance_part;
    }

    public function getInsurancePart(): float
    {
        return $this->choice == 0 ? $this->computed_insurance_part : $this->modified_insurance_part;
    }
}
