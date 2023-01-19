<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Maternite\CGrossesse;

CCanDo::checkEdit();
$grossesse_id       = CView::get("grossesse_id", "ref class|CGrossesse");
$operation_id       = CView::get("operation_id", "ref class|COperation");
$isDossierPerinatal = CView::get("isDossierPerinatal", "bool default|0");
CView::checkin();

$grossesse = new CGrossesse();
$grossesse->load($grossesse_id);

if (!$operation_id) {
  $grossesse->loadLastSejour();

  if ($grossesse->_ref_last_sejour) {
    $grossesse->_ref_last_sejour->loadRefsOperations();
    if ($grossesse->_ref_last_sejour->_ref_last_operation->_id) {
      $operation_id = $grossesse->_ref_last_sejour->_ref_last_operation->_id;
    }
  }
}

if (!$operation_id) {
  CAppUI::stepAjax("Aucune intervention n'est reliée à la grossesse !");

  return;
}

CAccessMedicalData::logAccess("COperation-$operation_id");

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("operation_id"      , $operation_id);
$smarty->assign("isDossierPerinatal", $isDossierPerinatal);
$smarty->display("inc_dossier_mater_graphique_sspi");