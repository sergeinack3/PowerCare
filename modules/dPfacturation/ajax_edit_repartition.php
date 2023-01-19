<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Facturation\CFacture;

CCanDo::checkEdit();
$facture_id    = CValue::getOrSession("facture_id");
$facture_class = CValue::getOrSession("facture_class");

/* @var CFacture $facture*/
$facture = new $facture_class;
$facture->load($facture_id);
$facture->loadRefsObjects();

$montant_total = $facture->du_tiers + $facture->du_patient;

// Creation du template
$smarty = new CSmartyDP();

$smarty->assign("facture"       , $facture);
$smarty->assign("montant_total" , $montant_total);
$smarty->assign("consult"       , $facture->_ref_first_consult);

$smarty->display("edit_repartition");