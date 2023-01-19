<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

global $uistyle;

use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Labo\CCatalogueLabo;
use Ox\Mediboard\Labo\CPrescriptionLabo;
use Ox\Mediboard\Labo\CPrescriptionPdf;

// Recuperation de l'id de la prescription
$prescription_id = CValue::get("prescription_id");

// Chargement de la prescription selectionnée
$prescription = new CPrescriptionLabo();
$prescription->load($prescription_id);
$prescription->loadRefsFwd();
$prescription->_ref_praticien->loadRefFunction();
$prescription->_ref_praticien->_ref_function->loadRefsFwd();
$prescription->loadRefsBack();
$prescription->loadClassification();

$tab_prescription = array();
$tab_pack_prescription = array();

// Creation d'un nouveau fichier pdf
$pdf = new CPrescriptionPdf("P", "mm", "A4", true); 

// Chargement de l'etablissement
$etab = CGroups::loadCurrent();

// Affichage de l'entete du document
// Impossible d'utiliser mbNormal.gif ==> format gif non supporté
$image = "../../style/$uistyle/images/pictures/logo.jpg";

// Si le style ne possede pas de logo, on applique le logo par defaut de mediboard
if (!is_file("./style/$uistyle/images/pictures/logo.jpg")) {
  $image = "logo.jpg";
}

$taille = "75";
$texte = "$etab->_view\n$etab->adresse\n$etab->cp $etab->ville\nTel: $etab->tel";
$pdf->SetHeaderData($image, $taille, "", $texte);

// Définition des marges de la pages
$pdf->SetMargins(15, 40);

// Définition de la police et de la taille de l'entete
$pdf->setHeaderFont(Array("vera", '', "10"));

// Creation d'une nouvelle page
$pdf->AddPage();

$praticien =& $prescription->_ref_praticien;
$patient =& $prescription->_ref_patient;

// Affichage du praticien et du patient à l'aide d'un tableau
$pdf->createTab(
  $pdf->viewPraticien(
    $praticien->_view, 
    $praticien->_ref_function->_view, 
    $praticien->_ref_function->_ref_group->_view
  ), 
  $pdf->viewPatient(
    $patient->_view, 
    CMbDT::transform($patient->naissance, null, '%d-%m-%y'), 
    $patient->adresse, 
    $patient->cp, 
    $patient->ville, 
    $patient->tel
  )
);

$urgent = "";
if ($prescription->urgence) {
  $urgent = "(URGENT)";
}
$pdf->setY(65);
$pdf->writeHTML(
  utf8_encode("<b>Prélèvement du ".(CMbDT::transform($prescription->date, null, '%d-%m-%y à %H:%M'))." ".$urgent."</b>")
);

$pdf->setY(90);

$pdf->SetFillColor(246, 246, 246);
$pdf->Cell(25, 7, utf8_encode("Code"), 1, 0, 'C', 1);
$pdf->Cell(85, 7, utf8_encode("Libellé"), 1, 0, 'C', 1);
$pdf->Cell(30, 7, utf8_encode("Résultat"), 1, 0, 'C', 1);
$pdf->Cell(20, 7, utf8_encode("Unité"), 1, 0, 'C', 1);
$pdf->Cell(20, 7, utf8_encode("Normes"), 1, 0, 'C', 1);
$pdf->Ln();

/**
 * Impression des résultarts du catalogue
 *
 * @param CCatalogueLabo   $catalogue Catalogue concerné
 * @param CPrescriptionPDF $pdf       le PDF
 *
 * @return void
 */
function printResultsCatalogue($catalogue, &$pdf) {
  if (count($catalogue->_ref_prescription_items)) {
    $pdf->Cell(180, 7, utf8_encode($catalogue->libelle), 1, 0, 'L', 0);
    $pdf->Ln();
  }
  foreach ($catalogue->_ref_prescription_items as $_item) {
    $analyse = $_item->_ref_examen_labo;
    $pdf->Cell(25, 7, utf8_encode($analyse->identifiant), 1, 0, 'L', 0);
    $pdf->Cell(85, 7, utf8_encode($analyse->libelle), 1, 0, 'L', 0);
    if (!$analyse->_external) {
      if ($_item->date) {
        $resultat = $_item->resultat;
      }
      else {
        $resultat = "En attente";
      }

      $pdf->Cell(30, 7, utf8_encode($_item->resultat), 1, 0, 'L', 0);
      if ($analyse->type == "num" || $analyse->type == "float") {
        $pdf->Cell(20, 7, utf8_encode($analyse->unite), 1, 0, 'L', 0);
        $pdf->Cell(20, 7, utf8_encode("$analyse->min - $analyse->max"), 1, 0, 'L', 0);
      }
      else {
        $pdf->Cell(40, 7, utf8_encode($analyse->type), 1, 0, 'L', 0);
      }
    }
    else {
      $pdf->Cell(70, 7, utf8_encode("Analyse externe"), 1, 0, 'L', 0);
    }
    $pdf->Ln();
  }
  foreach ($catalogue->_ref_catalogues_labo as $sub_catalogue) {
    printResultsCatalogue($sub_catalogue, $pdf);
  }
  
}

foreach ($prescription->_ref_classification_roots as $_catalogue) {
  printResultsCatalogue($_catalogue, $pdf);
}

// Nom du fichier: prescription-xxxxxxxx.pdf   / I : sortie standard
$pdf->Output("resultat-$prescription->_id.pdf", "I");
