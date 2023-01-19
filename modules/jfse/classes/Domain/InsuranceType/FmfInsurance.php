<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\InsuranceType;

/**
 * Class FmfInsurance
 * Free Medical Fees
 *
 * @package Ox\Mediboard\Jfse\ViewModels
 */
final class FmfInsurance extends AbstractInsurance
{
    public const CODE = 4;

    /** @var bool */
    protected $supported_fmf_existence;
    /** @var float */
    protected $supported_fmf_expense;

    /**
     * @return bool
     */
    public function getSupportedFmfExistence(): ?bool
    {
        return $this->supported_fmf_existence;
    }

    /**
     * @return float
     */
    public function getSupportedFmfExpense(): ?float
    {
        return $this->supported_fmf_expense;
    }
}
