<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsFiles extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $this->registerFile("vw_files", TAB_READ);
        $this->registerFile("vw_files_explorer", TAB_ADMIN);
        $this->registerFile("send_documents", TAB_EDIT);
        $this->registerFile("vwStats", TAB_ADMIN);
        $this->registerFile("vw_import", TAB_ADMIN);
        $this->registerFile("vw_upload", TAB_ADMIN);
        $this->registerFile("vw_upload", TAB_ADMIN);

        $this->registerFile("vw_categories", TAB_ADMIN, self::TAB_SETTINGS);

        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
