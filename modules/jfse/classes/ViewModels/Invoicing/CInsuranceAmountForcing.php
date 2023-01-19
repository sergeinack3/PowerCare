<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\Invoicing;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CInsuranceAmountForcing extends CJfseViewModel
{
    /** @var int */
    public $choice;

    /** @var float */
    public $computed_insurance_part;

    /** @var float */
    public $modified_insurance_part;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props['choice'] = 'num';
        $props['computed_insurance_part'] = 'currency';
        $props['modified_insurance_part'] = 'currency';

        return $props;
    }
}
