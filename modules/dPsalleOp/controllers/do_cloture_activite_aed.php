<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

$object_id           = CView::post("object_id", 'ref meta|object_class');
$object_class        = CView::post("object_class", 'str');
$chir_id             = CView::post("chir_id", 'ref class|CMediusers');
$anesth_id           = CView::post("anesth_id", 'ref class|CMediusers');
$password_activite_1 = CView::post("password_activite_1", 'str');
$password_activite_4 = CView::post("password_activite_4", 'str');

CView::checkin();

/** @var COperation|CSejour $object */
$object = new $object_class;
$object->load($object_id);

if ($password_activite_1) {
  $chir = new CMediusers;
  $chir->load($chir_id);

  if (!CUser::checkPassword($chir->_user_username, $password_activite_1)) {
    CAppUI::setMsg("Mot de passe incorrect", UI_MSG_ERROR);
    echo CAppUI::getMsg();
    CApp::rip();
  }

  $object->cloture_activite_1 = 1;

  if ($msg = $object->store()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
  }
  else {
    CAppUI::setMsg("COperation-msg-modify", UI_MSG_OK);
  }
}

if ($password_activite_4) {
  $anesth = new CMediusers;
  $anesth->load($anesth_id);

  if ($anesth->_id) {
    if (!CUser::checkPassword($anesth->_user_username, $password_activite_4)) {
      CAppUI::setMsg("Mot de passe incorrect", UI_MSG_ERROR);

      echo CAppUI::getMsg();
      CApp::rip();
    }

    $object->cloture_activite_4 = 1;

    if ($msg = $object->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
    }
    else {
      CAppUI::setMsg("COperation-msg-modify", UI_MSG_OK);
    }
  }
}

// Transmission des actes CCAM
if (CAppUI::conf("dPpmsi transmission_actes") == "signature" && $object instanceof COperation && $object->testCloture()) {
  $object->loadRefs();

  $actes_ccam = $object->_ref_actes_ccam;

  foreach ($object->_ref_actes_ccam as $acte_ccam) {
    $acte_ccam->loadRefsFwd();
  }

  $sejour = $object->_ref_sejour;
  $sejour->loadRefsFwd();
  $sejour->loadNDA();
  $sejour->_ref_patient->loadIPP();

  // Facturation de l'opération
  $object->facture = 1;
  $object->loadLastLog();

  try {
    $object->store();
  } catch(CMbException $e) {
    // Cas d'erreur on repasse à 0 la facturation
    $object->facture = 0;
    $object->store();

    CAppUI::setMsg($e->getMessage(), UI_MSG_ERROR );
  }

  $object->countExchanges();

  // Flag les actes CCAM en envoyés
  foreach ($actes_ccam as $key => $_acte_ccam) {
    $_acte_ccam->sent = 1;
    if ($msg = $_acte_ccam->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
    }
  }
}

echo CAppUI::getMsg();
CApp::rip();
