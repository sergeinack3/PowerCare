<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CViewAccessToken;

CCanDo::checkRead();
$token_ids = CView::get("token_ids", "str");
CView::checkin();

$token = new CViewAccessToken();
$tokens = $token->loadAll(explode("-", $token_ids));

$smarty = new CSmartyDP();
$smarty->assign("tokens", $tokens);
$smarty->display("cronify_tokens.tpl");
