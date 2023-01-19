<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Ccam\CCodageCCAM;
use Ox\Mediboard\Ccam\CModelCodage;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\SalleOp\CActeCCAM;

CCanDo::checkEdit();

$codes_ccam         = CView::post('codes_ccam', 'str');
$chir_id            = CView::post('chir_id', 'ref class|CMediusers');
$anesth_id          = CView::post('anesth_id', 'ref class|CMediusers');
$function_id        = CView::post('function_id', 'ref class|CFunctions');
$codage_ccam_chir   = CView::post('codage_ccam_chir', 'str');
$codage_ccam_anesth = CView::post('codage_ccam_anesth', 'str');
$codage_ngap_sejour = CView::post('codage_ngap_sejour', 'str');
$model_id           = CView::post('model_id', 'num default|0');
$role               = CView::post('role', 'enum list|chir|anesth|ngap default|chir');
$object_class       = CView::post('object_class', 'enum list|CProtocole|COperation default|COperation');

CView::checkin();

$model = new CModelCodage();

if ($model_id) {
  $model->load($model_id);
  $chir_id = $model->praticien_id;
  $anesth_id = $model->anesth_id;
}
else {
  /* Get the id of an anesthesist */
  if (!$anesth_id) {
    $anesth    = new CMediusers();
    $anesths   = $anesth->loadAnesthesistes();
    $anesth    = reset($anesths);
    $anesth_id = $anesth->_id;
  }

  if ($function_id) {
    /** @var CFunctions $function */
    $function = CMbObject::loadFromGuid("CFunctions-$function_id");
    $function->loadRefsUsers();
    $user = reset($function->_ref_users);
    $chir_id = $user->_id;
  }

  $model->praticien_id = $chir_id;
  $model->anesth_id = $anesth_id;
  $model->codes_ccam = $codes_ccam;
  $model->date = CMbDT::date();

  $model->store();

  if ($role == 'chir' || $role == 'anesth') {
    /* Création des codages CCAM du chir */
    $codage                  = new CCodageCCAM();
    $codage->codable_class   = 'CModelCodage';
    $codage->codable_id      = $model->_id;
    $codage->praticien_id    = $model->praticien_id;
    $codage->activite_anesth = '0';
    $codage->date            = CMbDT::date();
    $codage->store();

    /* Création des codages CCAM de l'anesth */
    $codage                  = new CCodageCCAM();
    $codage->codable_class   = 'CModelCodage';
    $codage->codable_id      = $model->_id;
    $codage->praticien_id    = $model->anesth_id;
    $codage->activite_anesth = '0';
    $codage->date            = CMbDT::date();
    $codage->store();

    $codage_anesth                  = new CCodageCCAM();
    $codage_anesth->codable_class   = 'CModelCodage';
    $codage_anesth->codable_id      = $model->_id;
    $codage_anesth->praticien_id    = $model->anesth_id;
    $codage_anesth->activite_anesth = '1';
    $codage_anesth->date            = CMbDT::date();
    $codage_anesth->store();
  }

  if ($codage_ccam_chir) {
    $codage_ccam_chir = explode('|', $codage_ccam_chir);
    foreach ($codage_ccam_chir as $_codage) {
      $_act = new CActeCCAM();
      $_act->_adapt_object = true;
      $_act->_preserve_montant = true;
      $_act->facturable = 1;
      $_act->setFullCode($_codage);

      if ($_act->code_activite != '' && $_act->code_phase != '') {
        $_act->_calcul_montant_base = 1;
        $_act->object_id = $model->_id;
        $_act->object_class = $model->_class;
        $_act->executant_id = $model->praticien_id;
        $_act->execution = CMbDT::dateTime();
        $_act->store();
      }
    }
  }

  if ($codage_ccam_anesth) {
    $codage_ccam_anesth = explode('|', $codage_ccam_anesth);
    foreach ($codage_ccam_anesth as $_codage) {
      $_act = new CActeCCAM();
      $_act->_adapt_object = true;
      $_act->_preserve_montant = true;
      $_act->facturable = 1;
      $_act->setFullCode($_codage);

      if ($_act->code_activite != '' && $_act->code_phase != '') {
        $_act->_calcul_montant_base = 1;
        $_act->object_id = $model->_id;
        $_act->object_class = $model->_class;
        $_act->executant_id = $model->anesth_id;
        $_act->execution = CMbDT::dateTime();
        $_act->store();
      }
    }
  }

  if ($codage_ngap_sejour) {
    $codage_ngap_sejour = explode('|', $codage_ngap_sejour);
    foreach ($codage_ngap_sejour as $_codage) {
      $_act = new CActeNGAP();
      $_act->_preserve_montant = true;
      $_act->setFullCode($_codage);

      if ($_act->code != '') {
        $_act->object_id = $model->_id;
        $_act->object_class = $model->_class;
        $_act->executant_id = $model->praticien_id;
        $_act->execution = CMbDT::dateTime();
        $_act->facturable = 1;
        $_act->store();
      }
    }
  }
}

$codages = $model->loadRefsCodagesCCAM();

if ($role == 'anesth') {
  $codages = $model->_ref_codages_ccam[$model->anesth_id];
  $praticien = $model->loadRefAnesth();
}
else {
  $codages = $model->_ref_codages_ccam[$model->praticien_id];
  $praticien = $model->loadRefPraticien();
}

$praticien->loadRefFunction();
$praticien->isAnesth();

foreach ($codages as $_codage) {
  $_codage->loadPraticien()->loadRefFunction();
  $_codage->_ref_praticien->isAnesth();
  $_codage->loadActesCCAM();
  $_codage->getTarifTotal();
  $_codage->checkRules();

  foreach ($_codage->_ref_actes_ccam as $_acte) {
    $_acte->getTarif();
  }

  // Chargement du codable et des actes possibles
  $_codage->loadCodable();
  $codable = $_codage->_ref_codable;
}

$model->loadExtCodesCCAM();
$model->loadRefsActesCCAM();
$model->getActeExecution();
$model->loadRefsActesNGAP();
$model->loadRefPatient();

$smarty = new CSmartyDP();

if ($role == 'anesth') {
  $model->loadPossibleActes($anesth_id);
}
elseif ($role == 'chir') {
  $model->loadPossibleActes($chir_id);
}
elseif ($role == 'ngap') {
  $acte_ngap = CActeNGAP::createEmptyFor($model, $praticien);
  $smarty->assign('acte_ngap', $acte_ngap);
  $smarty->assign('executant_id', $praticien->_id);
  $smarty->assign('execution', $model->_acte_execution);
}

$smarty->assign('subject'     , $model);
$smarty->assign('codages'     , $codages);
$smarty->assign('praticien'   , $praticien);
$smarty->assign('role'        , $role);
$smarty->assign('object_class', $object_class);
$smarty->display('inc_protocole_coding');

