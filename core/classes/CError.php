<?php
/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use ErrorException;
use Exception;
use Ox\Core\Logger\ErrorTypes;
use Ox\Core\Logger\Handler\ErrorBufferHandler;
use Ox\Core\Logger\LoggerLevels;
use Ox\Core\Logger\Wrapper\ErrorLoggerWrapper;

/**
 * V1 Error Handler
 */
class CError
{
    /** @var array */
    static $output = [];

    /** @var array */
    static $_excluded = [
        E_STRICT,
        E_DEPRECATED,        // BCB
        E_RECOVERABLE_ERROR, // Thrown by bad type hinting, to be removed
    ];

    /**
     * @var ErrorLoggerWrapper $logger
     */
    private static $logger;

    /**
     * Error handlers and configuration
     */
    public static function init()
    {
        global $dPconfig;
        // Do not set to E_STRICT as it hides fatal errors to our error handler

        // Developement
        //error_reporting(E_ALL | E_STRICT | E_USER_DEPRECATED | E_DEPRECATED);

        // Production
        error_reporting(E_ALL);

        ini_set("log_errors_max_len", "4M");
        ini_set("log_errors", true);
        ini_set("display_errors", $dPconfig["debug"]);

        // Set Handlers
        set_error_handler([static::class, 'errorHandler']);
        set_exception_handler([static::class, 'exceptionHandler']);

        // register shutdown
        CApp::registerShutdown([static::class, "logLastError"], CApp::ERROR_PRIORITY);
        CApp::registerShutdown([static::class, "displayErrors"], CApp::ERROR_PRIORITY);
    }

    /**
     * @throws Exception
     */
    static function getLogger(): ErrorLoggerWrapper
    {
        if (is_null(static::$logger)) {
            static::$logger = new ErrorLoggerWrapper();
        }

        return static::$logger;
    }


    /**
     * Because fata error (memory exhausted, max execution time ..) triggers script termination :
     * Shutdown function log uncatched error
     *
     * @throws Exception
     */
    static function logLastError()
    {
        $error = error_get_last();
        if (!is_null($error) && class_exists(CApp::class, false)) {
            $type = ErrorTypes::TYPES[$error['type']];
            CApp::log("Uncatched {$type}", $error, LoggerLevels::LEVEL_CRITICAL);
        }
    }

    /**
     * Custom error handler
     *
     * @param string $code Error code
     * @param string $text Error text
     * @param string $file Error file path
     * @param string $line Error line number
     *
     * @throws Exception
     *
     */
    public static function errorHandler($code, $text, $file, $line)
    {
        // Handles the @ case and ignored error
        $error_reporting = error_reporting();
        if (!self::isReportingActive($error_reporting) || in_array($code, CError::$_excluded)) {
            return;
        }

        // Convert to Throwable
        // TODO Check real severity
        $exception = new ErrorException($text, 0, $code, $file, $line);

        // Log
        static::logException($exception);
        // ... script continue
    }

    private static function isReportingActive(int $error_reporting): bool
    {
        // Before PHP 8.0 @ totaly disable the error_reporting
        if (PHP_VERSION_ID < 80000) {
            return $error_reporting;
        }

        // After php 8.0 the @ no longer silence fatal errors
        // (E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR)
        return $error_reporting && $error_reporting !==
            (E_ERROR + E_PARSE + E_CORE_ERROR + E_COMPILE_ERROR + E_USER_ERROR + E_RECOVERABLE_ERROR);
    }

    /**
     * Custom throwable handler
     * ExceptionListener do not catch Internal PHP Errors
     *
     * @param Exception $exception
     *
     * @throws Exception
     */
    public static function exceptionHandler($exception)
    {
        static::logException($exception);
        // ... script stop
    }

    /**
     * @param Exception $exception
     *
     * @param bool      $display_errors
     *
     * @throws Exception
     */
    public static function logException($exception)
    {
        // Devtools
        if (CDevtools::isActive()) {
            // when application does not in peace we receive this ErrorException
            if ($exception instanceof ErrorException && $exception->getMessage() === 'Application died unexpectedly') {
                CDevtools::makeTmpFile();
            } else {
                CApp::error($exception);
            }
        }

        $code = $exception instanceof ErrorException ? $exception->getSeverity() : "exception";
        CApp::$performance[ErrorTypes::CATEGORIES[$code]]++;

        $logger = self::getLogger();
        $logger->log(LoggerLevels::LEVEL_ERROR, get_class($exception), ['exception' => $exception]);

        return true;
    }

    /**
     * Add count error info and link to open logs
     */
    public static function displayErrors(bool $echo = true): ?string
    {
        $error_count = ErrorBufferHandler::getErrorCount();
        $html        = null;
        if ($error_count > 0 && ini_get("display_errors") && PHP_SAPI !== 'cli') {
            $error_count = number_format($error_count, 0, null, ' ');
            $request_uid = CApp::getRequestUID();
            $html        = "\n\n<div class='big-warning'>";
            $html        .= "Warning : {$error_count} errors occured while processing your request ";
            $html        .= "<a href='?m=dPdeveloppement&tab=view_logs&request_uid={$request_uid}' target='_blank' title='Open logs in new tab'>(open)</a>";
            $html        .= "</div>";
            if ($echo) {
                echo $html;
            }
        }

        return $html;
    }

    public static function setDisplayMode(bool $display): void
    {
        ini_set('display_errors', $display);
    }

}
