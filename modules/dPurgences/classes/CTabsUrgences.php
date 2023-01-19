<?php

/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Urgences;

use Ox\Core\CAppUI;
use Ox\Core\Module\AbstractTabsRegister;
use Ox\Core\Module\CModule;

/**
 * @codeCoverageIgnore
 */
class CTabsUrgences extends AbstractTabsRegister
{

    public function registerAll(): void
    {
        $this->registerFile('vw_idx_rpu', TAB_READ);

        if (CAppUI::gconf("dPhospi vue_topologique use_vue_topologique")) {
            $this->registerFile('patientsPlacementView', TAB_READ);
        }

        $this->registerFile('edit_consultation', TAB_EDIT);
        $this->registerFile('vw_sortie_rpu', TAB_READ);
        $this->registerFile('vw_attente', TAB_READ);

        if (CModule::getActive("dPstock")) {
            $this->registerFile('vw_stock_order', TAB_READ);
        }

        $this->registerFile('dashboard', TAB_READ);

        $this->registerFile('vw_stats', TAB_ADMIN);
        $this->registerFile('vw_bilan_cotations', TAB_ADMIN);

        $this->registerFile('vw_protocoles', TAB_EDIT, self::TAB_SETTINGS);
        $this->registerFile('vw_circonstances', TAB_ADMIN, self::TAB_SETTINGS);
        $this->registerFile('vw_categories', TAB_ADMIN, self::TAB_SETTINGS);
        $this->registerFile('infosServiceTypes', TAB_ADMIN, self::TAB_SETTINGS);

        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
