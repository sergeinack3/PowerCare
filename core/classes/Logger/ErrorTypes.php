<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Logger;

use CompileError;
use Error;
use ParseError;

/**
 * Constantes to handle error types and error categories
 */
final class ErrorTypes
{
    public const TYPES = [
        "exception"         => "exception",
        E_ERROR             => "error",
        E_WARNING           => "warning",
        E_PARSE             => "parse",
        E_NOTICE            => "notice",
        E_CORE_ERROR        => "core_error",
        E_CORE_WARNING      => "core_warning",
        E_COMPILE_ERROR     => "compile_error",
        E_COMPILE_WARNING   => "compile_warning",
        E_USER_ERROR        => "user_error",
        E_USER_WARNING      => "user_warning",
        E_USER_NOTICE       => "user_notice",
        E_STRICT            => "strict",
        E_RECOVERABLE_ERROR => "recoverable_error",
        E_DEPRECATED        => "deprecated",
        E_USER_DEPRECATED   => "user_deprecated",
    ];

    public const CATEGORIES = [
        "exception"         => "warning",
        E_ERROR             => "error",
        E_WARNING           => "warning",
        E_PARSE             => "error",
        E_NOTICE            => "notice",
        E_CORE_ERROR        => "error",
        E_CORE_WARNING      => "warning",
        E_COMPILE_ERROR     => "error",
        E_COMPILE_WARNING   => "warning",
        E_USER_ERROR        => "error",
        E_USER_WARNING      => "warning",
        E_USER_NOTICE       => "notice",
        E_STRICT            => "notice",
        E_RECOVERABLE_ERROR => "error",
        E_DEPRECATED        => "notice",
        E_USER_DEPRECATED   => "notice",
    ];

    /**
     * Get error types by level : error, warning and notice
     */
    public static function getErrorTypesByCategory(): array
    {
        $categories = [
            "error"   => [],
            "warning" => [],
            "notice"  => [],
        ];

        foreach (self::CATEGORIES as $type => $category) {
            $categories[$category][] = self::TYPES[$type];
        }

        return $categories;
    }

    public static function getCode(Error $error): int
    {
        switch (get_class($error)) {
            case ParseError::class:
                return E_PARSE;
            case CompileError::class:
                return E_COMPILE_ERROR;
            default:
                return E_ERROR;
        }
    }
}
