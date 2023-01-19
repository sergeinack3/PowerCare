<?php

/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\CAppUI;
use Ox\Core\Module\AbstractTabsRegister;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Tabs for the module dPplanningOp
 */
/**
 * @codeCoverageIgnore
 */
class CTabsPlanningOp extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $curr_group = CGroups::loadCurrent();
        $hors_plage = new CIntervHorsPlage();
        $this->registerFile('vw_idx_planning', TAB_EDIT);
        $this->registerFile('vw_edit_sejour', TAB_READ);
        $this->registerFile('vw_edit_planning', TAB_EDIT);
        if ($hors_plage->canDo()->read) {
            $this->registerFile('vw_edit_urgence', TAB_EDIT);
        }

        if (CAppUI::conf("dPplanningOp CSejour new_dhe", $curr_group)) {
            $this->registerFile('vw_dhe', TAB_EDIT);
        }

        $this->registerFile(
            'vw_protocoles',
            CAppUI::conf("dPplanningOp CSejour tab_protocole_DHE_only_for_admin", $curr_group)
                ? TAB_ADMIN : TAB_EDIT,
            self::TAB_SETTINGS
        );
        $this->registerFile('vw_protocoles_op', TAB_ADMIN, self::TAB_SETTINGS);
        $this->registerFile('vw_edit_typeanesth', TAB_ADMIN, self::TAB_SETTINGS);
        $this->registerFile('vw_idx_colors', TAB_ADMIN, self::TAB_SETTINGS);
        $this->registerFile('vw_sectorisations', TAB_ADMIN, self::TAB_SETTINGS);
        $this->registerFile('vw_parametrage', TAB_ADMIN, self::TAB_SETTINGS);
        $this->registerFile('vw_positions', TAB_ADMIN, self::TAB_SETTINGS);
        $this->registerFile('vw_amplis', TAB_ADMIN, self::TAB_SETTINGS);
        $this->registerFile('vw_labos_anapath', TAB_ADMIN, self::TAB_SETTINGS);
        $this->registerFile('vw_labos_bacterio', TAB_ADMIN, self::TAB_SETTINGS);

        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
