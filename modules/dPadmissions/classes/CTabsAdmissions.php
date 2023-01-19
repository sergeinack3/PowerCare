<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admissions;

use Ox\Core\CAppUI;
use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsAdmissions extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        if (CAppUI::conf("dPplanningOp CSejour use_recuse")) {
            $this->registerFile("vw_sejours_validation", TAB_EDIT);
        }

        $this->registerFile("vw_idx_admission", TAB_READ);
        $this->registerFile("vw_idx_sortie", TAB_READ);
        $this->registerFile("vw_idx_preadmission", TAB_READ);
        $this->registerFile("vw_idx_permissions", TAB_READ);
        $this->registerFile("vw_idx_present", TAB_READ);
        $this->registerFile("vw_projet_sortie", TAB_READ);

        if (CAppUI::gconf("dPadmissions General view_sortie_masss")) {
            $this->registerFile("vw_sortie_masse", TAB_READ);
        }
        $this->registerFile("vw_accueil_patient", TAB_READ);

        $this->registerFile("vw_idx_identito_vigilance", TAB_ADMIN);

        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
