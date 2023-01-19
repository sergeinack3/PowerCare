<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\InsuranceType;

/**
 * Class CInsuranceType
 *
 * @package Ox\Mediboard\Jfse\ViewModels
 */
class CFmfInsurance extends CInsuranceType
{
    /** @var bool */
    public $supported_fmf_existence;
    /** @var float */
    public $supported_fmf_expense;

    /**
     * @return array
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['supported_fmf_existence'] = 'bool notNull';
        $props['supported_fmf_expense']   = 'currency';

        return $props;
    }
}
