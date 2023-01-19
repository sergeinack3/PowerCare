<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbPDFMerger;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$sejour_id         = CView::get("sejour_id", "ref class|CSejour");
$date_min          = CView::get("date_min", "date");
$date_max          = CView::get("date_max", "date");
$checkbox_selected = CView::get("checkbox_selected", "str");

CView::checkin();

$checkbox_selected = stripslashes($checkbox_selected);

$pdf = new CMbPDFMerger();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

// PDF du dossier de soins
$pdf_content = $sejour->getPrintDossierSoins(1, $date_min, $date_max, $checkbox_selected);

$file_path = tempnam("./tmp", "dossier_soins");
file_put_contents($file_path, $pdf_content);

$pdf->addPDF($file_path);

unlink($file_path);

// PDFs des items documentaires
$contexts = [$sejour->loadRefPatient(), $sejour];
$where_op = [];
$where_op["annulee"] = "= '0'";
if ($date_min && $date_max) {
    $where_op["date"] = "BETWEEN '$date_min' AND '$date_max'";
} elseif ($date_min) {
    $where_op["date"] = ">= '$date_min'";
} elseif ($date_max) {
    $where_op["date"] = "<= '$date_max'";
}
foreach ($sejour->loadRefsOperations($where_op) as $_op) {
    $contexts[] = $_op;

    if ($_op->loadRefsConsultAnesth()->_id) {
        $contexts[] = $_op->_ref_consult_anesth;
    }
}

foreach ($contexts as $_context) {
    $_context->loadRefsDocs();

    foreach ($_context->_ref_documents as $_doc) {
        $_doc->date_print = CMbDT::dateTime();
        $_doc->store();
        $_doc->makePDFpreview(1);

        $pdf->addPDF($_doc->_ref_file->_file_path);
    }

    // Ajout des CFile si de type pdf ou si la conversion PDF est activée et possible
    $_context->loadRefsFiles();
    foreach ($_context->_ref_files as $_file) {
        $file_path_pdf = strpos($_file->file_type, "pdf") ? $_file->_file_path : null;

        if (!$file_path_pdf && $_file->isPDFconvertible() && $_file->convertToPDF()) {
            $file_path_pdf = $_file->loadPDFconverted()->_file_path;
        }

        if ($file_path_pdf) {
            $pdf->addPDF($file_path_pdf);
        }
    }
}

// Stream au navigateur
try {
    $pdf->merge("browser", "documents.pdf");
} catch (Exception $e) {
    CAppUI::stepAjax(utf8_encode("Aucun PDF à générer"));
    CApp::rip();
}
