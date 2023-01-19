<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Ox\Core\CAppUI;
use Ox\Core\Module\AbstractTabsRegister;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * @codeCoverageIgnore
 */
class CTabsHospi extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $this->registerFile("vw_placements", TAB_READ);
        $this->registerFile("edit_sorties", TAB_READ);
        $this->registerFile("vw_recherche", TAB_READ);
        $this->registerFile("vw_suivi_bloc", TAB_READ);
        $this->registerFile("form_print_planning", TAB_READ);

        if (CAppUI::gconf(
                "dPhospi General pathologies"
            ) || (CAppUI::$user instanceof CMediusers && CAppUI::$user->isAdmin())) {
            $this->registerFile('vw_idx_pathologies', TAB_READ);
        }

        $this->registerFile('vw_stats', CAppUI::gconf("dPhospi General stats_for_all") ? TAB_EDIT : TAB_ADMIN);

        if (CModule::getInstalled("printing")) {
            $this->registerFile('vw_printers', TAB_READ);
        }

        $this->registerFile('vw_idx_infrastructure', TAB_ADMIN, self::TAB_SETTINGS);

        if (CAppUI::gconf("dPhospi prestations systeme_prestations") === "standard") {
            $this->registerFile('vw_prestations_standard', TAB_ADMIN, self::TAB_SETTINGS);
        }

        if (CAppUI::gconf("dPhospi prestations systeme_prestations") !== "standard") {
            $this->registerFile('vw_prestations', TAB_ADMIN, self::TAB_SETTINGS);
        }

        $this->registerFile('vw_etiquettes', TAB_ADMIN, self::TAB_SETTINGS);

        if (CAppUI::gconf("dPhospi vue_topologique use_vue_topologique")) {
            $this->registerFile('vw_plan_etage', TAB_READ, self::TAB_SETTINGS);
        }

        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
