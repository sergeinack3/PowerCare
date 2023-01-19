<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

global $uistyle;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Labo\CPrescriptionLabo;
use Ox\Mediboard\Labo\CPrescriptionPdf;
use Ox\Mediboard\Sante400\CIdSante400;

// Recuperation de l'id de la prescription
$prescription_id = CValue::get("prescription_id");

// Chargement de la prescription selectionnée
$prescription = new CPrescriptionLabo();
$prescription->load($prescription_id);
$prescription->loadRefsFwd();
$prescription->_ref_praticien->loadRefFunction();
$prescription->_ref_praticien->_ref_function->loadRefsFwd();
$prescription->loadRefsBack();

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

$pdf->setY(80);
// Affichage des analyses
$pdf->writeHTML(utf8_encode("<b>Analyses demandées:</b>"));

$pdf->SetFillColor(246, 246, 246);
$pdf->Cell(25, 7, utf8_encode("Identifiant"), 1, 0, 'C', 1);
$pdf->Cell(105, 7, utf8_encode("Libellé de l'analyse"), 1, 0, 'C', 1);
$pdf->Cell(30, 7, utf8_encode("Type"), 1, 0, 'C', 1);
$pdf->Cell(20, 7, utf8_encode("Loc."), 1, 0, 'C', 1);
$pdf->Ln();

$tagCatalogue = CAppUI::gconf("dPlabo CCatalogueLabo remote_name");

// Chargement de l'id externe labo code4 du praticien
// Chargement de l'id400 "labo code4" du praticien
$tagCode4 = "labo code4";
$idex = new CIdSante400();
$idex->loadLatestFor($praticien, $tagCode4);


if ($idex->id400) {
  $numPrat = $idex->id400;
  $numPrat = str_pad($numPrat, 4, '0', STR_PAD_LEFT);
}
else {
  $numPrat = "xxxx";
}

// Chargement de la valeur de l'id externe de la prescription ==> retourne uniquement l'id400
if ($prescription->verouillee) {
  $idex = $prescription->loadIdPresc();
  $idex = str_pad($idex, 4, '0', STR_PAD_LEFT);
}
else {
  $idex = "xxxx";
}

$num = $numPrat.$idex;

// Initialisation du code barre, => utilisation par default du codage C128B
// L'affichage du code barre est realisee dans la fonction redefinie Footer dans la classe CPrescriptionPdf
$pdf->SetBarcode(
  $num, 
  $prescription->_ref_praticien->_user_last_name, 
  substr($prescription->_ref_patient->_view, 0, 20), 
  $prescription->_ref_patient->sexe, 
  CMbDT::transform($prescription->_ref_patient->naissance, null, "%d-%m-%y"), 
  CMbDT::transform($prescription->date, null, "%d-%m-%y %H:%M")
);

// Tableau de classement des analyses par pack
foreach ($prescription->_ref_prescription_items as $key => $item) {
  if ($item->_ref_pack->_id) {
    $tab_pack_prescription[$item->_ref_pack->_view][] = $item;
  }
  else {
    $tab_prescription[] = $item;
  }
}

foreach ($tab_pack_prescription as $key => $pack) {
  if ($key) {
    $pdf->Cell(0, 7, utf8_encode($key), 1, 0, 'C', 1);
    $pdf->Ln();
  }
  foreach ($pack as $key2 => $_item) {
    $examen_labo =& $_item->_ref_examen_labo;
    //$pdf->SetFillColor(230, 245, 255);
    $pdf->Cell(25, 7, utf8_encode($examen_labo->identifiant), 1, 0, 'L', 0);
    $pdf->Cell(105, 7, utf8_encode($examen_labo->libelle), 1, 0, 'L', 0);
    $pdf->Cell(30, 7, utf8_encode($examen_labo->type_prelevement), 1, 0, 'L', 0);
    if ($examen_labo->_external) {
      $pdf->Cell(20, 7, "Externe", 1, 0, 'L', 0);
    }
    else {
      $pdf->Cell(20, 7, "Interne", 1, 0, 'L', 0);
    }
    $pdf->Ln();

    // si on atteint y max de contenu de la page, on change de page
    if ($pdf->getY() > 200) {
      $pdf->AddPage();
    }
  }
}

if ($tab_pack_prescription && $tab_prescription) {
  $pdf->Cell(0, 7, "Autres analyses", 1, 0, 'C', 1);
  $pdf->Ln();
}

foreach ($tab_prescription as $key => $_item) {
  $examen_labo =& $_item->_ref_examen_labo;
  //$pdf->SetFillColor(230, 245, 255);
  $pdf->Cell(25, 7, utf8_encode($examen_labo->identifiant), 1, 0, 'L', 0);
  $pdf->Cell(105, 7, utf8_encode($examen_labo->libelle), 1, 0, 'L', 0);
  $pdf->Cell(30, 7, utf8_encode($examen_labo->type_prelevement), 1, 0, 'L', 0);
  if ($examen_labo->_external) {
    $pdf->Cell(20, 7, "Externe", 1, 0, 'L', 0);
  }
  else {
    $pdf->Cell(20, 7, "Interne", 1, 0, 'L', 0);
  }
  $pdf->Ln();

  if ($pdf->getY() > 200) {
    $pdf->AddPage();
  }
}

// Nom du fichier: prescription-xxxxxxxx.pdf   / I : sortie standard
$pdf->Output("prescription-$num.pdf", "I");