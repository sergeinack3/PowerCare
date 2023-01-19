<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

use Ox\Core\CAppUI;
use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsDeveloppement extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $this->registerFile('view_logs', TAB_READ);
        $this->registerFile('view_metrique', TAB_READ);
        $this->registerFile('vw_db_checks', TAB_READ);
        $this->registerFile('vw_integrity', TAB_READ);
        $this->registerFile('vw_audit', TAB_READ);

        if (CAppUI::conf("debug")) {
            $this->registerFile('vw_translations', TAB_EDIT);
        } else {
            $this->registerFile('displayTranslations', TAB_EDIT);
        }

        $this->registerFile('vw_create_module', TAB_EDIT);
        $this->registerFile('view_server_config', TAB_READ);
        $this->registerFile('view_data_model', TAB_READ);

        if (CAppUI::conf("dPdeveloppement external_repository_path")) {
            $this->registerFile('vw_external_components', TAB_ADMIN);
        }

        $this->registerFile('vw_tests', TAB_READ);
        $this->registerFile('vw_class', TAB_READ);
        $this->registerFile('vw_routes', TAB_READ);
//        $this->registerFile('vw_kernel', TAB_READ);

        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
