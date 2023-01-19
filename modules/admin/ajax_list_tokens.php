<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Admin\CViewAccessToken;
use Ox\Mediboard\System\CModuleAction;

CCanDo::checkEdit();

$min_validity_date = CView::get("_min_validity_date", "dateTime", true);
$max_validity_date = CView::get("_max_validity_date", "dateTime", true);
$min_usage_date    = CView::get("_min_usage_date", "dateTime", true);
$max_usage_date    = CView::get("_max_usage_date", "dateTime", true);
$module            = CView::get("filter_module", "str");
$module_action_id  = CView::get("module_action_id", "ref class|CModuleAction");
$user_id           = CView::get("user_id", "ref class|CUser");
$hash              = CView::get("hash", "str");
$start             = CView::get("start", "num");
$limit             = CView::get("limit", "num default|50");
$actif             = CView::get("actif", "bool", true);
$purgeable         = CView::get("purgeable", "bool");
$restricted        = CView::get("restricted", "bool");
$export            = CView::get("export", "bool");

CView::checkin();

$token = new CViewAccessToken();
$ds    = $token->getDS();

$where = array();

if ($min_validity_date) {
  $where['datetime_start'] = $ds->prepare('>= ?', $min_validity_date);
}

if ($max_validity_date) {
  $where['datetime_end'] = $ds->prepare('<= ?', $max_validity_date);
}

if ($min_usage_date) {
  $where['first_use'] = $ds->prepare('>= ?', $min_usage_date);
}

if ($max_usage_date) {
  $where['latest_use'] = $ds->prepare('<= ?', $max_usage_date);
}

if ($module_action_id) {
  $where['module_action_id'] = $ds->prepare("= ?", $module_action_id);
}
elseif ($module) {
  $module_action         = new CModuleAction();
  $module_action->module = $module;

  $module_actions = $module_action->loadMatchingListEsc();
  if ($module_actions) {
    $where['module_action_id'] = $ds->prepareIn(CMbArray::pluck($module_actions, '_id'));
  }
}

if ($user_id) {
  $where['user_id'] = $ds->prepare("= ?", $user_id);
}

if ($hash) {
  $where['hash'] = $ds->prepare("= ?", $hash);
}

if ($purgeable === '1' || $purgeable === '0') {
  $where['purgeable'] = $ds->prepare('= ?', $purgeable);
}

if ($restricted === '1' || $restricted === '0') {
  $where['restricted'] = $ds->prepare('= ?', $restricted);
}

// Load all tokens then array_filter for active ones
$lim = ($export || $actif !== '') ? null : "$start, $limit";

$tokens = $token->loadList($where, null, $lim);

if ($actif !== '') {
  $tokens = array_filter(
    $tokens,
    function ($elt) use ($actif) {
      return ($actif) ? $elt->isValid() : !$elt->isValid();
    }
  );

  $total = count($tokens);
}
else {
  $total = $token->countList($where);
}

$mas = CStoredObject::massLoadFwdRef($tokens, "module_action_id");

if ($export) {
  ob_end_clean();
  header("Content-Type: text/plain;charset=".CApp::$encoding);
  header("Content-Disposition: attachment;filename=\"jetons_acces.csv\"");

  $fp = fopen("php://output", "w");
  $csv = new CCSVFile($fp);

  $csv = CViewAccessToken::prepareCSV($csv, $tokens);
  $csv->close();
  CApp::rip();
}

CStoredObject::massCountBackRefs($tokens, 'jobs');

CStoredObject::massLoadFwdRef($tokens, 'user_id');

$smarty = new CSmartyDP();
$smarty->assign("tokens", $tokens);
$smarty->assign("total", $total);
$smarty->assign("limit", $limit);
$smarty->assign("start", $start);

$smarty->display("inc_list_tokens");
