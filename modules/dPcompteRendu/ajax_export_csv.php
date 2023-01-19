<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\CompteRendu\CCompteRendu;

CCanDo::checkRead();
$models_ids = CView::get("model_ids", ["str"]);
CView::checkin();

$csv = new CCSVFile(null, CCSVFile::PROFILE_EXCEL);
$csv->setColumnNames(
  [
    "object_class",
    "etat_envoi",
    "private",
    "annule",
    "doc_size",
    "remis_patient",
    "nom",
    "actif",
    "type",
    "factory",
    "language",
    "signature_mandatory",
    "alert_creation",
    "margin_top",
    "margin_bottom",
    "margin_left",
    "margin_right",
    "page_height",
    "page_width",
    "fast_edit",
    "fast_edit_pdf",
    "purgeable",
    "fields_missing",
    "version",
    "_source",
    "listes"
  ]
);

foreach ($models_ids as $_id) {
  $model = CCompteRendu::findOrFail($_id);
  $model->loadContent();
  $csv->writeLine(
    [
      $model->object_class,
      $model->etat_envoi,
      $model->private,
      $model->annule,
      $model->doc_size,
      $model->remis_patient,
      $model->nom,
      $model->actif,
      $model->type,
      $model->factory,
      $model->language,
      $model->signature_mandatory,
      $model->alert_creation,
      $model->margin_top,
      $model->margin_bottom,
      $model->margin_left,
      $model->margin_right,
      $model->page_height,
      $model->page_width,
      $model->fast_edit,
      $model->fast_edit_pdf,
      $model->purgeable,
      $model->fields_missing,
      $model->version,
      $model->_source,
      $model->_list_classes
    ]
  );
}

$csv->stream("export_models_".CMbDT::date());

CApp::rip();
