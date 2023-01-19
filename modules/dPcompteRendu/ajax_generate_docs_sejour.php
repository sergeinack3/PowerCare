<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\Sessions\CSessionHandler;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Génération en masse de documents pour un séjour
 */
CCanDo::checkRead();

CApp::setTimeLimit(300);
CApp::setMemoryLimit("2048M");

// On libère la session afin de ne pas bloquer l'utilisateur
CSessionHandler::writeClose();

CView::enableSlave();

$modele_id   = CValue::post("modele_id");
$sejours_ids = CValue::post("sejours_ids");

// Chargement des séjours
$sejour = new CSejour();

$where = array();
$where["sejour_id"] = "IN ($sejours_ids)";

$sejours = $sejour->loadList($where);
/** @var CPatient[] $patients */
$patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
CStoredObject::massLoadFwdRef($sejours, "praticien_id");

/** @var $sejours CSejour[] */
foreach ($sejours as $_sejour) {
  $_sejour->loadRefPatient();
  $_sejour->loadRefPraticien();
}

CSejour::massLoadNDA($sejours);
CPatient::massLoadIPP($patients);

CStoredObject::massCountBackRefs($sejours, "affectations");
CStoredObject::massCountBackRefs($sejours, "consultations");
CStoredObject::massCountBackRefs($sejours, "files");

// Tri par nom de patient
CMbArray::pluckSort($sejours, SORT_ASC, "_ref_patient", "nom");

// Chargement du modèle
$modele = new CCompteRendu();
$modele->load($modele_id);
$modele->loadContent();

$source = $modele->generateDocFromModel();

$nbDoc = array();

foreach ($sejours as $_sejour) {
  $compte_rendu = new CCompteRendu();
  $compte_rendu->cloneFrom($modele);
  $compte_rendu->setObject($_sejour);
  $compte_rendu->_id = "";
  $compte_rendu->content_id = "";
  $compte_rendu->user_id = "";
  $compte_rendu->function_id = "";
  $compte_rendu->group_id = "";
  $compte_rendu->modele_id = $modele->_id;
  $compte_rendu->_source = $source;

  $templateManager = new CTemplateManager();
  $templateManager->isModele = false;
  $templateManager->document = $source;

  CView::enableSlave();
  $_sejour->fillTemplate($templateManager);
  $templateManager->applyTemplate($compte_rendu);
  CView::disableSlave();

  $compte_rendu->_source = $templateManager->document;

  if ($msg = $compte_rendu->store()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
    continue;
  }
  $nbDoc[$compte_rendu->_id] = 1;
}

echo CApp::fetch("dPcompteRendu", "print_docs", array("nbDoc" => $nbDoc));

CApp::$callbacks = array();