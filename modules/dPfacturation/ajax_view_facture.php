<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Facturation\CReglement;
use Ox\Mediboard\Facturation\CFactureEtablissement;

CCanDo::checkEdit();
$facture_id   = CValue::getOrSession("facture_id");
$object_class = CValue::getOrSession("object_class", "CFactureCabinet");

$facture = new $object_class;
if ($facture_id) {
  /* @var CFactureCabinet $facture */
  $facture->load($facture_id);
  $facture->loadRefPatient();
  $facture->loadRefPraticien();
  $facture->loadRefAssurance();
  $facture->loadRefsObjects();
  $facture->loadRefsReglements();
  $facture->loadRefsRelances();
  $facture->loadRefsNotes();
  $facture->loadCoefficients();
  $facture->loadRefCategory();
  $facture->loadFileXML();
  $facture->loadRefsAvoirs();
  $facture->loadRefExtourne();
}

// Création du template
$smarty = new CSmartyDP();

if (!CValue::get("not_load_banque")) {
  $smarty->assign("factures"    , array(new CFactureEtablissement()));
}
$smarty->assign("facture"     , $facture);
$smarty->assign("reglement"   , new CReglement());
$smarty->assign("etat_ouvert" , CValue::getOrSession("etat_ouvert", 1));
$smarty->assign("show_button" , CValue::get("show_button", 1));
$smarty->assign("date"        , CMbDT::date());
$smarty->assign("chirSel"     , CValue::getOrSession("chirSel", "-1"));

$smarty->display("inc_vw_facturation");
