<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Plugin\Button;

use Exception;
use Ox\Core\CClassMap;
use Ox\Core\Module\ModuleManagerTrait;
use SplPriorityQueue;

/**
 * Button Plugin Manager
 */
class ButtonPluginManager
{
    public const BUTTON_LABEL       = 'label';
    public const BUTTON_CLASSES     = 'class_names';
    public const BUTTON_DISABLED    = 'disabled';
    public const BUTTON_LOCATIONS   = 'locations';
    public const BUTTON_PRIORITY    = 'priority';
    public const BUTTON_ONCLICK     = 'onclick';
    public const BUTTON_SCRIPT_NAME = 'script_name';
    public const BUTTON_INIT_ACTION = 'init_action';
    public const BUTTON_COUNTER     = 'counter';

    use ModuleManagerTrait;

    /** @var self */
    private static $instance;

    /** @var array */
    private $locations = [];

    /** @var array */
    private $register_bag = [];

    /** @var array */
    private $complex_register_bag = [];

    /**
     * ButtonPluginManager constructor.
     */
    private function __construct()
    {
    }

    /**
     * @return static
     * @throws Exception
     */
    public static function get(): self
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        $instance = new self();
        $instance->registerAll();

        return self::$instance = $instance;
    }

    /**
     * Register all buttons declared in modules
     *
     * @return void
     * @throws Exception
     */
    private function registerAll(): void
    {
        $module_button_plugin = $this->getRegisteredButtons();

        /** @var AbstractButtonPlugin $_module_button_plugin */
        foreach ($module_button_plugin as $_module_button_plugin) {
            $_module_name = $this->getModuleForClass($_module_button_plugin);

            if (!$this->isModuleActive($_module_name)) {
                continue;
            }

            $_module_button_plugin::registerButtons($this);
            $this->applyForModule($_module_name);
        }
    }

    /**
     * Get the Button classes
     *
     * @return array
     * @throws Exception
     */
    protected function getRegisteredButtons(): array
    {
        return CClassMap::getInstance()->getClassChildren(
            AbstractButtonPlugin::class,
            false,
            true
        );
    }

    /**
     * Create buttons from the register bag for each location, according to priority
     *
     * @param string $module_name
     *
     * @return void
     */
    private function applyForModule(string $module_name): void
    {
        $this->registerButtons($module_name, $this->register_bag);
        $this->registerButtons($module_name, $this->complex_register_bag, true);

        // Resetting the bags
        $this->register_bag         = [];
        $this->complex_register_bag = [];
    }

    private function registerButtons(string $module_name, array $register_bag, bool $complex = false): void
    {
        foreach ($register_bag as $_button_to_register) {
            $_button = $complex
                ? $this->initComplexButton($module_name, $_button_to_register)
                : $this->initButton($module_name, $_button_to_register);

            $_priority = $_button_to_register[self::BUTTON_PRIORITY];

            foreach ($_button_to_register[self::BUTTON_LOCATIONS] as $_location) {
                $this->registerButtonForLocation($_button, $_location, $_priority);
            }
        }
    }

    private function initButton(string $module_name, array $button_to_register): ButtonPlugin
    {
        return new ButtonPlugin(
            $button_to_register[self::BUTTON_LABEL],
            $button_to_register[self::BUTTON_CLASSES],
            $button_to_register[self::BUTTON_DISABLED],
            $module_name,
            $button_to_register[self::BUTTON_ONCLICK],
            $button_to_register[self::BUTTON_SCRIPT_NAME],
        );
    }

    private function initComplexButton(string $module_name, array $button_to_register): ComplexButtonPlugin
    {
        return new ComplexButtonPlugin(
            $button_to_register[self::BUTTON_LABEL],
            $button_to_register[self::BUTTON_CLASSES],
            $button_to_register[self::BUTTON_DISABLED],
            $module_name,
            $button_to_register[self::BUTTON_ONCLICK],
            $button_to_register[self::BUTTON_SCRIPT_NAME],
            $button_to_register[self::BUTTON_INIT_ACTION],
            $button_to_register[self::BUTTON_COUNTER]
        );
    }

    /**
     * Get the buttons for a given location
     *
     * @param string $location
     * @param mixed  ...$args
     *
     * @return array
     */
    public function getButtonsForLocation(string $location, ...$args): array
    {
        if (!$this->isLocationRegistered($location)) {
            return [];
        }

        // Because of loop is dequeuing elements
        $queue = clone $this->locations[$location];

        $buttons = [];

        /** @var ButtonPlugin $_item */
        foreach ($queue as $_item) {
            $_item->setParameters($args);

            $buttons[] = $_item;
        }

        return $buttons;
    }

    /**
     * @param ButtonPlugin $button
     * @param string       $location
     * @param int          $priority
     *
     * @return void
     */
    private function registerButtonForLocation(ButtonPlugin $button, string $location, int $priority): void
    {
        $this->registerLocation($location)->insert($button, $priority);
    }

    /**
     * @param string $location
     *
     * @return bool
     */
    private function isLocationRegistered(string $location): bool
    {
        return array_key_exists($location, $this->locations);
    }

    /**
     * @param string $location
     *
     * @return SplPriorityQueue
     */
    private function registerLocation(string $location): SplPriorityQueue
    {
        if ($this->isLocationRegistered($location)) {
            return $this->locations[$location];
        }

        return $this->locations[$location] = new SplPriorityQueue();
    }

    public function register(
        string $label,
        string $class_names,
        bool $disabled,
        array $locations,
        int $priority,
        string $onclick,
        string $script_name
    ): void {
        $this->register_bag[] = [
            self::BUTTON_LABEL       => $label,
            self::BUTTON_CLASSES     => $class_names,
            self::BUTTON_DISABLED    => $disabled,
            self::BUTTON_LOCATIONS   => $locations,
            self::BUTTON_PRIORITY    => $priority,
            self::BUTTON_ONCLICK     => $onclick,
            self::BUTTON_SCRIPT_NAME => $script_name,
        ];
    }

    public function registerComplex(
        string $label,
        string $class_names,
        bool $disabled,
        array $locations,
        int $priority,
        string $onclick,
        string $script_name,
        string $init_action,
        int $counter
    ): void {
        $this->complex_register_bag[] = [
            self::BUTTON_LABEL       => $label,
            self::BUTTON_CLASSES     => $class_names,
            self::BUTTON_DISABLED    => $disabled,
            self::BUTTON_LOCATIONS   => $locations,
            self::BUTTON_PRIORITY    => $priority,
            self::BUTTON_ONCLICK     => $onclick,
            self::BUTTON_SCRIPT_NAME => $script_name,
            self::BUTTON_INIT_ACTION => $init_action,
            self::BUTTON_COUNTER     => $counter,
        ];
    }
}
