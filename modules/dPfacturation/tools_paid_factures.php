<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkAdmin();

$current       = CView::get("current", "num default|0");
$date_min      = CView::get("date_min", "date default|", true);
$date_max      = CView::get("date_max", "date default|now", true);
$praticien_id  = CView::get("praticien_id", "str default|-1", true);
$lite          = CView::get("lite", "bool default|0");

CView::checkin();

$nb = 25;

$types = array();
$utypes_flip = array_flip(CUser::$types);
foreach (array("Chirurgien", "Anesthésiste", "Médecin", "Dentiste") as $_type) {
  $types[] = $utypes_flip[$_type];
}

$praticien = new CMediusers();
$praticiens = $praticien->loadList(
  array(
    "group_id" => "= '".CGroups::get()->_id."'",
    "users.user_type"     => CSQLDataSource::prepareIn($types)
  ),
  "users.user_last_name, users.user_first_name",
  null, null,
  array(
    "users"               => "users_mediboard.user_id = users.user_id",
    "functions_mediboard" => "functions_mediboard.function_id = users_mediboard.function_id"
  )
);

$request = new CRequest();
$request->addTable("facture_cabinet");
$request->addSelect(
  array(
    "SUM(reglement.montant) as 'reglement'",
    "facture_cabinet.du_patient as 'du_patient'",
    "ABS(SUM(reglement.montant)-facture_cabinet.du_patient) as 'delta'",
    "facture_cabinet.facture_id as 'facture_id'",
  )
);

$where = array(
  "facture_cabinet.ouverture" => "BETWEEN '$date_min' AND '$date_max'",
  "reglement.emetteur"        => "= 'patient'",
  "facture_cabinet.patient_date_reglement" => "IS NULL",
);
$ljoin = array(
  "reglement" => "facture_cabinet.facture_id = reglement.object_id AND reglement.object_class = 'CFactureCabinet'"
);
if ($praticien_id !== "-1") {
  $where["praticien_id"] = "= '$praticien_id'";
}
else {
  $where["praticien_id"] = CSQLDataSource::prepareIn(CMbArray::pluck($praticiens, "user_id"));
}

$request->addWhere($where);
$request->addLJoin($ljoin);
$request->addHaving(
  array(
    "delta < 0.05 AND delta > 0 AND delta IS NOT NULL"
  )
);
$request->addGroup("facture_cabinet.facture_id");

$datasource = CSQLDataSource::get("std");
$total = $datasource->countRows($request->makeSelect());
$request->setLimit("$current, $nb");
$factures_list = $datasource->loadList($request->makeSelect());

foreach ($factures_list as $_key=>$_facture) {
  $facture = new CFactureCabinet();
  $facture->load($_facture["facture_id"]);
  $facture->loadRefPatient();
  $facture->loadRefsObjects();
  $factures_list[$_key]["facture"] = $facture;
  $factures_list[$_key]["reglement"] = sprintf("%.4f", $_facture["reglement"]);
  $factures_list[$_key]["delta"] = sprintf("%.4f", $_facture["delta"]);
  $factures_list[$_key]["du_patient"] = sprintf("%.4f", $_facture["du_patient"]);
}

$smarty = new CSmartyDP();
if (!$lite) {
  $smarty->assign("praticiens",   $praticiens);
}
$smarty->assign("factures",     $factures_list);
$smarty->assign("current",      $current);
$smarty->assign("date_min",     $date_min);
$smarty->assign("date_max",     $date_max);
$smarty->assign("praticien_id", $praticien_id);
$smarty->assign("total",        $total);
$smarty->assign("lite",         $lite);
$smarty->assign("nb",           $nb);
$smarty->display("tools/tools_paid_factures");