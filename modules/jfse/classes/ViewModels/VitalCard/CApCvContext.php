<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\VitalCard;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CApCvContext extends CJfseViewModel
{
    /** @var string */
    public $identifier;

    /** @var string */
    public $token;

    /** @var string */
    public $expiration_date;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props['identifier'] = 'str notNull';
        $props['token'] = 'str notNull';
        $props['expiration_date'] = 'dateTime';

        return $props;
    }
}
