<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels;

class CCommonLawAccident extends CJfseViewModel
{
    /** @var bool */
    public $common_law_accident;

    /** @var string */
    public $date;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props["common_law_accident"] = "bool notNull";
        $props["date"] = "date";

        return $props;
    }
}
