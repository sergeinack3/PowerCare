<?php

/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp;

use Ox\Core\CAppUI;
use Ox\Core\Module\AbstractTabsRegister;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * @codeCoverageIgnore
 */
class CTabsSalleOp extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        global $can;

        // Chargement de l'utilisateur courant
        $user = CMediusers::get();
        $user->isPraticien();
        $user_can_edit      = (!$user->_is_praticien || ($user->_is_praticien && $can->edit));
        $view_planning_bloc = CAppUI::pref('view_planning_bloc');

        if ($view_planning_bloc == 'timeline') {
            $tab_suivi_salle = "vw_timeline_salles";
        } else {
            $tab_suivi_salle = "vw_suivi_salles";
        }

        $this->registerFile('vw_operations', TAB_READ);

        if ($user_can_edit) {
            $this->registerFile('vw_reveil', TAB_READ);
            $this->registerFile('vw_urgences', TAB_READ);
            $this->registerFile($tab_suivi_salle, TAB_READ);
            $this->registerFile('vw_suivi_sterilisation', TAB_READ);
            $this->registerFile('vw_interv_non_cotees', TAB_EDIT);
            $this->registerFile('vw_traceability', TAB_READ);
        }

        if (CModule::getActive("vivalto")) {
            $this->registerFile('vw_dmi', TAB_READ);
        }

        if ($user_can_edit) {
            $this->registerFile('vw_daily_check_list_type', TAB_ADMIN, self::TAB_SETTINGS);
            $this->registerFile('viewDailyCheckListGroup', TAB_ADMIN, self::TAB_SETTINGS);
            $this->registerFile('ajax_vw_gestes_perop', TAB_ADMIN, self::TAB_SETTINGS);
        }

        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
