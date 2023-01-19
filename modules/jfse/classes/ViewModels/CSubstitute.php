<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class CSubstitute extends CJfseViewModel
{
    /** @var int */
    public $id;

    /** @var int */
    public $user_id;

    /** @var string */
    public $last_name;

    /** @var string */
    public $first_name;

    /** @var string */
    public $invoicing_number;

    /** @var string */
    public $national_id;

    /** @var int */
    public $situation_id;

    /**
     * @return array
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['id']               = 'num';
        $props['user_id']          = 'num';
        $props['last_name']        = 'str';
        $props['first_name']       = 'str';
        $props['invoicing_number'] = 'str';
        $props['national_id']      = 'str';
        $props['situation_id']     = 'num';

        return $props;
    }
}
