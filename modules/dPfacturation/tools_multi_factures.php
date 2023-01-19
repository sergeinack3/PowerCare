<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkAdmin();

$date_min      = CView::get("date_min", "date default|", true);
$date_max      = CView::get("date_max", "date default|now", true);
$praticien_id  = CView::get("praticien_id", "str default|-1", true);
$current       = CView::get("current", "str default|0");
$element_class = CView::get("element_class", "str default|");

CView::checkin();

if (!$date_min) {
  $date_min = CMbDT::date("-1 days", $date_max);
}
$nb_elements = 10;

$ds = CSQLDataSource::get("std");
$elements = array();
$totaux = array();
$element_to_factures = array();
foreach (array("consultation" => "CConsultation", "evenement_patient"=>"CEvenementPatient") as $_table => $_class) {
  if ($element_class && $element_class !== $_class) {
    continue;
  }
  $request = new CRequest();
  $request->addSelect(
    array(
      "$_table.".$_table."_id as element_id",
      "COUNT(DISTINCT facture_cabinet.facture_id) as annule",
      "GROUP_CONCAT(DISTINCT facture_cabinet.facture_id SEPARATOR ',') as facture_id",
    )
  );
  $request->addTable("$_table");
  $request->addLJoin(
    array(
      "facture_liaison" => "facture_liaison.object_id = $_table.".$_table."_id AND facture_liaison.object_class = '$_class'",
      "facture_cabinet" => "facture_cabinet.facture_id = facture_liaison.facture_id"
    )
  );
  $request->addWhere(
    array(
      "facture_cabinet.ouverture" => "BETWEEN '$date_min' AND '$date_max'",
      "facture_cabinet.annule"    => "= '0'",
      "facture_cabinet.group_id"    => "= '".CGroups::get()->_id."'"
    )
  );
  if ($praticien_id && $praticien_id !== '-1') {
    $request->addWhere(
      array(
        "facture_cabinet.praticien_id" => "= '$praticien_id'"
      )
    );
  }
  $request->addGroup("$_table.".$_table."_id");
  $request->addHaving(
    array(
      "annule" => "> 1"
    )
  );
  $totaux[$_class] = count($ds->loadColumn($request->makeSelect()));
  $request->setLimit("$current, $nb_elements");
  $res_elements = $ds->loadList($request->makeSelect());
  $elements[$_class] = array();
  foreach ($res_elements as $_element) {
    /** @var CConsultation $element */
    $element = new $_class();
    $element->load($_element["element_id"]);
    $element->loadRefFacture();
    $element->loadRefPatient();
    $elements[$_class][] = $element;
    foreach (explode(",", $_element["facture_id"]) as $_facture_id) {
      if (!$_facture_id) {
        continue;
      }
      if (!isset($element_to_factures[$element->_id])) {
        $element_to_factures[$element->_id] = array();
      }
      $_facture = new CFactureCabinet();
      $_facture->load($_facture_id);
      $element_to_factures[$element->_id][$_facture_id] = $_facture;
    }
  }
}

// Creation du template
$smarty = new CSmartyDP();

if ($element_class) {
  $smarty->assign("total",         $totaux[$element_class]);
  $smarty->assign("elements",      $elements[$element_class]);
  $smarty->assign("element_class", $element_class);
  $tpl = "tools_elements_by_class";
}
else {
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
  $smarty->assign("praticiens",          $praticiens);
  $smarty->assign("date_min",            $date_min);
  $smarty->assign("date_max",            $date_max);
  $smarty->assign("praticien_id",        $praticien_id);
  $smarty->assign("totaux",              $totaux);
  $smarty->assign("elements_list",       $elements);
  $tpl = "tools_elements";
}
$smarty->assign("js_function",           "showMultiFactures");
$smarty->assign("tool_lib",              "multifactures");
$smarty->assign("element_to_factures",   $element_to_factures);
$smarty->assign("current",               $current);
$smarty->assign("nb",                    $nb_elements);

$smarty->display("tools/$tpl");