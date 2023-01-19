<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Handlers\Facades;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\Handlers\Managers\EAIHandlerManager;
use Ox\Core\Handlers\Managers\IndexHandlerManager;
use Ox\Core\Handlers\Managers\ObjectHandlerManager;
use Ox\Core\Logger\LoggerLevels;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * HandlerManager Facade
 */
final class HandlerManager
{
    /** @var array|null Handlers to log (according to a configuration) */
    private static $handlers_to_log = null;

    /**
     * HandlerManager constructor.
     */
    private function __construct()
    {
    }

    private static function getObjectHandlerManager(?int $group_id = null): ObjectHandlerManager
    {
        $group_id = ($group_id) ?: CGroups::get()->_id;

        return ObjectHandlerManager::get($group_id);
    }

    private static function getIndexHandlerManager(?int $group_id = null): IndexHandlerManager
    {
        $group_id = ($group_id) ?: CGroups::get()->_id;

        return IndexHandlerManager::get($group_id);
    }

    private static function getEAIHandlerManager(?int $group_id = null): EAIHandlerManager
    {
        $group_id = ($group_id) ?: CGroups::get()->_id;

        return EAIHandlerManager::get($group_id);
    }

    /**
     * Check if object handler is active according to given CGroups ID
     *
     * @param string   $class    Handler classname
     * @param int|null $group_id CGroups ID
     *
     * @return bool
     */
    public static function isObjectHandlerActive(string $class, ?int $group_id = null): bool
    {
        return self::getObjectHandlerManager($group_id)->isHandlerActive($class);
    }

    /**
     * Check if index handler is active according to given CGroups ID
     *
     * @param string   $class    Handler classname
     * @param int|null $group_id CGroups ID
     *
     * @return bool
     */
    public static function isIndexHandlerActive(string $class, ?int $group_id = null): bool
    {
        return self::getIndexHandlerManager($group_id)->isHandlerActive($class);
    }

    /**
     * Check if EAI handler is active according to given CGroups ID
     *
     * @param string   $class    Handler classname
     * @param int|null $group_id CGroups ID
     *
     * @return bool
     */
    public static function isEAIHandlerActive(string $class, ?int $group_id = null): bool
    {
        return self::getEAIHandlerManager($group_id)->isHandlerActive($class);
    }

    /**
     * Disable all object handlers
     *
     * @return void
     */
    public static function disableObjectHandlers(?int $group_id = null): void
    {
        self::getObjectHandlerManager($group_id)->disableHandlers();
    }

    /**
     * Disable a given object handler
     *
     * @param string $handler_class Object handler classname (sn)
     *
     * @return void
     */
    public static function disableObjectHandler(string $handler_class, ?int $group_id = null): void
    {
        self::getObjectHandlerManager($group_id)->disableHandler($handler_class);
    }

    /**
     * Enable an object handler according to given classname
     *
     * @param string $handler_class Object handler classname (sn)
     *
     * @return void
     */
    public static function enableObjectHandler(string $handler_class, ?int $group_id = null): void
    {
        self::getObjectHandlerManager($group_id)->enableHandler($handler_class);
    }

    /**
     * Enforce the activation of a given handler on all groups.
     *
     * @param string $handler_class
     */
    public static function enforceObjectHandler(string $handler_class): void
    {
        ObjectHandlerManager::enforceHandler($handler_class);
    }

    /**
     * Drop the enforcing of a given handler on all groups.
     *
     * @param string $handler_class
     */
    public static function dropObjectHandler(string $handler_class): void
    {
        ObjectHandlerManager::dropHandler($handler_class);
    }

    /**
     * Disable all index handlers
     *
     * @return void
     */
    public static function disableIndexHandlers(?int $group_id = null): void
    {
        self::getIndexHandlerManager($group_id)->disableHandlers();
    }

    /**
     * Disable a given index handler
     *
     * @param string $handler_class Index handler classname (sn)
     *
     * @return void
     */
    public static function disableIndexHandler(string $handler_class, ?int $group_id = null): void
    {
        self::getIndexHandlerManager($group_id)->disableHandler($handler_class);
    }

