<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Status;

use ErrorException;

/**
 * Convert error to exception
 * StatusErrorHandler
 */
class StatusErrorHandler
{
    /**
     * @param string $severity
     * @param string $message
     * @param string $file
     * @param string $line
     *
     * @throws ErrorException
     */
    public static function exception_error_handler($severity, $message, $file, $line): void
    {
        $error_reporting = error_reporting();
        if (
            !$error_reporting || ($error_reporting ===
                (E_ERROR + E_PARSE + E_CORE_ERROR + E_COMPILE_ERROR + E_USER_ERROR + E_RECOVERABLE_ERROR))
        ) {
            return;
        }

        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * setHandlers
     */
    public static function setHandlers(): void
    {
        set_error_handler([static::class, 'exception_error_handler']);
    }
}
