<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbPdf;
use Ox\Core\CMbPDFMerger;
use Ox\Core\CValue;
use Ox\Mediboard\Hospi\CModeleEtiquette;
use Ox\Mediboard\Stock\CProductOrderItemReception;
use Ox\Mediboard\Stock\CProductReception;

CCanDo::checkRead();

$reception_id = CValue::get('reception_id');
$lot_id       = CValue::get('lot_id');
$force_print  = CValue::get('force_print');

$reception = new CProductReception();
if ($reception_id) {
  $reception->load($reception_id);
  $reception->loadRefsFwd();
  $reception->loadRefsBack();
}

if ($reception->_id) {
  $lots = $reception->_ref_reception_items;
}
else {
  $lot = new CProductOrderItemReception();
  $lot->load($lot_id);
  $lots = array($lot);
}

// Recherche d'un éventuel modèle d'étiquettes
$modele_etiquette               = new CModeleEtiquette();
$modele_etiquette->object_class = "CProductOrderItemReception";
$modele_etiquette->loadMatchingObject();

if ($modele_etiquette->_id) {
  $save_texte = $modele_etiquette->texte;

  $files = array();
  $pdf   = new CMbPDFMerger();

  /** @var CProductOrderItemReception $_lot */
  foreach ($lots as $_lot) {
    if (!$_lot->barcode_printed || $force_print) {
      $modele_etiquette->texte = $save_texte;

      $fields = array();
      $_lot->completeLabelFields($fields, null);
      $modele_etiquette->completeLabelFields($fields, null);
      $modele_etiquette->replaceFields($fields);

      $file    = tempnam("./tmp", "barcode_" . $_lot->code);
      $files[] = $file;
      file_put_contents($file, $modele_etiquette->printEtiquettes(null, 0));
      for ($i = 0; $i < $_lot->quantity / $_lot->_ref_order_item->_ref_reference->quantity; $i++) {
        $pdf->addPDF($file);
      }
    }
  }

  try {
    $pdf->merge("browser", "barcodes.pdf");
  }
  catch (Exception $e) {
    CApp::rip();
  }

  return;
}

$pdf = new CMbPdf();

$pdf->setFont("vera", '', "10");

// Définition des marges de la pages
//$pdf->SetMargins(15, 15);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(0);

// Creation d'une nouvelle page
$pdf->AddPage();

$data = array();
$j    = 0;

foreach ($lots as &$item) {
  if (!$item->barcode_printed || $force_print) {
    $item->loadRefsBack();
    $item->loadRefsFwd();
    $item->_ref_order_item->loadReference();

    $reference = $item->_ref_order_item->_ref_reference;
    $reference->loadRefsFwd();
    $reference->_ref_product->loadRefsFwd();

    for ($i = 0; $i < $item->quantity / $reference->quantity; $i++) {
      $data[$j] = array();
      $d        = &$data[$j];

      $lines = explode("\n", wordwrap($reference->_ref_product->name, 30, "\n", true));
      $d[]   = $lines[0];
      $d[]   = isset($lines[1]) ? $lines[1] : "";
      $d[]   = $reference->_ref_product->code;
      $d[]   = "LOT $item->code  PER $item->lapsing_date";

      $d[] = array(
        'barcode' => "MB" . str_pad($item->_id, 8, "0", STR_PAD_LEFT),
        'type'    => 'C128B'
      );
      $j++;
    }
  }
}

$pdf->WriteBarcodeGrid(8, 8, 210 - 16, 297 - 16, 3, 10, $data);

// Nom du fichier: prescription-xxxxxxxx.pdf   / I : sortie standard
$pdf->Output("barcodes.pdf", "I");
