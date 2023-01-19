<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\Invoicing;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CInsuredParticipationAct extends CJfseViewModel
{
    /** @var string */
    public $date;

    /** @var string */
    public $code;

    /** @var int */
    public $index;

    /** @var bool */
    public $add_insured_participation;

    /** @var bool */
    public $amo_amount_reduction;

    /** @var float */
    public $amount;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props['date'] = 'date';
        $props['code'] = 'str';
        $props['index'] = 'num';
        $props['add_insured_participation'] = 'bool';
        $props['amo_amount_reduction'] = 'bool';
        $props['amount'] = 'currency';

        return $props;
    }
}
