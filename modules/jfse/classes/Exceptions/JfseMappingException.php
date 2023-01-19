<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Exceptions;

use LogicException;

/**
 * Class MappingException
 *
 * @package Ox\Mediboard\Jfse\Exceptions
 */
final class JfseMappingException extends LogicException
{
    /**
     * @return $this
     */
    public static function missingFirstCallWithArray(): self
    {
        return new static(
            'Calling self::addOptionalValue without an array requires to call it at least once before hand with an array'
        );
    }
}
