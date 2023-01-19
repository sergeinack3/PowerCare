<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Import\CMbObjectExport;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Ccam\CActe;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CWkhtmlToPDF;
use Ox\Mediboard\Facturation\CFacture;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$patient_id = CView::get('patient_id', 'ref class|CPatient notNull');

CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);

if (!$patient->getPerm(PERM_READ)) {
  CAppUI::stepAjax('CPatient-export-file-permission-denied', UI_MSG_ERROR);
}

$zip_name = "export_{$patient->nom}_{$patient->prenom}_{$patient->naissance}";
$path     = rtrim(CAppUI::conf('root_dir'), '/\\') . "/tmp/$zip_name";

$zip = new ZipArchive();
$zip->open($path . '.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

$time_line_query = array(
  'timeline' => array(
    "m"          => "oxCabinet",
    "dialog"     => "patient_timeline",
    "patient_id" => $patient_id,
    "print"      => 1,
  ),
);

$zip->addFromString("timeline.pdf", CWkhtmlToPDF::makePDF(null, null, $time_line_query, "A4", "Portrait", "screen", false));

$synthese_query = array(
  'synthese' => array(
    'm'          => 'oxCabinet',
    'dialog'     => 'vw_synthese_medicale',
    'patient_id' => $patient_id,
  ),
);

$zip->addFromString("synthese.pdf", CWkhtmlToPDF::makePDF(null, null, $synthese_query, "A4", "Portrait", "screen", false));

// Documents du patient
$patient->loadRefsDocs();
foreach ($patient->_ref_documents as $_doc) {
  $_doc->date_print = CMbDT::dateTime();
  $_doc->store();
  $_doc->makePDFpreview(1);

  $zip->addFile($_doc->_ref_file->_file_path, "patient/$_doc->nom.pdf");
}

// Fichiers du patient
$patient->loadRefsFiles();
foreach ($patient->_ref_files as $_file) {
  if (!file_exists($_file->_file_path)) {
    continue;
  }

  $zip->addFile($_file->_file_path, "patient/{$_file->file_name}");
}

// Documents et fichiers des consultations
foreach ($patient->loadRefsConsultations() as $_consult) {
  $_consult->loadRefsDocs();
  foreach ($_consult->_ref_documents as $_doc) {

    $_doc->date_print = CMbDT::dateTime();
    $_doc->store();
    $_doc->makePDFpreview(1);

    $zip->addFile($_doc->_ref_file->_file_path, "consultations/$_doc->nom.pdf");
  }

  $_consult->loadRefsFiles();

  foreach ($_consult->_ref_files as $_file) {
    if (!file_exists($_file->_file_path)) {
      continue;
    }

    $zip->addFile($_file->_file_path, "consultations/{$_file->file_name}");
  }

  $prescriptions = $_consult->loadRefsPrescriptions();

  if (is_array($prescriptions)) {
    foreach ($prescriptions as $_prescription) {
      $_prescription->loadRefsFiles();

      foreach ($_prescription->_ref_files as $_file) {
        $zip->addFile($_file->_file_path, "prescriptions/{$_file->file_name}");
      }
    }
  }
}

// Patient XML
$user_id  = array(CMediusers::get()->_id);
$callback = function (CStoredObject $object) use ($user_id) {
  if ($object instanceof CPlageconsult && !in_array($object->chir_id, $user_id)) {
    return false;
  }

  if ($object instanceof CSejour && !in_array($object->praticien_id, $user_id)) {
    return false;
  }

  if ($object instanceof COperation && !in_array($object->chir_id, $user_id)) {
    return false;
  }

  if ($object instanceof CFacture && !in_array($object->praticien_id, $user_id)) {
    return false;
  }

  if ($object instanceof CActe && !in_array($object->executant_id, $user_id)) {
    return false;
  }

  if ($object instanceof CFile || $object instanceof CCompteRendu) {
    return false;
  }

  return true;
};

$xml_export = new CMbObjectExport($patient, CMbObjectExport::DEFAULT_BACKREFS_TREE);
$xml_export->setForwardRefsTree(CMbObjectExport::DEFAULT_FWREFS_TREE);
$xml_export->setFilterCallback($callback);

$zip->addFromString('export.xml', $xml_export->toDOM()->saveXML());

$zip->close();

header("Content-Description: File Transfert");
header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=$zip_name.zip");
header("Content-Transfert-Encoding: binary");
header("Content-Length: " . filesize($path . '.zip'));
ob_end_flush();

$fp = fopen($path . '.zip', 'r');
while ($content = fread($fp, 1024 * 1024)) {
  echo $content;
}

fclose($fp);

unlink($path . '.zip');
