<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;

CCanDo::checkAdmin();

$start = CView::get("start", "num");

CView::checkin();

$cr = new CCompteRendu();

$where = array(
  "object_id" => "IS NOT NULL",
);

$count = 100;
$crs = $cr->loadList($where, null, "$start,$count");

CStoredObject::massLoadBackRefs($crs, "files");

$messages = array();
/** @var CCompteRendu $_cr */
foreach ($crs as $_cr) {
  $_cr->factory = 'CWkHtmlToPDFConverter';
  $msg = $_cr->makePDFpreview();

  if ($msg) {
    $messages[$_cr->_id] = $msg;
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("messages"  , $messages);
$smarty->assign("keep_going", count($crs) === $count);
$smarty->assign("count", $count);

$smarty->display("inc_regenerate_files");