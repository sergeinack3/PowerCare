<?php

/**
 * @package Mediboard\Plugin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Plugin\Button;

use Ox\Core\CAppUI;

abstract class AbstractAppBarButtonPlugin extends AbstractButtonPlugin
{
    public const LOCATION_APPBAR_SHORTCUT = 'appbar shortcuts';

    public const CONFIG_PREFIX = 'appbar_shortcuts';

    protected static $configs = null;

    abstract protected static function isActive(): bool;

    protected static function isConfigEnabled(string $config): bool
    {
        if (self::$configs === null) {
            // Need to use @ because of CAppUI::conf which trigger an notice (not catchable) if the conf does not exists
            self::$configs = @CAppUI::conf(self::CONFIG_PREFIX) ?: [];
        }

        return isset(self::$configs[$config]) && self::$configs[$config];
    }
}
