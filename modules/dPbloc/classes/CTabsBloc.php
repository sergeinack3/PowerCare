<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Bloc;

use Ox\Core\CAppUI;
use Ox\Core\Module\AbstractTabsRegister;
use Ox\Core\Module\CModule;

/**
 * @codeCoverageIgnore
 */
class CTabsBloc extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $this->registerFile('vw_edit_planning', TAB_READ);

        $view_planning_bloc = CAppUI::pref('view_planning_bloc');

        if ($view_planning_bloc == 'timeline') {
            $tab_suivi_salle = "vw_timeline_salles";
        } elseif ($view_planning_bloc == 'horizontal') {
            $tab_suivi_salle = "vw_horizontal_planning";
        } else {
            $tab_suivi_salle = "vw_suivi_salles";
        }
        $this->registerFile($tab_suivi_salle, TAB_READ);

        if (CModule::getActive("reservation")) {
            $this->registerFile('vw_planning', TAB_READ);
        }
        $this->registerFile('vw_urgences', TAB_EDIT);
        $this->registerFile('vw_departs_us', TAB_EDIT);

        if (CAppUI::gconf("dPbloc CPlageOp systeme_materiel") == "standard") {
            $this->registerFile('vw_idx_materiel', TAB_EDIT);
        }
        $this->registerFile('vw_blocages', TAB_EDIT);
        $this->registerFile('print_planning', TAB_READ);

        $this->registerFile('vw_idx_blocs', TAB_ADMIN, self::TAB_SETTINGS);
        $this->registerFile('vw_plan_etage_blocs', TAB_READ, self::TAB_SETTINGS);

        if (CAppUI::gconf("dPbloc CPlageOp systeme_materiel") == "expert") {
            $this->registerFile('vw_ressources', TAB_EDIT, self::TAB_SETTINGS);
        }

        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
