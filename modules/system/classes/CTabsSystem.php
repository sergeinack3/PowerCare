<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * Tabs for module system
 */
/**
 * @codeCoverageIgnore
 */
class CTabsSystem extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $this->registerFile("view_modules", TAB_ADMIN);
        $this->registerFile("view_cache", TAB_ADMIN);
        $this->registerFile("idx_messages", TAB_READ);
        $this->registerFile("object_merger", TAB_ADMIN);
        $this->registerFile("view_history", TAB_READ);
        $this->registerFile("vw_monitoring", TAB_ADMIN);
        $this->registerFile("view_translations", TAB_EDIT);
        $this->registerFile("idx_view_senders", TAB_EDIT);
        $this->registerFile('vw_cronjob', TAB_ADMIN);
        $this->registerFile("vw_config_info", TAB_ADMIN);
        $this->registerFile("viewDatasources", TAB_ADMIN);
        $this->registerFile("viewKeysMetadata", TAB_ADMIN);
        $this->registerFile("about", TAB_READ);
        $this->registerFile("configure", TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
