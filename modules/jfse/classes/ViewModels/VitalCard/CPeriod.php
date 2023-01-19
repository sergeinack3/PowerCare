<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\VitalCard;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CPeriod extends CJfseViewModel
{
    /** @var int */
    public $group;

    /** @var string */
    public $begin_date;

    /** @var string */
    public $end_date;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props["group"]      = "date";
        $props["begin_date"] = "date";
        $props["end_date"]   = "date";

        return $props;
    }
}
