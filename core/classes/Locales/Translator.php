<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Locales;

use Ox\Core\CAppUI;

/**
 * Object-fallback for CAppUI::tr().
 */
class Translator
{
    /**
     * @param string $key
     * @param mixed  ...$args
     *
     * @return string
     */
    public function tr(string $key, ...$args): string
    {
        return CAppUI::tr($key, ...$args);
    }

    /**
     * Return current user locale
     */
    public function getCurrentLocale(): string
    {
        return CAppUI::pref('LOCALE', 'fr');
    }
}
