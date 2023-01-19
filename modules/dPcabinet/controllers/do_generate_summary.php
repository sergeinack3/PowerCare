<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CHtmlToPDF;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesCategory;

/**
 * Creates appointments summaries which are stored in files (used by CRONs)
 */

CCanDo::check();

$model_id         = CAppUI::gconf("dPcabinet Summary model_id");
$file_category_id = CAppUI::gconf("dPcabinet Summary category_id");

$files_category = CFilesCategory::findOrFail($file_category_id);

$model = CCompteRendu::findOrFail($model_id);
$model->loadContent();
$source = $model->generateDocFromModel(null, $model->header_id, $model->footer_id);

$consultation = new CConsultation();
$ds           = $consultation->getDS();

$ljoin = [
  "plageconsult"        => "plageconsult.plageconsult_id = consultation.plageconsult_id",
  "users_mediboard"     => "users_mediboard.user_id = plageconsult.chir_id",
  "functions_mediboard" => "functions_mediboard.function_id = users_mediboard.function_id"
];
$where = [
  "plageconsult.date"            => $ds->prepare("= ?", CMbDT::date()),
  "consultation.annule"          => $ds->prepare("= ?", "0"),
  "consultation.reunion_id"      => "is null",
  "consultation.patient_id"      => "is not null",
  "functions_mediboard.group_id" => $ds->prepare("= ?", CGroups::get()->_id)
];

$consultations = $consultation->loadList($where, null, null, null, $ljoin);

$generated_files = [];
foreach ($consultations as $_consultation) {
  // Make report
  $doc_sum = new CCompteRendu();
  $doc_sum->cloneFrom($model);
  $doc_sum->setObject($_consultation);

  // Get the template
  $template_manager           = new CTemplateManager();
  $template_manager->isModele = false;
  $template_manager->document = $source;

  // Fill out the template
  $_consultation->fillTemplate($template_manager);
  $template_manager->applyTemplate($doc_sum);
  $doc_sum->_source = $template_manager->document;

  // Make the file
  $file                   = new CFile();
  $file->file_category_id = $files_category->_id;
  $file->setObject($_consultation);
  $file->file_name = $doc_sum->nom . ".pdf";
  $file->updateFormFields();
  $file->fillFields();

  // Generate the HTML structure
  $smarty = new CSmartyDP("modules/dPcompteRendu");
  $smarty->assign("content", $doc_sum->_source);

  // Convert it to a pdf
  $html_to_pdf = new CHtmlToPDF();
  $html_to_pdf->generatePDF($smarty->fetch("htmlheader.tpl"), false, $doc_sum, $file);

  // Store the file in the appointment
  $file->store();
}
