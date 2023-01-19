<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\Formula;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CFormulaParameter extends CJfseViewModel
{
    /** @var string */
    public $number;

    /** @var string */
    public $label;

    /** @var string */
    public $type;

    /** @var float */
    public $value;

    /**
     * @return array
     */
    public function getProps(): array
    {
        $props           = parent::getProps();
        $props["number"] = 'str';
        $props["label"]  = 'str';
        $props["type"]   = 'str';
        $props["value"]  = 'float';

        return $props;
    }
}
