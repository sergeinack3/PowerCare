<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Facturation\CReglement;
use Ox\Mediboard\Facturation\CFacture;

CCanDo::checkEdit();
$facture_id   = CValue::getOrSession("facture_id");
$consult_id   = CValue::get("consult_id");
$object_class = CValue::getOrSession("object_class", "CFactureCabinet");
$object_class = $object_class == "CConsultation" ? "CFactureCabinet" : $object_class;

$consult = null;
/* @var CFacture $facture*/
$facture = new $object_class;
if ($consult_id) {
  $consult = new CConsultation();
  $consult->load($consult_id);

  CAccessMedicalData::logAccess($consult);

  $facture = $consult->loadRefFacture();
}
elseif ($facture_id) {
  $facture->load($facture_id);
}

$facture->load($facture_id);
$facture->loadRefPatient();
$facture->loadRefPraticien();
$facture->loadRefAssurance();
$facture->loadRefsObjects();
$facture->loadRefsReglements();
$facture->loadRefsNotes();
$facture->loadCoefficients();
$facture->loadRefCategory();
$facture->loadRefsAvoirs();

// Création du template
$smarty = new CSmartyDP();

if (!CValue::get("not_load_banque")) {
  $smarty->assign("factures"    , array(new $object_class()));
}
$smarty->assign("facture"     , $facture);
$smarty->assign("reglement"   , new CReglement());
$smarty->assign("consult"     , $consult);
$smarty->assign("etat_ouvert" , CValue::getOrSession("etat_ouvert", 1));
$smarty->assign("date"        , CMbDT::date());
$smarty->assign("chirSel"     , CValue::getOrSession("chirSel", "-1"));

$smarty->display("inc_vw_facturation.tpl");