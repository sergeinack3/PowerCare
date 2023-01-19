<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CViewAccessToken;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$token_id = CView::get('token_id', 'ref class|CViewAccessToken', true);

CView::checkin();

$token = new CViewAccessToken();
$token->load($token_id);

if (!$token->_id) {
  $token->datetime_start = CMbDT::dateTime();
  $token->user_id        = CMediusers::get()->_id;
  $token->setDefaultHashLength();
}

$token->loadRefsNotes();
$token->loadRefUser();
$token->getUrl();

$token->getValidators();

$smarty = new CSmartyDP();
$smarty->assign('token', $token);
$smarty->display('inc_edit_token');
