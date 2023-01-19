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
use Ox\Core\CView;
use Ox\Mediboard\Facturation\CEcheance;

CCanDo::checkEdit();
$facture_class = CView::get("facture_class", "enum list|CFactureCabinet|CFactureEtablissement");
$facture_id    = CView::get("facture_id", "ref class|$facture_class");
CView::checkin();

$echeance = new CEcheance();
$echeance->object_id = $facture_id;
$echeance->object_class = $facture_class;
$echeance->date = CMbDT::date("first day of +1 month", CMbDT::date());

$facture = new $facture_class();
$facture->load($facture_id);
$montant_facture = $facture->montant_total > 0 ? $facture->montant_total : ($facture->du_patient + $facture->du_tiers);

// Creation du template
$smarty = new CSmartyDP();
$smarty->assign("echeance", $echeance);
$smarty->assign("facture", $facture);
$smarty->assign("montant_facture", $montant_facture);
$smarty->display("vw_monthly_echeance");