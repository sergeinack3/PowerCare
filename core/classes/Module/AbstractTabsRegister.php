<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Module;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\Kernel\Kernel;
use Ox\Core\Kernel\Routing\RouterBridge;

/**
 * Description
 */
abstract class AbstractTabsRegister
{
    /** @var string */
    public const TAB_SETTINGS = CModule::TAB_SETTINGS;

    /** @var string */
    public const TAB_STANDARD = CModule::TAB_STANDARD;

    /** @var string */
    public const TAB_CONFIGURE = CModule::TAB_CONFIGURE;

    /** @var array */
    protected $tabs = [];

    /** @var CModule */
    protected $module;

    public function __construct(CModule $module)
    {
        $this->module = $module;
    }

    /**
     * The tabs will be displayed in the declared order no matter it's a file or a route
     */
    abstract public function registerAll(): void;

    protected function registerFile(string $file, int $perm, string $group = CModule::TAB_STANDARD): void
    {
        if (!$this->checkPerm($perm)) {
            return;
        }

        if (!$this->checkGroup($group)) {
            return;
        }

        $this->module->addTab($file, $this->generateFileUrl($file, $group), $group);
    }

    private function checkGroup(string $group): bool
    {
        if (!in_array($group, CModule::TABS, true)) {
            return false;
        }

        if (($group === CModule::TAB_CONFIGURE) && (CAppUI::$instance->user_type != 1)) {
            return false;
        }

        return true;
    }

    private function checkPerm(int $perm): bool
    {
        $can = $this->module->canDo();

        switch ($perm) {
            case TAB_READ:
                $add_tab = (bool)$can->read;
                break;

            case TAB_EDIT:
                $add_tab = (bool)$can->edit;
                break;

            case TAB_ADMIN:
                $add_tab = (bool)$can->admin;
                break;

            default:
                $add_tab = false;
        }

        return $add_tab;
    }

    protected function registerRoute(string $route_name, int $perm, string $group = CModule::TAB_STANDARD): void
    {
        if (!$this->checkPerm($perm)) {
            return;
        }

        if (!$this->checkGroup($group)) {
            return;
        }

        try {
            $url = $this->generateRouteUrl($route_name);

            if (strpos($url, 'gui') !== 1) {
                return;
            }

            $this->module->addTab($route_name, $url, $group);
        } catch (Exception $e) {
            return;
        }
    }


    private function generateFileUrl(string $file, string $group = self::TAB_STANDARD): string
    {
        $action = ($group === self::TAB_SETTINGS) ? 'a' : 'tab';

        return sprintf('?m=%s&%s=%s', $this->module->mod_name, $action, $file);
    }

    private function generateRouteUrl(string $route_name): string
    {
        return RouterBridge::generateUrl($route_name);
    }
}
