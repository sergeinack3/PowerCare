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
use Ox\Mediboard\Files\CFilesCategory;

CCanDo::checkEdit();
$files_category_id = CView::get("files_category_id", "ref class|CFilesCategory");
$refresh           = CView::get("refresh"          , "bool default|0");
CView::checkin();

$files_category = new CFilesCategory();
$files_category->load($files_category_id);
$related_receivers = $files_category->loadRefRelatedReceivers();

foreach ($related_receivers as $_related_receiver) {
  $_related_receiver->loadRefReceiver();
}

$smarty = new CSmartyDP();
$smarty->assign("files_category"   , $files_category);
$smarty->assign("related_receivers", $related_receivers);
$smarty->display($refresh ? "inc_vw_related_receivers_list" : "inc_vw_related_receivers");