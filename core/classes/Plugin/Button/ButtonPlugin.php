<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Plugin\Button;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;

class ButtonPlugin
{
    /** @var string */
    protected $label;

    /** @var string */
    protected $class_names;

    /** @var bool */
    protected $disabled;

    /** @var string */
    protected $module_name;

    /** @var string */
    protected $onclick;

    /** @var string */
    protected $script_name;

    /** @var array */
    protected $parameters = [];

    /** @var string|null */
    protected $serialized_parameters = null;

    /**
     * ButtonPlugin constructor.
     *
     * @param string $label
     * @param string $class_names
     * @param bool   $disabled
     * @param string $module_name
     * @param string $onclick
     * @param string $script_name
     */
    public function __construct(
        string $label,
        string $class_names,
        bool $disabled,
        string $module_name,
        string $onclick,
        string $script_name
    ) {
        $this->label       = $label;
        $this->class_names = $class_names;
        $this->disabled    = $disabled;
        $this->module_name = $module_name;
        $this->onclick     = $onclick;
        $this->script_name = $script_name;
    }

    /**
     * Todo: Inject a LocaleManager into ButtonPluginManager and make it give the translated label instead of
     * translating it here
     *
     * @return string
     */
    public function getLabel(): string
    {
        return CAppUI::tr($this->label);
    }

    /**
     * @return string
     */
    public function getClassNames(): string
    {
        return $this->class_names;
    }

    /**
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * @return string
     */
    public function getModuleName(): string
    {
        return $this->module_name;
    }

    /**
     * @return string
     */
    public function getOnClick(): string
    {
        if ($this->onclick === '') {
            return '';
        }

        return "try { {$this->onclick}({$this->serialized_parameters}); } catch(e) { console.error(e); }";
    }

    public function getAction(): string
    {
        return $this->onclick;
    }

    /**
     * @return string
     */
    public function getScriptName(): string
    {
        return $this->script_name;
    }

    /**
     * Set the call parameters
     * Todo: Beware of non-UTF8 issues?
     *
     * @param array $args Parameters to add
     *
     * @return void
     */
    public function setParameters(array $args): void
    {
        $this->parameters = $args;

        $args                        = array_map([CMbArray::class, 'toJSON'], $this->parameters);
        $this->serialized_parameters = implode(', ', $args);
    }
}
