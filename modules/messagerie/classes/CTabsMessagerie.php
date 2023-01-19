<?php

/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Ox\Core\CAppUI;
use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsMessagerie extends AbstractTabsRegister
{

    public function registerAll(): void
    {
        $this->registerFile('vw_messagerie', TAB_READ);
        $this->registerFile('vw_list_accounts', TAB_ADMIN);

        if (CAppUI::gconf("messagerie access allow_internal_mail")) {
            $this->registerFile('vw_user_message_dest_groups', TAB_ADMIN, self::TAB_SETTINGS);
        }

        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
