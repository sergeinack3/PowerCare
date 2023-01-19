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
use Ox\Mediboard\Files\CFilesCategoryToReceiver;

CCanDo::checkEdit();
$related_receiver_id = CView::get("related_receiver_id", "ref class|CFilesCategoryToReceiver");
$files_category_id   = CView::get("files_category_id"   , "ref class|CFilesCategory");
CView::checkin();

$related_receiver = new CFilesCategoryToReceiver();
$related_receiver->load($related_receiver_id);
$related_receiver->loadRefFilesCategory();
$related_receiver->loadRefReceiver();

$selected_category = new CFilesCategory();
$selected_category->load($files_category_id);

$available_receivers = CFilesCategoryToReceiver::getAvailableReceivers(
  null,
  $selected_category->group_id ? $selected_category->group_id : null,
  true
);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("related_receiver"   , $related_receiver);
$smarty->assign("selected_category"  , $selected_category);
$smarty->assign("available_receivers", $available_receivers);
$smarty->display("inc_edit_related_receiver_to_category.tpl");
