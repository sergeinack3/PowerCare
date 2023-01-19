<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\VitalCard;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CDeclaredWorkAccident extends CJfseViewModel
{
    /** @var int */
    public $number;

    /** @var string */
    public $id;

    /** @var string */
    public $code;

    /** @var string */
    public $organism;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props["number"] = "num";
        $props["id"]     = "str";
        $props["code"]   = "str";
        $props["organism"] = "str";

        return $props;
    }
}
