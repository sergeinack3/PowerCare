<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\InsuranceType;

/**
 * Class CSicknessInsuranceType
 *
 * @package Ox\Mediboard\Jfse\ViewModels
 */
final class MedicalInsurance extends AbstractInsurance
{
    public const CODE = 0;

    /** @var int */
    protected $code_exoneration_disease;

    /**
     * @return int
     */
    public function getCodeExonerationDisease(): ?int
    {
        return $this->code_exoneration_disease;
    }
}
