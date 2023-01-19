<?php

/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins;

use Ox\Core\CAppUI;
use Ox\Core\Module\AbstractTabsRegister;
use Ox\Core\Module\CModule;

/**
 * @codeCoverageIgnore
 */
class CTabsSoins extends AbstractTabsRegister
{

    public function registerAll(): void
    {
        $pref_vue_sejours             = CAppUI::pref("vue_sejours");
        $module_active_dPprescription = CModule::getActive('dPprescription');
        $module_active_planSoins      = CModule::getActive('planSoins');

        $vw_sejour = (CAppUI::pref("vue_sejours") == "standard") ? "viewIndexSejour" : "vwSejours";
        $this->registerFile($vw_sejour, TAB_READ);

        if ($pref_vue_sejours == "standard") {
            $this->registerFile('viewIndexSejour', TAB_READ);
        } else {
            $this->registerFile('vwSejours', TAB_READ);
        }

        if ($module_active_dPprescription && $module_active_planSoins) {
            $this->registerFile('vw_pancarte_service', TAB_READ);
        }

        if ($module_active_dPprescription) {
            $this->registerFile('vw_bilan_prescription', TAB_READ);
        }

        if ($module_active_dPprescription && $module_active_planSoins) {
            $this->registerFile('vw_plan_soins_service', TAB_READ);
        }

        if ($module_active_dPprescription && (CAppUI::gconf("soins Other show_charge_soins"))) {
            $this->registerFile('vw_ressources_soins', TAB_READ);
        }

        if (CModule::getActive("dispensation") &&
            CAppUI::gconf("dispensation general enable_v2") &&
            CAppUI::gconf("dispensation general show_dispensation_dossier_soins")) {
            $this->registerFile('vw_dispensation', TAB_READ);
        }

        if (CAppUI::gconf("soins dossier_soins manage_consult_reeducation")) {
            $this->registerFile('vw_sejours_reeducation', TAB_READ);
        }

        $this->registerFile('vw_affectations_soignant', TAB_READ);

        if (CModule::getActive('dPstock')) {
            $this->registerFile('vw_stocks_service', TAB_EDIT);
        }

        $this->registerFile('vw_categories_objectif_soin', TAB_EDIT, self::TAB_SETTINGS);
        $this->registerFile('vw_timings_affectation_sejour', TAB_ADMIN, self::TAB_SETTINGS);

        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
