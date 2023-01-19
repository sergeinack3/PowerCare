<?php

/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie\Buttons;

use Ox\Core\CAppUI;
use Ox\Core\Module\CModule;
use Ox\Core\Plugin\Button\AbstractAppBarButtonPlugin;
use Ox\Core\Plugin\Button\ButtonPluginManager;

class InternalMessagerieShortcut extends AbstractAppBarButtonPlugin
{
    protected static $counters;

    protected static function isActive(): bool
    {
        return CModule::getActive('messagerie')
                && CModule::getCanDo('messagerie')->read
                && !CAppUI::isPatient();
    }

    public static function registerButtons(ButtonPluginManager $manager): void
    {
        if (static::isActive()) {
            $manager->registerComplex(
                static::getLabel(),
                static::getIcon(),
                static::getDisabled(),
                [self::LOCATION_APPBAR_SHORTCUT],
                0,
                static::getAction(),
                'Messagerie',
                static::getInitAction(),
                static::getCounter()
            );
        }
    }

    protected static function getCounter(): int
    {
        self::initCounter();

        return self::$counters[static::getType()]['total'] ?? 0;
    }

    protected static function initCounter(): void
    {
        // Init $counter only once by using self
        if (!self::$counters) {
            self::$counters = CAppUI::getMessagerieCounters();
        }
    }

    protected static function getType(): string
    {
        return 'internal';
    }

    protected static function getLabel(): string
    {
        return 'messagerie-internal-title-access';
    }

    protected static function getDisabled(): bool
    {
        return !CAppUI::gconf('messagerie access allow_internal_mail');
    }

    protected static function getAction(): string
    {
        return json_encode(['callable' => 'Messagerie.openModal', 'arguments' => ['internal']]);
    }

    protected static function getInitAction(): string
    {
        return 'Messagerie.periodicalCount';
    }

    protected static function getIcon(): string
    {
        return 'accountGroup';
    }
}
