<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\CAppUI;
use Ox\Core\Module\AbstractTabsRegister;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * @codeCoverageIgnore
 */
class CTabsPatients extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $this->registerFile("vw_idx_patients", TAB_READ);
        $this->registerFile("vw_full_patients", TAB_READ);
        $this->registerFile("vw_edit_patients", TAB_EDIT);
        $this->registerFile("vw_correspondants", TAB_EDIT);
        $this->registerFile("vw_recherche_dossier_clinique", TAB_READ);
        $this->registerFile("vw_recherche_doc", TAB_READ);

        if ((CAppUI::$user instanceof CMediusers) && (CAppUI::$user->_user_type == 0)) {
            $this->registerFile('vw_identito_vigilance', TAB_ADMIN);
        }

        $this->registerFile('indexIdentityProofTypes', TAB_ADMIN, self::TAB_SETTINGS);
        $this->registerFile('vwExportPatients', TAB_ADMIN, self::TAB_SETTINGS);

        if (CAppUI::pref("allowed_modify_identity_status")) {
            $this->registerFile('vw_patient_state', TAB_EDIT);
        }

        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
