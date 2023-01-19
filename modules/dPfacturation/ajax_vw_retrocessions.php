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

$filter = new CConsultation;
$filter->_date_min = CValue::getOrSession("_date_min", CMbDT::date());
$filter->_date_max = CValue::getOrSession("_date_max", CMbDT::date("+ 0 day"));


// Filtre sur les praticiens
$chir_id = CValue::getOrSession("chir");
$prat = new CMediusers;
$prat->load($chir_id);
$listPrat = CConsultation::loadPraticiensCompta($chir_id);

$where = array();
$where["cloture"] = "BETWEEN '$filter->_date_min' AND '$filter->_date_max'";
$where["praticien_id"] = CSQLDataSource::prepareIn(array_keys($listPrat));

if (CAppUI::gconf("dPfacturation CFactureCabinet view_bill")) {
  $facture = new CFactureCabinet();
}
if (CAppUI::gconf("dPfacturation CFactureEtablissement view_bill")) {
  $facture = new CFactureEtablissement();
}
$factures = $facture->loadList($where, "cloture, praticien_id");

$total_retrocession = 0;
$total_montant      = 0;
foreach ($factures as $_facture) {
  $_facture->loadRefPatient();
  $_facture->loadRefPraticien();
  $_facture->loadRefsObjects();
  $_facture->loadRefsReglements();
  $_facture->updateMontantRetrocession();
  $total_retrocession += $_facture->_montant_retrocession;
  $total_montant      += $_facture->_montant_avec_remise;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("print"         , CValue::get("print", 0));
$smarty->assign("prat"          , $prat);
$smarty->assign("factures"      , $factures);
$smarty->assign("filter"        , $filter);
$smarty->assign("total_montant" , $total_montant);
$smarty->assign("total_retrocession" , $total_retrocession);

$smarty->display("inc_vw_retrocessions");