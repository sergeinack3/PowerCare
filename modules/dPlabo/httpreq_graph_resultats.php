<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CFlotrGraph;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Labo\CPrescriptionLaboExamen;

CCanDo::checkRead();

$prescription_labo_examen_id = CView::get("prescription_labo_examen_id", "ref class|CPrescriptionLaboExamen notNull", true);

CView::checkin();

// Chargement de l'item choisi
$prescriptionItem = new CPrescriptionLaboExamen;
$prescriptionItem->load($prescription_labo_examen_id);

$prescription = $prescriptionItem->loadRefPrescription();
$examen = $prescriptionItem->loadRefExamen();

$prescription->loadRefPatient();

$siblingItems = $prescriptionItem->loadSiblings();

$resultats = $prescriptionItem->loadResults($prescription->patient_id, $examen->_id, 20);
$resultats = array_reverse($resultats, true);

$series = array();
$options = array();

// Prepare values
$xlabels = array();
$min = $examen->min;
$max = $examen->max;
$delta = 2;

$_serie = array();
foreach (array_values($resultats) as $_i => $resultat) {
  $min = min($min, $resultat->resultat);
  $max = max($max, $resultat->resultat);

  $_serie[]  = array($_i, $resultat->resultat);
  $xlabels[] = array($_i, $resultat->date ? CMbDT::dateToLocale($resultat->date) : "attendu");
}

$series[] = $_serie;

$options = CFlotrGraph::merge("lines", array(
  "xaxis" => array(
    "ticks" => $xlabels,
  ),
  "yaxis" => array(
    "labelWidth" => 25,
    "min" => $min - $delta,
    "max" => $max + $delta,
  ),
  'grid'       => array(
    'verticalLines' => true,
  ),
));

/*
if ($examen->max) {
  $uband = new PlotBand(HORIZONTAL, BAND_RDIAG, $examen->max, "max", "#ffbbbb");
  $uband->ShowFrame(true);
  $uband->SetDensity(92);
  $this->AddBand($uband);
}

if ($examen->min) {
  $lband = new PlotBand(HORIZONTAL, BAND_RDIAG, "min", $examen->min, "#ffbbbb");
  $lband->ShowFrame(true);
  $lband->SetDensity(92);
  $this->AddBand($lband);
}*/

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("prescriptionItem", $prescriptionItem);
$smarty->assign("siblingItems", $siblingItems);
$smarty->assign("series", $series);
$smarty->assign("options", $options);

$smarty->display("inc_graph_resultats");
