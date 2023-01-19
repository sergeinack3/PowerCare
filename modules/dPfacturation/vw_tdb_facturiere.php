<?php

/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Facturation\CFactureEtablissement;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkEdit();

$facture_switch         = CView::get("facture_switch", "str default|", true);
$get_list               = CView::get("get_list", "bool default|0");
$date_min               = CView::get("_date_min", "date default|" . CMbDT::date("-1 month"));
$date_max               = CView::get("_date_max", "date default|now");
$patient_id             = CView::get("patient_id", "ref class|CPatient");
$praticien_id           = CView::get("praticien_id", "ref class|CMediusers");
$numero_facture         = CView::get("num_facture", "str default|");
$xml_etat               = CView::get("xml_etat", "str default|");
$type_date_search       = CView::get("type_date_search", "str default|cloture");
$statuts                = CView::get("statut", "str default|");
$page                   = CView::get("page", "num default|-1");

CView::checkin();
$nb_facture_par_page = 25;

$smarty = new CSmartyDP();
$smarty->assign("facture_switch", $facture_switch);
$template = "";
if ($get_list) {
  $template = "_factures";
  $patient  = new CPatient();
  $patient->load($patient_id);
  $consultation            = new CConsultation();
  $consultation->_date_min = $date_min;
  $consultation->_date_max = $date_max;
  $facture                 = new CFactureCabinet();
  $praticien               = new CMediusers();
  $praticiens              = $praticien->loadPraticiens(PERM_EDIT);
  $all_praticiens          = $praticien->loadPraticiens(PERM_EDIT, null, null, null, false);
  if ($praticien_id) {
    $praticien->load($praticien_id);
  }
  $smarty->assign("patient", $patient);
  $smarty->assign("facture", $facture);
  $smarty->assign("praticiens", $praticiens);
  $smarty->assign("all_praticiens", $all_praticiens);
  $smarty->assign("praticien_id", $praticien_id);
  $smarty->assign("consultation", $consultation);

  if ($page >= 0) {
    $template .= "_list";

    /** @var $facture CFactureEtablissement|CFactureCabinet */
    $facture = new $facture_switch();
    $ljoin   = array();
    $where   = array(
      "$type_date_search" => "BETWEEN '$date_min' AND '$date_max'",
      "group_id"          => "= '" . CGroups::loadCurrent()->_id . "'",
    );
    if ($patient_id) {
      $where["patient_id"] = "= '$patient_id'";
    }
    if ($praticien_id) {
      $where["praticien_id"] = "= '$praticien_id'";
    }
    if ($numero_facture) {
      $where["facture_id"] = "= '$numero_facture'";
    }
    if ($xml_etat) {
      $where["statut_envoi"] = "= '$xml_etat'";
    }
    if (is_array($statuts)) {
      if (in_array("cloture", $statuts)) {
        $where[] = "cloture IS NOT NULL";
      }
      if (in_array("no-cloture", $statuts)) {
        $where[] = "cloture IS NULL";
      }
      if (in_array("extourne", $statuts)) {
        $where["extourne"] = "= '1'";
      }
      if (in_array("no-annule", $statuts)) {
        $where["annule"] = "= '0'";
      }
      if (in_array("regle", $statuts)) {
        $where[] = "regle = '1'";
      }
      if (in_array("no-regle", $statuts)) {
        $where[] = "regle = '0'";
      }
      if (in_array("rejete", $statuts)) {
        $ljoin["facture_rejet"]        = "facture_rejet.facture_class = '$facture_switch' AND facture_rejet.facture_id = " . $facture->_spec->table . ".facture_id";
        $where["facture_rejet.statut"] = "= 'attente'";
      }
    }
    $factures_count = $facture->countList($where, null, $ljoin);
    $factures       = $facture->loadList($where, null, "$page, $nb_facture_par_page", null, $ljoin);
    CMbObject::massLoadFwdRef($factures, "patient_id");
    foreach ($factures as $_facture) {
      $_facture->loadRefPatient();
      $_facture->loadStatut();
    }

    $filters = array(
      "CFacture"                       => CAppUI::tr($facture_switch),
      "CConsultation-_date_min"        => $date_min,
      "CConsultation-_date_max"        => $date_max,
      "CFactureEtablissement-date-of"  => CappUI::tr("CFactureEtablissement-etat.$type_date_search"),
      "CPatient"                       => $patient_id ? $patient->_view : "",
      "Praticien"                      => $praticien_id ? $praticien->_view : "",
      "CFactureCabinet-numero"         => $numero_facture ?: "",
      "CFactureCabinet-statut_envoi"   => $xml_etat ? CAppUI::tr("CFactureCabinet.statut_envoi.$xml_etat") : "",
      "CFactureCabinet-statut-invoice" => ""
    );
    foreach ($statuts as $_statut) {
      if ($_statut === "all") {
        continue;
      }
      $filters["CFactureCabinet-statut-invoice"] .= ($filters["CFactureCabinet-statut-invoice"] === "" ? "" : ", ")
        . CAppUI::tr("CFactureCabinet-facture.$_statut|f");
    }


    $smarty->assign("active_filter", $filters);
    $smarty->assign("facture_list", $factures);
    $smarty->assign("facture_count", $factures_count);
    $smarty->assign("page", $page);
    $smarty->assign("nb_facture_par_page", $nb_facture_par_page);
  }
}
$smarty->display("tdb_facturiere/tdb_facturiere$template");
