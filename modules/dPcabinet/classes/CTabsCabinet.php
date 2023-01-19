<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Ox\Core\CAppUI;
use Ox\Core\Module\AbstractTabsRegister;
use Ox\Core\Module\CModule;

/**
 * @codeCoverageIgnore
 */
class CTabsCabinet extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        if (CAppUI::pref("new_semainier") == "1") {
            $this->registerFile('weeklyPlanning', TAB_READ);
            $this->registerFile('vw_journee_new', TAB_READ);
        }
        if (CAppUI::pref("new_semainier") != "1") {
            $this->registerFile('vw_planning', TAB_READ);
            $this->registerFile('vw_journee', TAB_READ);
        }

        $this->registerFile("edit_planning", TAB_READ);
        $this->registerFile("edit_consultation", TAB_EDIT);
        $this->registerFile("form_print_plages", TAB_READ);
        $this->registerFile("vw_compta", TAB_EDIT);
        $this->registerFile("vw_factures", TAB_ADMIN);
        $this->registerFile("vw_evenements_rappel", TAB_EDIT);
        $this->registerFile("vw_stats", TAB_ADMIN);

        if (CModule::getActive("fse")) {
            $this->registerFile("vw_fse", TAB_READ);
        }
        $this->registerFile("vw_plages_ressources", TAB_EDIT);

        $this->registerFile('vw_edit_tarifs', TAB_EDIT, self::TAB_SETTINGS);
        $this->registerFile('vw_categories', TAB_EDIT, self::TAB_SETTINGS);
        $this->registerFile('showBanks', TAB_ADMIN, self::TAB_SETTINGS);
        $this->registerFile('vw_ressources', TAB_EDIT, self::TAB_SETTINGS);

        if (CModule::getActive("dPprescription")) {
            $this->registerFile('vw_idx_livret', TAB_EDIT, self::TAB_SETTINGS);
        }
        $this->registerFile('vw_info_checklist', TAB_ADMIN, self::TAB_SETTINGS);
        $this->registerFile('vw_edit_lieux', TAB_EDIT, self::TAB_SETTINGS);

        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
