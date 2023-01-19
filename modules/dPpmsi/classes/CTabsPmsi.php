<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Pmsi;

use Ox\Core\CAppUI;
use Ox\Core\Module\AbstractTabsRegister;
use Ox\Core\Module\CModule;

/**
 * @codeCoverageIgnore
 */
class CTabsPmsi extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        if (CAppUI::gconf("dPpmsi display see_recept_dossier")) {
            $this->registerFile("vw_recept_dossiers", TAB_READ);
            $this->registerFile('vw_reception_multiple', TAB_READ);
        }

        $this->registerFile("vw_dossier_pmsi", TAB_EDIT);
        $this->registerFile("vw_current_dossiers", TAB_READ);
        $this->registerFile("vw_relances", TAB_EDIT);
        $this->registerFile('vw_cotations', TAB_READ);
        $this->registerFile('vw_cotations_ngap', TAB_READ);
        $this->registerFile("vw_print_planning", TAB_READ);

        if (CModule::getActive("atih")) {
            $this->registerFile("vw_traitement_dossiers", TAB_EDIT);
            $this->registerFile('vw_ghs_explorer', TAB_READ);
        }

        $this->registerFile('vw_cim10_explorer', TAB_READ);

        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}







