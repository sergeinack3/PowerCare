<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CPatientReunion;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CTemplateManager;

CCanDo::checkEdit();

$tokens_patient_meeting = CView::post("patient_meeting", "str");
$model_id               = CView::post("model_id", "num default|0");

CView::checkin();

$patient_meeting_ids = explode(',', $tokens_patient_meeting);

// Get the patients for a meeting
$patient_meeting = new CPatientReunion();
$patients_meeting = $patient_meeting->loadList(array("patient_reunion_id" => CSQLDataSource::prepareIn($patient_meeting_ids)));

foreach ($patients_meeting as $_patient_meeting) {
  $model_to_use = ($model_id > 0) ? $model_id : $_patient_meeting->model_id;

  $model = new CCompteRendu();
  $model->load($model_to_use);
  $model->loadContent();

  $source = $model->generateDocFromModel();

  // Affect the source to the template manager
  $templateManager           = new CTemplateManager();
  $templateManager->isModele = false;
  $templateManager->document = $source;

  // Create the document that will be soon generated
  // The document must be directly related to the model and an object (Patient meeting)
  // Init the other variables (this part was copied from ajax_generate_docs_sejour)
  $cr = new CCompteRendu();
  $cr->cloneFrom($model);
  $cr->setObject($_patient_meeting);
  $cr->_id         = "";
  $cr->content_id  = "";
  $cr->user_id     = "";
  $cr->function_id = "";
  $cr->group_id    = "";
  $cr->_source     = $source;

  // Fill the template of the patient meeting object with the template manager and apply it
  $_patient_meeting->fillTemplate($templateManager);
  $templateManager->applyTemplate($cr);

  $cr->_source = $templateManager->document;

  // Save the generated file
  if ($msg = $cr->store()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
    echo CAppUI::getMsg();
    CApp::rip();
  }
}

CAppUI::setMsg("document-saved", UI_MSG_OK);
echo CAppUI::getMsg();
