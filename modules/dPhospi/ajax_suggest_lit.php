<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CLit;

CCanDo::checkRead();

$affectation_id       = CView::get("affectation_id", "ref class|CAffectation");
$_link_affectation    = CView::get("_link_affectation", "bool default|0");
$services_ids_suggest = CView::get("services_ids_suggest", "str");
$datetime             = CView::get("datetime", "dateTime");

CView::checkin();

if (!$datetime) {
  $datetime = CMbDT::dateTime();
}

$entree = $datetime;
$sortie = null;
$lit_id = null;

$group_id = CGroups::loadCurrent()->_id;

$affectation = new CAffectation();
$affectation->load($affectation_id)->loadRefLit()->loadRefChambre();

if (!$services_ids_suggest) {
  $pref_services_ids = json_decode(CAppUI::pref("services_ids_hospi"));
  if (isset($pref_services_ids->{"g$group_id"})) {
    $services_ids = $pref_services_ids->{"g$group_id"};
    if ($services_ids) {
      $services_ids_suggest = explode("|", $services_ids);
    }
  }
  if (!count($services_ids_suggest)) {
    $services_ids_suggest = array($affectation->_ref_lit->_ref_chambre->service_id);
  }
}
else {
  $services_ids_suggest = explode(",", $services_ids_suggest);
}

$sortie = $affectation->sortie;

$where                       = array();
$where["chambre.service_id"] = CSQLDataSource::prepareIn($services_ids_suggest);
$where['chambre.annule']     = " = '0'";
$where['lit.annule']         = " = '0'";

$ljoin            = array();
$ljoin["chambre"] = "lit.chambre_id = chambre.chambre_id";

$lit = new CLit();
/** @var CLit[] $lits */
$lits = $lit->loadList($where, null, null, null, $ljoin);

//unset($lits[$affectation->lit_id]);

$max_entree = 0;
$max_sortie = 0;

$ds = $lit->getDS();

foreach ($lits as $key => $_lit) {

  $_lit->_ref_affectations = array();
  $_lit->loadCompleteView();

  if ($_lit->_id == $affectation->lit_id) {

    $_lit->_ref_last_dispo         = new CAffectation();
    $_lit->_ref_last_dispo->sortie = $entree;
    $_lit->_dispo_depuis           = 0;
  }
  else {
    $where           = array();
    $where["lit_id"] = "= '$_lit->_id'";
    $where["entree"] = "<= '$sortie'";
    $where["sortie"] = ">= '$entree'";

    $affectation_collide = new CAffectation();
    $affectation_collide->loadObject($where);

    if ($affectation_collide->_id) {
      unset($lits[$key]);
      continue;
    }
    $where                 = array(
      "lit_id" => "= '$_lit->_id'",
      "sortie" => "<= '$entree'");
    $index                 = "lit_id";
    $_lit->_ref_last_dispo = new CAffectation();
    $_lit->_ref_last_dispo->loadObject($where, "sortie DESC", null, null, $index);

    $_lit->_dispo_depuis = strtotime($entree) - strtotime($_lit->_ref_last_dispo->sortie);
    if ($_lit->_dispo_depuis < 0) {
      unset($lits[$key]);
      continue;
    }

    if ($_lit->_ref_last_dispo->_id && $_lit->_dispo_depuis > $max_entree) {
      $max_entree = $_lit->_dispo_depuis;
    }
  }

  // Sexe de l'autre patient présent dans la chambre
  $sql                       = "SELECT sexe
          FROM affectation
          LEFT JOIN lit ON lit.lit_id = affectation.lit_id
          LEFT JOIN sejour ON sejour.sejour_id = affectation.sejour_id
          LEFT JOIN patients ON patients.patient_id = sejour.patient_id
          WHERE lit.chambre_id = '$_lit->chambre_id'
          AND lit.lit_id != '$_lit->_id'
          AND '$datetime' BETWEEN affectation.entree AND affectation.sortie";
  $_lit->_sexe_other_patient = $ds->loadResult($sql);

  $where                 = array(
    "lit_id" => "= '$_lit->_id'",
    "entree" => " >= '$sortie'");
  $_lit->_ref_next_dispo = new CAffectation();
  $_lit->_ref_next_dispo->loadObject($where, "entree ASC");

  $_lit->_dispo_depuis_friendly = CMbDT::relativeDuration($_lit->_ref_last_dispo->sortie, $entree);

  if ($_lit->_ref_next_dispo->entree) {
    $_lit->_occupe_dans = strtotime($_lit->_ref_next_dispo->entree) - strtotime($sortie);

    if ($_lit->_occupe_dans < 0) {
      unset($lits[$key]);
      continue;
    }

    if ($max_sortie < $_lit->_occupe_dans) {
      $max_sortie = $_lit->_occupe_dans;
    }

    $_lit->_occupe_dans_friendly = CMbDT::relativeDuration($sortie, $_lit->_ref_next_dispo->entree);
  }
  else {
    $_lit->_occupe_dans = "libre";
  }

    $_lit->_ref_last_dispo->sejour_id = $affectation->sejour_id;
    $_lit->_ref_last_dispo->makeUF();
}

// Tri des lits suivant la config
$order_changement_lit = CAppUI::conf("dPhospi vue_temporelle order_changement_lit", "CGroups-$group_id");

$sort_order = SORT_ASC;

switch ($order_changement_lit) {
  case "libre_ASC":
  case "libre_DESC":
  default:
    $sorter = CMbArray::pluck($lits, "_dispo_depuis");

    if ($order_changement_lit == "libre_DESC") {
      $sort_order = SORT_DESC;
    }
    break;

  case "alpha":
    $sorter = CMbArray::pluck($lits, "_view");
    break;

  case "rank":
    $sorter = CMbArray::pluck($lits, "_ref_chambre", "rank");
}

array_multisort($sorter, $sort_order, $lits);

$smarty = new CSmartyDP();

$smarty->assign("lits", $lits);
$smarty->assign("affectation_id", $affectation_id);
$smarty->assign("max_entree", $max_entree);
$smarty->assign("max_sortie", $max_sortie);
$smarty->assign("_link_affectation", $_link_affectation);
$smarty->assign("services_ids_suggest", $services_ids_suggest);
$smarty->assign("datetime", $datetime);

$smarty->display("inc_suggest_lit.tpl");
