<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\Convention;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

/**
 * Class CConventionType
 *
 * @package Ox\Mediboard\Jfse\ViewModels\Convention
 */
class CConventionType extends CJfseViewModel
{
    /** @var string */
    public $code;
    /** @var string */
    public $label;

    public function getProps(): array
    {
        $props          = parent::getProps();
        $props["code"]  = 'str';
        $props["label"] = 'str';

        return $props;
    }
}
