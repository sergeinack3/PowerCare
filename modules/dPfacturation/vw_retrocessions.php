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
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Facturation\CFactureEtablissement;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();
// Gestion des bouton radio des dates
$now             = CMbDT::date();
$yesterday       = CMbDT::date("-1 DAY"         , $now);
$week_deb        = CMbDT::date("last sunday"    , $now);
$week_fin        = CMbDT::date("next sunday"    , $week_deb);
$week_deb        = CMbDT::date("+1 day"         , $week_deb);
$rectif          = CMbDT::transform("+0 DAY", $now, "%d")-1;
$month_deb       = CMbDT::date("-$rectif DAYS"  , $now);
$month_fin       = CMbDT::date("+1 month"       , $month_deb);
$three_month_deb = CMbDT::date("-3 month"       , $month_fin);
$month_fin       = CMbDT::date("-1 day"         , $month_fin);
$chir            = CValue::getOrSession("chir", 0);

$filter = new CConsultation;
$filter->_date_min = CValue::getOrSession("_date_min", CMbDT::date());
$filter->_date_max = CValue::getOrSession("_date_max", CMbDT::date("+ 0 day"));

// L'utilisateur est-il praticien ?
$mediuser = CMediusers::get();
$mediuser->loadRefFunction();
$listPrat = CConsultation::loadPraticiensCompta();

$prat = new CMediusers();
$prat->load($chir);
$prat->loadRefFunction();
$listchir = ($prat->_id) ? array($prat->_id => $prat) : $listPrat;

$total_retrocession = 0;
$total_montant      = 0;
$where = array();
$where["cloture"] = "BETWEEN '$filter->_date_min' AND '$filter->_date_max'";
$where["praticien_id"] = CSQLDataSource::prepareIn(array_keys($listchir));

if (CAppUI::gconf("dPfacturation CFactureCabinet view_bill")) {
  $facture = new CFactureCabinet();
}
if (CAppUI::gconf("dPfacturation CFactureEtablissement view_bill")) {
  $facture = new CFactureEtablissement();
}
$factures = $facture->loadList($where, "cloture, praticien_id");

foreach ($factures as $_facture) {
  $_facture->loadRefPatient();
  $_facture->_ref_patient->loadRefsCorrespondantsPatient();
  $_facture->loadRefPraticien();
  $_facture->loadRefAssurance();
  $_facture->loadRefsObjects();
  $_facture->loadRefsReglements();
  $_facture->loadRefsRelances();
  $_facture->updateMontantRetrocession();
  $_facture->loadRefsNotes();
  $total_retrocession += $_facture->_montant_retrocession;
  $total_montant      += $_facture->_montant_avec_remise;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("filter"          , $filter);
$smarty->assign("listPrat"        , $listPrat);
$smarty->assign("prat"            , $prat);
$smarty->assign("now"             , $now);
$smarty->assign("yesterday"       , $yesterday);
$smarty->assign("week_deb"        , $week_deb);
$smarty->assign("week_fin"        , $week_fin);
$smarty->assign("month_deb"       , $month_deb);
$smarty->assign("three_month_deb" , $three_month_deb);
$smarty->assign("month_fin"       , $month_fin);
$smarty->assign("print"           , CValue::get("print", 0));
$smarty->assign("factures"        , $factures);
$smarty->assign("total_montant"   , $total_montant);
$smarty->assign("total_retrocession"  , $total_retrocession);

$smarty->display("vw_retrocessions");