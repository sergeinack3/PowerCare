<?php

/**
 * @package Mediboard\Maidis
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Maidis;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsMaidis extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $this->registerFile('vw_users_fw', TAB_EDIT);
        $this->registerFile('vw_import_fw', TAB_EDIT);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
