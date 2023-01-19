<?php

namespace Ox\Mediboard\Admin;

use Ox\Core\CAppUI;
use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsAdmin extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $this->registerFile("vw_edit_users", TAB_EDIT);
        $this->registerFile("viewAllPerms", TAB_READ);
        $this->registerFile("vw_edit_tokens", TAB_EDIT);
        $this->registerFile("vw_users_auth", TAB_EDIT);

        if (CAppUI::gconf('admin CBrisDeGlace enable_bris_de_glace')) {
            $this->registerFile("vw_bris_de_glace", TAB_ADMIN);
        }

        if (CAppUI::gconf('admin CLogAccessMedicalData enable_log_access')) {
            $this->registerFile("vw_access_history", TAB_ADMIN);
        }

        $this->registerFile('vw_rgpd', TAB_ADMIN);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
