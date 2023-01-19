<?php

/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\CAppUI;
use Ox\Core\Module\AbstractTabsRegister;
use Ox\Core\Module\CModule;

/**
 * @codeCoverageIgnore
 */
class CTabsSsr extends AbstractTabsRegister
{

    public function registerAll(): void
    {
        $use_acte_presta         = CAppUI::gconf("ssr general use_acte_presta");
        $module_active_plansoins = CModule::getActive("planSoins");

        if (CAppUI::conf("ssr recusation use_recuse")) {
            $this->registerFile('vw_sejours_validation', TAB_EDIT);
        }

        $this->registerFile('vw_sejours_ssr', TAB_READ);
        $this->registerFile('vw_aed_sejour_ssr', TAB_READ);
        $this->registerFile('vw_kine_board', TAB_EDIT);
        $this->registerFile('vw_planning_collectif', TAB_EDIT);

        if ($use_acte_presta == 'csarr') {
            $this->registerFile('vw_groupes_patient', TAB_EDIT);
        }

        $this->registerFile('vw_idx_repartition', TAB_READ);
        $this->registerFile('vw_plateau_board', TAB_READ);

        if ($module_active_plansoins) {
            $this->registerFile('vw_activite_ssr', TAB_READ);
        }

        $this->registerFile('vw_aed_replacement', TAB_ADMIN);
        $this->registerFile('vw_cdarr', TAB_READ);

        if ($use_acte_presta == 'csarr') {
            $this->registerFile('vw_csarr', TAB_READ);
        } elseif ($use_acte_presta == 'presta') {
            $this->registerFile('vw_cpresta_ssr', TAB_READ);
        }

        $this->registerFile('vw_stats', TAB_ADMIN);

        if ($use_acte_presta != 'aucun') {
            $this->registerFile('vw_facturation_rhs', TAB_EDIT);
        }

        $this->registerFile('vw_idx_plateau', TAB_ADMIN, self::TAB_SETTINGS);
        $this->registerFile('edit_codes_intervenants', TAB_ADMIN, self::TAB_SETTINGS);

        if ($use_acte_presta == 'presta') {
            $this->registerFile('vw_functions', TAB_ADMIN, self::TAB_SETTINGS);
        }

        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
