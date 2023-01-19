<?php

/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admissions;

use Ox\Core\CAppUI;
use Ox\Core\Module\CModule;
use Ox\Interop\Dmp\CDMP;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Class CConfigurationAdmissions
 */
class AdmissionsService
{
    public static function flagContextualIcons(): bool
    {
        $flag_contextual_icons     = false;
        $systeme_prestations_tiers = CAppUI::gconf("dPhospi prestations systeme_prestations_tiers");

        if (
            CModule::getActive("web100T")
            || CModule::getActive("softway")
            || CModule::getActive("novxtelHospitality")
            || CModule::getActive("notifications")
            || CAppUI::gconf("dPadmissions General show_deficience")
            || !($systeme_prestations_tiers == 'Aucun' || !CModule::getActive($systeme_prestations_tiers))
            || !($systeme_prestations_tiers == 'softway' && CAppUI::gconf("softway presta send_presta_immediately"))
        ) {
            $flag_contextual_icons = true;
        }

        return $flag_contextual_icons;
    }

    public static function flagDMP(): bool
    {
        $flag_dmp = false;
        if (CModule::getActive('dmp')) {
            $auth_type = CDMP::getAuthentificationType();
            $flag_dmp  = ($auth_type === 'indirecte' || ($auth_type === 'directe' && CMediusers::get()->isPraticien()));
        }

        return $flag_dmp;
    }
}
