<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Urgences\CExtractPassages;

CCanDo::checkRead();

$page            = CValue::get('page', 0);
$date_min        = CValue::get("_date_min", CMbDT::dateTime("-7 day"));
$date_max        = CValue::get("_date_max", CMbDT::dateTime("+1 day"));
$debut_selection = CValue::get("debut_selection");
$fin_selection   = CValue::get("fin_selection");
$type            = CValue::get("type");
$has_send_datetime = CValue::get("has_send_datetime", "bool");

$where             = array();
$where["group_id"] = " = '" . CGroups::loadCurrent()->_id . "'";

if ($date_min) {
  $where["date_extract"] = " >= '$date_min'";
}
if ($date_max) {
  $where["date_extract"] = " <= '$date_max'";
}
if ($debut_selection) {
  $where["debut_selection"] = " >= '$debut_selection'";
}
if ($fin_selection) {
  $where["fin_selection"] = " <= '$fin_selection'";
}
if ($type) {
  $where["type"] = " = '$type'";
}

if ($has_send_datetime === "0") {
    $where['date_echange'] = "IS NULL";
} elseif ($has_send_datetime) {
    $where['date_echange'] = "IS NOT NULL";
}

$order = "date_extract DESC";

$extractPassages = new CExtractPassages();
$total_passages  = $extractPassages->countList($where);
/** @var CExtractPassages[] $listPassages */
$listPassages = $extractPassages->loadList($where, $order, "$page, 20");

$total_rpus = 0;
foreach ($listPassages as $_passage) {
  $_passage->countDocItems();
  $_passage->loadRefsFiles();

  $total_rpus += $_passage->_nb_rpus;
}

// Création du template
$smarty = new CSmartyDP("modules/dPurgences");
$smarty->assign("extractPassages", $extractPassages);
$smarty->assign("listPassages", $listPassages);

$smarty->assign("page", $page);
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign("type", $type);

$smarty->assign("total_passages", $total_passages);

$smarty->assign("total_rpus", $total_rpus);

$smarty->display("inc_extract_passages");