    /**
     * Enable an index handler according to given classname
     *
     * @param string $handler_class Index handler classname (sn)
     *
     * @return void
     */
    public static function enableIndexHandler(string $handler_class, ?int $group_id = null): void
    {
        self::getIndexHandlerManager($group_id)->enableHandler($handler_class);
    }

    /**
     * Disable all EAI handlers
     *
     * @return void
     */
    public static function disableEAIHandlers(?int $group_id = null): void
    {
        self::getEAIHandlerManager($group_id)->disableHandlers();
    }

    /**
     * Disable a given EAI handler
     *
     * @param string $handler_class EAI handler classname (sn)
     *
     * @return void
     */
    public static function disableEAIHandler(string $handler_class, ?int $group_id = null): void
    {
        self::getEAIHandlerManager($group_id)->disableHandler($handler_class);
    }

    /**
     * Enable an EAI handler according to given classname
     *
     * @param string $handler_class EAI handler classname (sn)
     *
     * @return void
     */
    public static function enableEAIHandler(string $handler_class, ?int $group_id = null): void
    {
        self::getEAIHandlerManager($group_id)->enableHandler($handler_class);
    }

    /**
     * Instantiate enabled object handlers
     *
     * @return void
     * @throws Exception
     */
    public static function makeObjectHandlers(?int $group_id = null): void
    {
        self::getObjectHandlerManager($group_id)->makeHandlers();
    }

    /**
     * @return array
     */
    public static function getObjectHandlers(?int $group_id = null): array
    {
        return self::getObjectHandlerManager($group_id)->getHandlers();
    }

    /**
     * Instantiate enabled index handlers
     *
     * @return void
     * @throws Exception
     */
    public static function makeIndexHandlers(?int $group_id = null): void
    {
        self::getIndexHandlerManager($group_id)->makeHandlers();
    }

    /**
     * @return array
     */
    public static function getIndexHandlers(?int $group_id = null): array
    {
        return self::getIndexHandlerManager($group_id)->getHandlers();
    }

    /**
     * Instantiate enabled EAI handlers
     *
     * @return void
     * @throws Exception
     */
    public static function makeEAIHandlers(?int $group_id = null): void
    {
        self::getEAIHandlerManager($group_id)->makeHandlers();
    }

    /**
     * @return array
     */
    public static function getEAIHandlers(?int $group_id = null): array
    {
        return self::getEAIHandlerManager($group_id)->getHandlers();
    }

    /**
     * For TU purpose only
     *
     * @return void
     */
    public static function resetHandlers(?int $group_id = null): void
    {
        self::getObjectHandlerManager($group_id)->resetHandlers();
        self::getIndexHandlerManager($group_id)->resetHandlers();
        self::getEAIHandlerManager($group_id)->resetHandlers();
    }

    private static function loadHandlersToLog(): void
    {
        if (is_array(self::$handlers_to_log)) {
            return;
        }

        self::$handlers_to_log = [];

        $handlers_to_log = CAppUI::conf('logged_handler_calls_list');

        if (!$handlers_to_log) {
            return;
        }

        $handlers = explode(';', $handlers_to_log);

        array_walk(
            $handlers,
            function (&$handler): void {
                $handler = trim($handler);
            }
        );

        if ($handlers === false) {
            return;
        }

        self::$handlers_to_log = array_fill_keys($handlers, true);
    }

    /**
     * @param object $observer
     *
     * @return bool
     * @throws Exception
     */
    public static function mustLogHandler(object $observer): bool
    {
        self::loadHandlersToLog();

        $sn = CClassMap::getSN($observer);

        return isset(self::$handlers_to_log[$sn]);
    }

    /**
     * @param string     $message
     * @param object     $observer
     * @param string     $method
     * @param mixed|null $data
     *
     * @throws Exception
     */
    public static function trace(string $message, object $observer, string $method, $data = null): void
    {
        $msg = sprintf("[%s]: %s::%s {$message}", CApp::getRequestUID(), get_class($observer), $method);

        CApp::log($msg, $data, LoggerLevels::LEVEL_DEBUG);
    }
}
