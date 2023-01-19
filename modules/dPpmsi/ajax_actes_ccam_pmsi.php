<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Ccam\CModelCodage;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();
$sejour_id   = CView::get("sejour_id", "ref class|CSejour", true);
$rum_id      = CView::get("rum_id", "ref class|CRUM");
$model_id    = CView::get("model_id", "ref class|CModelCodage");
$subject_id  = CView::post("subject_id", "ref class|CModelCodage");
$rum_id_post = CView::post("rum_id_post", "ref class|CRUM");
CView::checkin();

if ($subject_id) {
  $model_id = $subject_id;
}

if ($rum_id_post) {
  $rum_id = $rum_id_post;
}

$operation = new COperation();

$codages       = array();
$codes_ccam    = array();
$codes_ccam_op = array();

$model = new CModelCodage();
$model->load($model_id);

$model->loadRefsCodagesCCAM();
$codages = $model->_ref_codages_ccam[$model->praticien_id];

foreach ($codages as $codage) {
  $codage->loadPraticien()->loadRefFunction();
  $codage->_ref_praticien->isAnesth();
  $codage->loadActesCCAM();
  $codage->getTarifTotal();
  $codage->checkRules();

  foreach ($codage->_ref_actes_ccam as $_acte) {
    $_acte->getTarif();
  }

  // Chargement du codable et des actes possibles
  $codage->loadCodable();
  $codable = $codage->_ref_codable;
}

$model->loadRefsCodagesCCAM();
$model->loadExtCodesCCAM();
$model->loadRefsActesCCAM();
$model->getActeExecution();
$model->loadPossibleActes($model->praticien_id);

foreach ($model->_ext_codes_ccam as $_code) {
  foreach ($_code->activites as $activite) {
    foreach ($activite->phases as $phase) {
      $phase->_connected_acte->loadRefCodageCCAM();
    }
  }
}

$sejour = CSejour::find($sejour_id);

CAccessMedicalData::logAccess($sejour);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("subject"  , $model);
$smarty->assign("operation", $operation);
$smarty->assign("sejour"   , $sejour);
$smarty->assign("rum_id"   , $rum_id);
$smarty->assign("codages"  , $codages);
$smarty->display("inc_actes_ccam_pmsi");
