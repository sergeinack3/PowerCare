<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Handlers\Managers;

use Ox\Core\CAppUI;

/**
 * Description
 */
class ObjectHandlerManager extends AbstractHandlerManager
{
    /** @var static[] */
    private static $instances = [];

    /** @var array */
    private static $whitelist = [];

    /**
     * @param int $group_id
     *
     * @return static
     */
    public static function get(int $group_id): self
    {
        if (isset(self::$instances[$group_id])) {
            return self::$instances[$group_id];
        }

        return self::$instances[$group_id] = new static($group_id);
    }

    /**
     * @inheritDoc
     */
    protected function getHandlersConfig(): array
    {
        $handlers = CAppUI::gconf('system object_handlers', $this->group_id);

        if (is_array($handlers)) {
            return $handlers;
        }

        // Should always be an array
        return [];
    }

    /**
     * @inheritDoc
     */
    public function isHandlerActive(string $class): bool
    {
        return (bool)CAppUI::gconf("system object_handlers {$class}", $this->group_id);
    }

    /**
     * @@inheritDoc
     */
    public static function enforceHandler(string $handler_class): void
    {
        self::$whitelist[$handler_class] = true;
    }

    /**
     * @inheritDoc
     */
    public static function dropHandler(string $handler_class): void
    {
        unset(self::$whitelist[$handler_class]);
    }

    /**
     * @param string $handler_class
     *
     * @inheritDoc
     */
    protected static function isHandlerEnforced(string $handler_class): bool
    {
        return (self::$whitelist[$handler_class]) ?? false;
    }
}
