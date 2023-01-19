<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\Formula;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

/**
 * Class CFormulaOperand
 *
 * @package Ox\Mediboard\Jfse\ViewModels\Formula
 */
class CFormulaOperand extends CJfseViewModel
{
    /** @var int */
    public $code;

    /** @var string */
    public $label;

    public function getProps(): array
    {
        $props          = parent::getProps();
        $props["code"]  = 'num';
        $props["label"] = 'str';

        return $props;
    }
}
