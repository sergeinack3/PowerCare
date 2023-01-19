<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Lpp\Exceptions;

use Exception;
use Ox\Core\CMbException;

class LppDatabaseException extends CMbException
{
    public static function databaseError(Exception $e): self
    {
        return new self('LppDatabaseException-error-database_error', $e->getMessage());
    }

    public static function invalidRequestResult(): self
    {
        return new self('LppDatabaseException-error-invalid_request_result');
    }
}
