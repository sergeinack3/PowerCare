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

class ExternalMessagerieShortcut extends InternalMessagerieShortcut
{
    protected static function getType(): string
    {
        return 'external';
    }

    protected static function getLabel(): string
    {
        return 'messagerie-external-title-access';
    }

    protected static function getDisabled(): bool
    {
        return !CAppUI::gconf('messagerie access allow_external_mail');
    }

    protected static function getAction(): string
    {
        return '';
    }

    protected static function getInitAction(): string
    {
        return 'Messagerie.initExternalMessagerie';
    }

    protected static function getIcon(): string
    {
        return 'email';
    }
}
