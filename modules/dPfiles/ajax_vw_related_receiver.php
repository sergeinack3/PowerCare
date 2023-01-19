<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Files\CFilesCategoryToReceiver;

CCanDo::checkEdit();
$related_receiver_id = CView::get("related_receiver_id", "ref class|CFilesCategoryToReceiver");
CView::checkin();

$related_receiver = new CFilesCategoryToReceiver();
$related_receiver->load($related_receiver_id);
$files_category = $related_receiver->loadRefFilesCategory();
$related_receiver->loadRefReceiver();

$smarty = new CSmartyDP();
$smarty->assign("_related_receiver", $related_receiver);
$smarty->assign("files_category"   , $files_category);
$smarty->display("inc_vw_related_receiver");