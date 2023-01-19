<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

/**
 * Representation of a single tab.
 * Attributes are public to allow their conversion in JsonAPI.
 */
class CTab
{
    public $mod_name;

    public $tab_name;

    public $is_standard;
    public $is_param;
    public $is_config;
    public $pinned_order;

    private $url;

    public function __construct(
        string $mod_name,
        string $tab_name,
        bool $is_standard,
        bool $is_param,
        bool $is_config,
        ?int $pinned_order,
        string $url
    ) {
        $this->mod_name     = $mod_name;
        $this->tab_name     = $tab_name;
        $this->is_standard  = $is_standard;
        $this->is_param     = $is_param;
        $this->is_config    = $is_config;
        $this->pinned_order = $pinned_order;
        $this->url          = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
    
    public function getDatas(): array
    {
        return [
            'is_config'    => $this->is_config,
            'is_param'     => $this->is_param,
            'is_standard'  => $this->is_standard,
            'mod_name'     => $this->mod_name,
            'pinned_order' => $this->pinned_order,
            'tab_name'     => $this->tab_name,
        ];
    }
}
