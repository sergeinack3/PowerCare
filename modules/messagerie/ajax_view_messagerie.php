<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$account_guid = CView::get('account_guid', 'str notNull');

CView::checkin();

$account = null;
if ($account_guid != 'internal') {
  $account = CMbObject::loadFromGuid($account_guid);
}

$smarty = new CSmartyDP();
$smarty->assign('account_guid', $account_guid);
$smarty->assign('account', $account);
$smarty->assign('user', CMediusers::get());
$smarty->display('inc_messagerie_modal.tpl');