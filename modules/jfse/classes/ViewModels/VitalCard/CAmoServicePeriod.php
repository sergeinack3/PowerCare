<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\VitalCard;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CAmoServicePeriod extends CJfseViewModel
{
    /** @var string */
    public $code;

    /** @var string */
    public $label;

    /** @var string */
    public $ruf_data;

    /** @var string */
    public $begin_date;

    /** @var string */
    public $end_date;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props["code"]       = "str";
        $props["label"]      = "str";
        $props["ruf_data"]   = "str";
        $props['begin_date'] = 'date';
        $props['end_date']   = 'date';

        return $props;
    }
}
