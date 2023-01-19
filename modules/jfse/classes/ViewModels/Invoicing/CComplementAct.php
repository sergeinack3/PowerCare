<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\Invoicing;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CComplementAct extends CJfseViewModel
{
    /** @var string */
    public $date;

    /** @var string */
    public $code;

    /** @var float */
    public $total;

    /** @var float */
    public $amo_amount;

    /** @var float */
    public $patient_amount;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props['date'] = 'date';
        $props['code'] = 'str';
        $props['total'] = 'currency';
        $props['amo_amount'] = 'currency';
        $props['patient_amount'] = 'currency';

        return $props;
    }
}
