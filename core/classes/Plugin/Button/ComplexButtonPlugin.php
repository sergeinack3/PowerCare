<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Plugin\Button;

class ComplexButtonPlugin extends ButtonPlugin
{
    public const MAX_COUNTER = 99;
    
    /** @var string */
    private $init_action;

    /** @var int */
    private $counter;

    public function __construct(
        string $label,
        string $class_names,
        bool $disabled,
        string $module_name,
        string $onclick,
        string $script_name,
        ?string $init_action,
        int $counter
    ) {
        parent::__construct($label, $class_names, $disabled, $module_name, $onclick, $script_name);

        $this->init_action = $init_action;
        $this->counter     = $counter;
    }

    /**
     * @return string
     */
    public function getInitAction(): ?string
    {
        return $this->init_action;
    }
    
    public function getCounter(): string
    {
        return $this->counter > self::MAX_COUNTER ? self::MAX_COUNTER . '+' : $this->counter;
    }
}
