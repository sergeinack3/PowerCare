<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Handlers\Managers;

use Exception;
use Ox\Core\CClassMap;
use Ox\Core\Module\ModuleManagerTrait;

/**
 * Description
 */
abstract class AbstractHandlerManager
{
    use ModuleManagerTrait;

    /** @var int */
    protected $group_id;

    /** @var array|null */
    private $handlers = null;

    /** @var array */
    private $ignored_handlers = [];

    /**
     * AbstractHandlerManager constructor.
     *
     * @param int $group_id
     */
    protected function __construct(int $group_id)
    {
        $this->group_id = $group_id;
    }

    /**
     * Get handlers in configuration.
     *
     * @return array
     */
    abstract protected function getHandlersConfig(): array;

    /**
     * Check if handler is active in configuration.
     *
     * @param string $class Handler classname (SN).
     *
     * @return bool
     */
    abstract public function isHandlerActive(string $class): bool;

    /**
     * Disable handlers.
     *
     * @return void
     */
    public function disableHandlers(): void
    {
        $this->handlers = [];
    }

    /**
     * Disable a given handler.
     *
     * @param string $handler_class Handler classname (SN).
     *
     * @return void
     */
    public function disableHandler(string $handler_class): void
    {
        $this->ignored_handlers[$handler_class] = $handler_class;
        unset($this->handlers[$handler_class]);
    }

    /**
     * Enable an handler according to given classname.
     *
     * @param string $handler_class Handler classname (SN).
     *
     * @return void
     */
    public function enableHandler(string $handler_class): void
    {
        unset($this->ignored_handlers[$handler_class]);
        $this->handlers[$handler_class] = new $handler_class();
    }

    /**
     * Tell whether a handler is ignored.
     *
     * @param string $handler_class Handler classname (SN).
     *
     * @return bool
     */
    public function isHandlerIgnored(string $handler_class): bool
    {
        return isset($this->ignored_handlers[$handler_class]);
    }

    /**
     * Instantiate enabled handlers.
     *
     * @return void
     * @throws Exception
     */
    public function makeHandlers(): void
    {
        // Handlers already initialized
        if (is_array($this->handlers)) {
            return;
        }

        $this->handlers = [];

        foreach ($this->getHandlersConfig() as $_class => $_active) {
            if (($_active && !$this->isHandlerIgnored($_class)) || static::isHandlerEnforced($_class)) {
                if (!class_exists($_class)) {
                    throw new Exception(
                        sprintf(
                            "common-error-Handler missing class: %s. Check the handlers configuration.",
                            $_class
                        )
                    );
                }

                $_namespaced_class = CClassMap::getInstance()->getAliasByShortName($_class);

                if (!$this->isModuleActiveForClass($_namespaced_class)) {
                    continue;
                }

                $this->handlers[$_class] = new $_class();
            }
        }
    }

    /**
     * @return array
     */
    public function getHandlers(): array
    {
        return (is_array($this->handlers)) ? $this->handlers : [];
    }

    /**
     * For TU purpose only.
     *
     * @return void
     */
    public function resetHandlers(): void
    {
        $this->handlers = null;
    }

    /**
     * Enforce activation of a handler on all groups.
     *
     * @param string $handler_class
     */
    abstract public static function enforceHandler(string $handler_class): void;

    /**
     * Drop an enforced handler on all groups.
     *
     * @param string $handler_class
     */
    abstract public static function dropHandler(string $handler_class): void;

    /**
     * Tell whether a handler is enforced.
     *
     * @param string $handler_class
     *
     * @return bool
     */
    abstract protected static function isHandlerEnforced(string $handler_class): bool;
}
