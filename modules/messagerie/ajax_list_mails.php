<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\CUserMail;
use Ox\Mediboard\Messagerie\CUserMailFolder;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourcePOP;
use Ox\Mediboard\System\CSourceSMTP;

CCanDo::checkRead();

$account_id    = CView::get("account_id", 'ref class|CSourcePOP');
$query         = CView::get('search', 'str');
$query_options = json_decode(stripslashes(CView::get('query_options', 'str default|[]')), true);
$folder        = CView::get("folder", "str default|inbox");
$page          = CView::get("page", 'num default|0');
$display_all   = CView::get('display_all', 'bool default|0');

CView::checkin();

//user connected
$user = CMediusers::get();

$limit_list   = CAppUI::pref("nbMailList", 20);

//account POP
$account_pop = new CSourcePOP();
$account_pop->load($account_id);

$account_smtp = CExchangeSource::get('mediuser-' . CMediusers::get()->_id, CSourceSMTP::TYPE);

if (($account_pop->object_id != $user->_id) && $account_pop->is_private) {
  CAppUI::stepAjax("CSourcePOP-error-private_account", UI_MSG_ERROR);
}

if (!$account_pop->_id) {
  $where                 = array();
  $where["object_class"] = " = 'CMediusers'";
  $where["object_id"]    = " = '$user->_id'";
  $account_pop->loadObject($where);
}

$where = array();
//mails
$mail = new CUserMail();

if (in_array($folder, CUserMailFolder::$types)) {
  $type = $folder;
}
else {
  /** @var CUserMailFolder $folder */
  $folder = CUserMailFolder::loadFromGuid($folder);
  $type = $folder->type;
}

$results = CUserMail::search($account_pop->_id, $folder, $query, $query_options, "$page, $limit_list");

/** @var CUserMail[] $mails */
foreach ($results['mails'] as $_mail) {
  $_mail->loadReadableHeader();
  $_mail->loadRefsFwd();
  $_mail->checkApicrypt();
}

//smarty
$smarty = new CSmartyDP();
$smarty->assign("mails", $results['mails']);
$smarty->assign("page", $page);
$smarty->assign("nb_mails", $results['count']);
$smarty->assign("account_id", $account_id);
$smarty->assign("user", $user);
$smarty->assign("folder", $folder);
$smarty->assign('type', $type);
$smarty->assign('query_options', $query_options);
$smarty->assign('query', $query);
$smarty->assign("account_pop", $account_pop);
$smarty->assign('account_smtp', $account_smtp);
$smarty->display("inc_list_mails.tpl");
