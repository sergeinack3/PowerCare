<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesCategory;

CCanDo::checkEdit();

$do = CValue::get("do", "0");

// Auto send categories
$category = new CFilesCategory();
$category->send_auto = "1";
foreach ($categories = $category->loadMatchingList() as $_category) {
  $_category->countDocItems();
  $_category->countUnsentDocItems();
}

// Unsent docItems
$max_load = CAppUI::gconf("dPfiles CDocumentSender auto_max_load");
$where["file_category_id"] = CSQLDataSource::prepareIn(array_keys($categories));
$where["etat_envoi"      ] = "!= 'oui'";
$where["object_id"       ] = "IS NOT NULL";

$file = new CFile();
$items["CFile"] = $file->loadList($where, "file_id DESC", $max_load);
$count["CFile"] = $file->countList($where);

$document = new CCompteRendu();
$items["CCompteRendu"] = $document->loadList($where, "compte_rendu_id DESC", $max_load);
$count["CCompteRendu"] = $document->countList($where);

// Sending
$max_send = CAppUI::gconf("dPfiles CDocumentSender auto_max_send");
foreach ($items as $_items) {
  $sent = 0;

  /** @var CDocumentItem[] $_items */
  foreach ($_items as $_item) {
    $_item->loadTargetObject();
    if ($do && !$_item->_send_problem) {
      // Max sent
      if (++$sent > $max_send) {
        break;
      }

      $_item->_send = "1";
      $_item->_send_problem = $_item->store();

      // To track whether sending has been tried
      $_item->_send = "1";

    }
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("do", $do);
$smarty->assign("categories", $categories);
$smarty->assign("items", $items);
$smarty->assign("count", $count);
$smarty->assign("max_load", $max_load);
$smarty->assign("max_send", $max_send);

$smarty->display("send_documents.tpl");
