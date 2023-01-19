<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Medimail\CMedimailAccount;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mssante\CMSSanteUserAccount;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourcePOP;
use Ox\Mediboard\System\CSourceSMTP;

CCanDo::checkEdit();

CView::checkin();

$user = CMediusers::get();

$source_smtp = CSourceSMTP::get("mediuser-$user->_id", 'smtp', true, null, false);
$sources_smtp = array();
if ($source_smtp->_id) {
  $sources_smtp[] = $source_smtp;
}

$sources_pop = new CSourcePOP();
$where["source_pop.object_class"] = "= 'CMediusers'";
$where["source_pop.object_id"] = " = '$user->_id'";
$where['source_pop.name'] = " NOT LIKE '%apicrypt'";
$sources_pop = $sources_pop->loadList($where);

if (CModule::getActive('mssante') && CModule::getCanDo('mssante')->read) {
  $mssante_account = CMSSanteUserAccount::getAccountForCurrentUser();
}
else {
  $mssante_account = false;
}

if (CModule::getActive('apicrypt') && CModule::getCanDo('apicrypt')->read) {
  $apicrypt_account = CExchangeSource::get("mediuser-$user->_id-apicrypt", CSourceSMTP::TYPE, true, null, false);
}
else {
  $apicrypt_account = false;
}

$medimail_account = false;
if (CModule::getActive('medimail') && CModule::getCanDo('medimail')->read) {
  $medimail_account = CMedimailAccount::getAccountFor($user);
}

$smarty = new CSmartyDP();
$smarty->assign('sources_smtp', $sources_smtp);
$smarty->assign('sources_pop', $sources_pop);
$smarty->assign('mssante_account', $mssante_account);
$smarty->assign('apicrypt_account', $apicrypt_account);
$smarty->assign('medimail_account', $medimail_account);
$smarty->display('inc_manage_accounts.tpl');
