<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Eai\CInteropSender;

/**
 * View interop actors EAI
 */
CCanDo::checkRead();
CView::checkin();

$receiver  = new CInteropReceiver();
$receivers = array();

$sender  = new CInteropSender();
$senders = array();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("receiver" , $receiver);
$smarty->assign("receivers", $receivers);

$smarty->assign("sender" , $sender);
$smarty->assign("senders", $senders);

$smarty->display("vw_idx_interop_actors.tpl");