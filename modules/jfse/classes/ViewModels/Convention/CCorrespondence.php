<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\Convention;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CCorrespondence extends CJfseViewModel
{
    /** @var int */
    public $correspondence_id;
    /** @var string */
    public $health_insurance_number;
    /** @var string */
    public $regime_code;
    /** @var string */
    public $amc_number;
    /** @var string */
    public $amc_label;
    /** @var int */
    public $group_id;

    public function getProps(): array
    {
        $props                            = parent::getProps();
        $props["correspondence_id"]       = 'num';
        $props["health_insurance_number"] = 'str';
        $props["regime_code"]             = 'str';
        $props["amc_number"]              = 'str';
        $props["amc_label"]               = 'str';
        $props["group_id"]                = 'num';

        return $props;
    }
}
