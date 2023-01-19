<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Facturation\CEcheance;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Facturation\CFactureEtablissement;

CCanDo::checkEdit();
$facture_class = CView::get("object_class", "enum list|CFactureCabinet|CFactureEtablissement");
$facture_id    = CView::get("object_id", "ref class|$facture_class");
$date_debut    = CView::get("date", "date");
$nb_month      = CView::get("nb_month", "num");
$interest      = CView::get("interest", "float");
$description   = CView::get("description", "str");

CView::checkin();

/** @var CFactureEtablissement|CFactureCabinet $facture */
$facture = new $facture_class();
$facture->load($facture_id);
if (!$facture->_id || $nb_month === 0) {
  CApp::rip();
}
$montant_facture = $facture->montant_total > 0 ? $facture->montant_total : ($facture->du_patient + $facture->du_tiers);

$montant_interest = $montant_facture * (1 + ($interest / 100));
$montant_men = floor(($montant_interest / $nb_month) * 100) / 100;
$msgs = array();

for ($i = 0; $i < $nb_month; $i++) {
  $montant_interest -= $montant_men;
  $echeance = new CEcheance();
  $echeance->object_id = $facture->_id;
  $echeance->object_class = $facture->_class;
  $echeance->date = CMbDT::date("first day of +$i month", $date_debut);
  $echeance->montant = $montant_men > $montant_interest ? $montant_interest + $montant_men : $montant_men;
  $echeance->description = $description;
  if ($msg = $echeance->store()) {
    CAppUI::stepAjax($msg, UI_MSG_ERROR);
  }
}
