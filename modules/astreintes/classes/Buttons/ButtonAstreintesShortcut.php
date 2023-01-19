<?php

/**
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Astreintes\Buttons;

use Ox\Core\Module\CModule;
use Ox\Core\Plugin\Button\AbstractAppBarButtonPlugin;
use Ox\Core\Plugin\Button\ButtonPluginManager;

class ButtonAstreintesShortcut extends AbstractAppBarButtonPlugin
{
    public const CONFIG = 'ButtonAstreintesShortcut';

    public static function registerButtons(ButtonPluginManager $manager): void
    {
        if (self::isActive()) {
            $manager->register(
                'CPlageAstreinte',
                'phone',
                false,
                [self::LOCATION_APPBAR_SHORTCUT],
                1,
                'PlageAstreinte.modaleastreinteForDay',
                'plage'
            );
        }
    }

    protected static function isActive(): bool
    {
        return CModule::getActive('astreintes')
            && self::isConfigEnabled(self::CONFIG);
    }
}
