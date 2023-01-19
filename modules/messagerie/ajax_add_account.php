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
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourcePOP;
use Ox\Mediboard\System\CSourceSMTP;

CCanDo::checkRead();

CView::checkin();

$user = CMediusers::get();

$source_smtp = CExchangeSource::get("mediuser-".$user->_id, CSourceSMTP::TYPE, true, null, false);

$source_pop = new CSourcePOP();
$source_pop->object_class = $user->_class;
$source_pop->object_id    = $user->_id;
$source_pop->name = 'SourcePOP-' . $user->_id . '-' . ($source_pop->countMatchingList() + 1);

$mssante = false;
if (CModule::getActive('mssante') && CModule::getCanDo('mssante')->read) {
  $mssante = true;
}

$apicrypt = false;
if (CModule::getActive('apicrypt') && CModule::getCanDo('apicrypt')->read) {
  $apicrypt = true;
}

$medimail = false;
if (CModule::getActive('medimail') && CModule::getCanDo('medimail')->read()) {
  $medimail = true;
}

$smarty = new CSmartyDP();
$smarty->assign('source_smtp', $source_smtp);
$smarty->assign('source_pop', $source_pop);
$smarty->assign('mssante', $mssante);
$smarty->assign('apicrypt', $apicrypt);
$smarty->assign('medimail', $medimail);
$smarty->display('inc_add_account.tpl');
