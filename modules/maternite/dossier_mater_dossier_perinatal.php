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
use Ox\Mediboard\Maternite\CDossierPerinat;
use Ox\Mediboard\Maternite\CGrossesse;

CCanDo::checkEdit();
$grossesse_id = CView::get("grossesse_id", "ref class|CGrossesse");
$operation_id = CView::get("operation_id", "ref class|COperation");
CView::checkin();

CAccessMedicalData::logAccess("COperation-$operation_id");

$grossesse = new CGrossesse();
$grossesse->load($grossesse_id);

$dossier = new CDossierPerinat();
if ($grossesse->_id) {
  $dossier = $grossesse->loadRefDossierPerinat();
  $dossier->loadEtatDossier();
  $sejour = $grossesse->loadLastSejour();
  if ($sejour && $sejour->_id) {
    $sejour->loadNDA($grossesse->group_id);
    $sejour->loadRefPraticien()->loadRefFunction();
  }
  $grossesse->countBackRefs("depistages");
  $grossesse->countBackRefs("consultations");
  $grossesse->countBackRefs("echographies");
  $grossesse->countBackRefs("sejours");
  $grossesse->countBackRefs("examens_nouveau_ne");

  // Rearrange menu for TAMM
  if (CAppUI::pref("UISTYLE") == "tamm") {
    foreach ($dossier->_listChapitres as $key_chapter => $categories) {
      if ($key_chapter == "accouchement") {
        unset($dossier->_listChapitres[$key_chapter]);
      }

      foreach ($categories as $key_category => $_category) {
        if (in_array($key_category, array("tableau_hospit_grossesse", "transfert", "cloture_sans_accouchement",
          "nouveau_ne_salle_naissance", "resume_sejour_nouveau_ne", "resume_sejour_mere"))) {
          unset($dossier->_listChapitres[$key_chapter][$key_category]);
        }
      }
    }
  }
}

if (!$operation_id) {
    $grossesse->loadLastSejour(["annule" => "= '0'"]);

    if ($grossesse->_ref_last_sejour) {
        $grossesse->_ref_last_sejour->loadRefsOperations();
        if ($grossesse->_ref_last_sejour->_ref_last_operation->_id) {
            $operation_id = $grossesse->_ref_last_sejour->_ref_last_operation->_id;
        }
    }
}

$smarty = new CSmartyDP();
$smarty->assign("grossesse", $grossesse);
$smarty->assign("operation_id", $operation_id);
$smarty->display("dossier_perinatal_menu");
