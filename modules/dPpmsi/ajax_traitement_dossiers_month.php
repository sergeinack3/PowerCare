<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$filterFunction = CView::get("filterFunction", "str");
$type           = CView::get("type", "enum list|ambucomp|ambucompssr|comp|ambu|exte|seances|ssr|psy|urg|consult default|ambu");
$service_id     = CView::get("service_id", "ref class|CService");
$prat_id        = CView::get("prat_id", "ref class|CMediusers");
$order_way      = CView::get("order_way", "enum list|ASC|DESC default|ASC");
$order_col      = CView::get("order_col", "str default|patient_id");
$tri_recept     = CView::get("tri_recept", "str");
$tri_complet    = CView::get("tri_complet", "str");
$date           = CView::get("date", "date default|now", true);
CView::checkin();

$service_id     = explode(",", $service_id);
CMbArray::removeValue("", $service_id);

$month_min  = CMbDT::date("first day of +0 month", $date);
$lastmonth  = CMbDT::date("last day of -1 month" , $date);
$nextmonth  = CMbDT::date("first day of +1 month", $date);
$bank_holidays = CMbDT::getHolidays($date);

$group = CGroups::loadCurrent();
$where = array();
$leftjoin = array();
// Initialisation du tableau de jours
$days = array();
for ($day = $month_min; $day < $nextmonth; $day = CMbDT::date("+1 DAY", $day)) {
  $days[$day] = array(
    "sortie" => "0",
    "traitement" => "0",
    "complet" => "0",
  );
}

// filtre sur les types d'admission
if ($type == "ambucomp") {
  $where["sejour.type"] = "= 'ambu' OR `sejour`.`type` = 'comp'";
}
elseif ($type == "ambucompssr") {
  $where["sejour.type"] = "= 'ambu' OR `sejour`.`type` = 'comp' OR `sejour`.`type` = 'ssr'";
}
elseif ($type) {
  $where["sejour.type"] = " = '$type'";
}
else {
  $where["sejour.type"] = CSQLDataSource::prepareNotIn(CSejour::getTypesSejoursUrgence()) . " AND `sejour`.`type` != 'seances'";
}

$ds = CSQLDataSource::get("std");

// filtre sur les services
if (count($service_id)) {
  $leftjoin["affectation"] = " ON affectation.sejour_id = sejour.sejour_id AND affectation.sortie = sejour.sortie";
  $leftjoin["lit"] = " ON affectation.lit_id = lit.lit_id";
  $leftjoin["chambre"] = " ON lit.chambre_id = chambre.chambre_id";
  $leftjoin["service"] = " ON chambre.service_id = service.service_id";

  $where["sejour.service_id"] = $ds->prepareIn($service_id)." OR affectation.service_id ". $ds->prepareIn($service_id);
}

// filtre sur le praticien
if ($prat_id) {
  $where["sejour.praticien_id"] = " = '$prat_id'";
}

$month_min  = CMbDT::dateTime(null, $month_min);
$nextmonth  = CMbDT::dateTime(null, $nextmonth);

// Liste des sorties par jour
$request = new CRequest();
$request->addSelect(array("DATE_FORMAT(sejour.sortie_reelle, '%Y-%m-%d') AS 'date'", "COUNT(sejour.sejour_id) AS 'num'"));
$request->addTable("sejour");
$where["sejour.sortie_reelle"] = "BETWEEN '$month_min' AND '$nextmonth'";
$where["sejour.group_id"] = " = '$group->_id'";
$where["sejour.annule"] = " = '0'";
$request->addWhere($where);
$request->addLJoin($leftjoin);
$request->addGroup("date");
$request->addOrder("date");

foreach ($ds->loadHashList($request->makeSelect()) as $day => $_sortie) {
  $days[$day]["sortie"] = $_sortie;
}

// Liste des sorties dont le dossier n'a pas été reçu
$leftjoin["traitement_dossier"] = "traitement_dossier.sejour_id = sejour.sejour_id";
$where["traitement_dossier.traitement"] = " IS NOT NULL";
$request->addWhere($where);
$request->addLJoin($leftjoin);
foreach ($ds->loadHashList($request->makeSelect()) as $day => $_traitement) {
  $days[$day]["traitement"] = $_traitement;
}

// Liste des sorties dont le dossier est traité
unset($where['traitement_dossier.traitement']);
$request->where = array();
$where["traitement_dossier.validate"] = " IS NOT NULL";
$request->addWhere($where);
foreach ($ds->loadHashList($request->makeSelect()) as $day => $_complet) {
  $days[$day]["complet"] = $_complet;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("filterFunction", $filterFunction);
$smarty->assign("order_way"     , $order_way);
$smarty->assign("order_col"     , $order_col);
$smarty->assign("tri_recept"    , $tri_recept);
$smarty->assign("tri_complet"   , $tri_complet);
$smarty->assign('date'          , $date);
$smarty->assign('lastmonth'     , $lastmonth);
$smarty->assign('nextmonth'     , $nextmonth);
$smarty->assign('bank_holidays' , $bank_holidays);
$smarty->assign('days'          , $days);
$smarty->display("traitement_dossiers/inc_traitement_dossiers_month");