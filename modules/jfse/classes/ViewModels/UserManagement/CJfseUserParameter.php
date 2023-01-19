<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\UserManagement;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CJfseUserParameter extends CJfseViewModel
{
    /** @var int */
    public $id;

    /** @var string The name of the parameter */
    public $name;

    /** @var mixed The value of the parameter */
    public $value;

    /**
     * @return array
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['id']     = 'num';
        $props['name']   = 'str';
        $props['value']  = 'str';

        return $props;
    }
}
