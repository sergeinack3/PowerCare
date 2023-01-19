<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Labo;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsLabo extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $this->registerFile('vw_edit_prescriptions', TAB_READ);
        $this->registerFile('vw_resultats', TAB_READ);
        $this->registerFile('add_pack_exams', TAB_READ);

        $this->registerFile('vw_edit_packs', TAB_EDIT, self::TAB_SETTINGS);
        $this->registerFile('vw_edit_catalogues', TAB_EDIT, self::TAB_SETTINGS);
        $this->registerFile('vw_edit_examens', TAB_EDIT, self::TAB_SETTINGS);
        $this->registerFile('vw_edit_idLabo', TAB_EDIT, self::TAB_SETTINGS);

        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
