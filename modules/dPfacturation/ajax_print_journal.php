<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\Facturation\CEditPdf;
use Ox\Mediboard\Facturation\CJournalBill;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Printing\CPrinter;

CCanDo::checkEdit();
$journal_id     = CValue::get("journal_id");
$uniq_checklist = CValue::get("uniq_checklist");

$journal = new CJournalBill();
$journal->load($journal_id);

$user = CMediusers::get();
$printer_bvr = new CPrinter();
$printer_bvr->function_id = $user->function_id;
$printer_bvr->label = "bvr";
$printer_bvr->loadMatchingObject();

if (!$printer_bvr->_id) {
  CAppUI::setMsg("Les imprimantes ne sont pas paramétrées", UI_MSG_ERROR);
  echo CAppUI::getMsg();
  return false;
}
else {
  $file = new CFile();
  $file->object_id = $journal->checklist_id;
  $file->object_class = "CJournalBill";
  $file->loadMatchingObject();
  $printer_bvr->loadRefSource()->sendDocument($file);
}

if (!$uniq_checklist) {
  $factures = $journal->loadRefsFacture();

  if (count($factures)) {
    $printer_justif = new CPrinter();
    $printer_justif->function_id = $user->function_id;
    $printer_justif->label = "justif";
    $printer_justif->loadMatchingObject();

    if (!$printer_justif->_id) {
      CAppUI::setMsg("Les imprimantes ne sont pas paramétrées", UI_MSG_ERROR);
      echo CAppUI::getMsg();
      return false;
    }
    $file = new CFile();
    foreach ($factures as $facture) {
      $facture_pdf = new CEditPdf();
      $facture_pdf->factures = array($facture);
      $pdf = "";
      $pdf = $facture_pdf->editFactureBVR(false, "S");
      $file_path = tempnam("tmp", "facture");
      $file->_file_path = $file_path;
      file_put_contents($file_path, $pdf);
      $printer_bvr->loadRefSource()->sendDocument($file);
      unlink($file_path);

      $pdf = "";
      $pdf = $facture_pdf->editJustificatif(false, "S");
      $file_path = tempnam("tmp", "facture");
      $file->_file_path = $file_path;
      file_put_contents($file_path, $pdf);
      $printer_justif->loadRefSource()->sendDocument($file);
      unlink($file_path);
    }
  }
}