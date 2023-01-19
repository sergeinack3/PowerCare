<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\CarePath;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CCarePathDoctor extends CJfseViewModel
{
    /** @var int */
    public $invoicing_id;
    /** @var string */
    public $first_name;
    /** @var string */
    public $last_name;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props['invoicing_id'] = 'str length|9';
        $props['first_name']   = 'str';
        $props['last_name']    = 'str';

        return $props;
    }
}
