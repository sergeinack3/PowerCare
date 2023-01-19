<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CClassMap;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Eai\CExchangeAny;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CMovement;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CChargePriceIndicator;

/**
 * View tools EAI
 */
CCanDo::checkAdmin();

$date_min = CView::get('date_min', array('dateTime', 'default' => CMbDT::dateTime("-7 day")));
$date_max = CView::get('date_max', array('dateTime', 'default' => CMbDT::dateTime("+1 day")));
CView::checkin();

$group_id = CGroups::loadCurrent()->_id;
$exchanges_classes = array();
foreach (CExchangeDataFormat::getAll(CExchangeDataFormat::class, false) as $key => $_exchange_class) {
  foreach (CApp::getChildClasses($_exchange_class, true, true) as $under_key => $_under_class) {
    $class = new $_under_class;
    $class->countExchangesDF();
    $exchanges_classes[CClassMap::getSN($_exchange_class)][] = $class;
  }
  if ($_exchange_class == "CExchangeAny") {
    $class = new CExchangeAny();
    $class->countExchangesDF();
    $exchanges_classes["CExchangeAny"][] = $class;
  }
}

$group = new CGroups();
$groups = $group->loadList();
foreach ($groups as $_group) {
  $_group->loadConfigValues();
}

$receiver = new CInteropReceiver();
$receivers = $receiver->getObjects(true, $group_id);
foreach ($receivers as $key => $_receivers) {
  if (empty($_receivers)) {
    unset($receivers[$key]);
  }
}

$tools = array(
  "exchanges" => array(
    "send",
    "inject_master_idex_missing",
    "reprocessing",
    "detect_collision",
  ),
  "smp"       => array(
    "resend_exchange",
    "generate_all_movement",
  ),
);

$mode_traitement = new CChargePriceIndicator();
$mode_traitement->group_id = $group_id;
$modes_traitement = $mode_traitement->loadMatchingList();

$mediuser = new CMediusers();
$mediusers = $mediuser->loadPraticiens();
foreach ($mediusers as $_mediuser) {
  $_mediuser->loadRefFunction();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("exchange", new CExchangeDataFormat());
$smarty->assign("movement", new CMovement());
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign("groups"  , $groups);
$smarty->assign("tools"   , $tools);
$smarty->assign("exchanges_classes", $exchanges_classes);
$smarty->assign("receivers"        , $receivers);
$smarty->assign("modes_traitement" , $modes_traitement);
$smarty->assign("mediusers"        , $mediusers);
$smarty->display("vw_tools.tpl");

