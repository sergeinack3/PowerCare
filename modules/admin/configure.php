<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Admin\Services\AccountActivationService;

CCanDo::checkAdmin();

$activer_user_action  = CAppUI::conf("activer_user_action");
$reset_account_source = AccountActivationService::getSMTPSource();

$smarty = new CSmartyDP();
$smarty->assign('activer_user_action', $activer_user_action);
$smarty->assign('reset_account_source', $reset_account_source);
$smarty->display('configure.tpl');
