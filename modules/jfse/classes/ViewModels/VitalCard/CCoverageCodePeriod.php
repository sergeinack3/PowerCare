<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\VitalCard;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CCoverageCodePeriod extends CJfseViewModel
{
    /** @var string */
    public $ald_code;

    /** @var string */
    public $situation_code;

    /** @var string */
    public $standard_exoneration_code;

    /** @var string */
    public $standard_rate;

    /** @var int */
    public $alsace_mozelle_flag;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props["ald_code"]                  = "str";
        $props["situation_code"]            = "str";
        $props["standard_exoneration_code"] = "str";
        $props["standard_rate"]             = "str";
        $props["alsace_mozelle_flag"]       = "num";

        return $props;
    }
}
