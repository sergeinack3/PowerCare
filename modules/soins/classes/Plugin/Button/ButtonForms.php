<?php

/**
 * @package Mediboard\soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins\Plugin\Button;

use Ox\Core\Plugin\Button\AbstractButtonPlugin;
use Ox\Core\Plugin\Button\ButtonPluginManager;

class ButtonForms extends AbstractButtonPlugin
{
    /**
     * @inheritDoc
     */
    public static function registerButtons(ButtonPluginManager $manager): void
    {
        $manager->register(
            'sejour_forms',
            'not-printable fa-list me-notext me-icon me-white me-float-right',
            false,
            ['patient_banner'],
            1,
            'Soins.openFormsWithSejourContext',
            'soins'
        );
    }
}
