<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

global $m, $current_m;

if (!isset($current_m)) {
  $current_m = CValue::get("current_m", $m);
}

// Filtres d'affichage

$recuse     = CValue::getOrSession("recuse", "-1");
$envoi_mail = CValue::getOrSession("envoi_mail", "0");
$order_way  = CValue::getOrSession("order_way", "ASC");
$order_col  = CValue::getOrSession("order_col", "patient_id");
$date       = CValue::getOrSession("date", CMbDT::date());
$service_id = CValue::getOrSession("service_id");
$prat_id    = CValue::getOrSession("prat_id");

$date_actuelle = CMbDT::dateTime("00:00:00");
$date_demain   = CMbDT::dateTime("00:00:00", "+ 1 day");
$hier          = CMbDT::date("- 1 day", $date);
$demain        = CMbDT::date("+ 1 day", $date);

// Récupération de la liste des services
$where = array();
$where["externe"]   = "= '0'";
$where["cancelled"] = "= '0'";
$service = new CService();
$services = $service->loadGroupList($where);

// Récupération de la liste des praticiens
$prat = CMediusers::get();
$prats = $prat->loadPraticiens();

$sejour = new CSejour();
if ($current_m == "ssr" || $current_m == "psy") {
  $sejour->_type_admission = $current_m;
}
$sejour->service_id      = $service_id;
$sejour->praticien_id    = $prat_id;

// Liste des séjours en attente de validation
$g = CGroups::loadCurrent()->_id;
$where = array();
$where["group_id"] = "= '$g'";
$where["recuse"]   = "= '-1'";
$where["annule"]   = "= '0'";
if ($current_m == "ssr" || $current_m == "psy") {
  $where["type"]     = "= '$current_m'";
}
$where["entree"]   = ">= '".CMbDT::date()."'";
$nb_sejours_attente = $sejour->countList($where);

// Création du template
$smarty = new CSmartyDP("modules/dPadmissions");

$smarty->assign("current_m"         , $current_m);
$smarty->assign("sejour"            , $sejour);
$smarty->assign("date_demain"       , $date_demain);
$smarty->assign("date_actuelle"     , $date_actuelle);
$smarty->assign("date"              , $date);
$smarty->assign("recuse"            , $recuse);
$smarty->assign("envoi_mail"        , $envoi_mail);
$smarty->assign("order_way"         , $order_way);
$smarty->assign("order_col"         , $order_col);
$smarty->assign("services"          , $services);
$smarty->assign("prats"             , $prats);
$smarty->assign("hier"              , $hier);
$smarty->assign("demain"            , $demain);
$smarty->assign("nb_sejours_attente", $nb_sejours_attente);

$smarty->display("vw_sejours_validation");
