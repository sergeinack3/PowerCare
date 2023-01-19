<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Messagerie\CDocumentExterne;

CCanDo::checkRead();

$class      = CView::get("class", 'str');
$mode       = CView::get("mode", "str default|unlinked");
$page       = CView::get("start", 'bool default|0');
$account_id = CView::get("account_id", 'num'); // Can't check ref class

CView::checkin();

$iteration = 30;

/** @var CDocumentExterne $doc */
$doc = new $class();
$doc->account_id = $account_id;
$account = $doc->loadRefAccount();


$nb_total_unlinked = $doc->count_document_total("unlinked");
$nb_documents = $doc->count_document_total($mode) ? $doc->count_document_total($mode) : 0;
/** @var CDocumentExterne[] $documents */
$documents = $doc->get_document_list($mode, $page, $iteration);

foreach ($documents as $_doc) {
  $_doc->loadRefFile(true);
}

$smarty = new CSmartyDP("modules/messagerie");
$smarty->assign("nb_total_documents", $nb_documents);
$smarty->assign("nb_unlinked", $nb_total_unlinked);
$smarty->assign("account_id", $account_id);
$smarty->assign("mode", $mode);
$smarty->assign("_status", CDocumentExterne::$_status_available);
$smarty->assign("documents", $documents);
$smarty->assign("doc", $doc);
$smarty->assign("page", $page);
$smarty->assign("iteration", $iteration);
$smarty->display("inc_list_external_documents.tpl");