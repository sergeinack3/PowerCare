<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse;

use MyCLabs\Enum\Enum;

abstract class JfseEnum extends Enum
{
    public static function getProp(): string
    {
        return "enum list|" . implode('|', self::values());
    }
}
