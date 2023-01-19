<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Ccam\CCodageCCAM;
use Ox\Mediboard\Ccam\CDentCCAM;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\SalleOp\CActeCCAM;

$acte_id = CView::get("acte_id" , "ref class|CActeCCAM");
CView::checkin();

$acte = new CActeCCAM();
$acte->load($acte_id);
$acte->canDo();

if (!$acte->_can->edit) {
  CAppUI::accessDenied();
}

$acte->getTarif();

// Chargement du code, de l'activité et de la phase CCAM
$code     = $acte->_ref_code_ccam;
$activite = $code->activites[$acte->code_activite];
$phase    = $activite->phases[$acte->code_phase];

$listModificateurs = $acte->modificateurs;
if (property_exists($phase, '_modificateurs')) {
  foreach ($phase->_modificateurs as $modificateur) {
    $position = strpos($listModificateurs, $modificateur->code);
    if ($position !== false) {
      if ($modificateur->_double == "1") {
        $modificateur->_checked = $modificateur->code;
      }
      elseif ($modificateur->_double == "2") {
        $modificateur->_checked = $modificateur->code . $modificateur->_double;
      }
      else {
        $modificateur->_checked = null;
      }
    }
    else {
      $modificateur->_checked = null;
    }
  }
}

/* Vérification et précodage des modificateurs */
CCodageCCAM::precodeModifiers($phase->_modificateurs, $acte, $acte->loadRefObject());
$acte->getMontantModificateurs($phase->_modificateurs);

$prescriptions = array();
if ($acte->_ref_object instanceof CConsultation && CModule::getActive("oxCabinet")) {
  $acte->loadRefsFiles();
  $prescriptions = CPrescription::loadRefsPrescriptionExternes($acte->_ref_object, null);
}

// Liste des dents CCAM
$dents = CDentCCAM::loadList();
$liste_dents = reset($dents);

// Chargement des listes de praticiens
$user = new CMediusers();
$listAnesths = $user->loadAnesthesistes(PERM_EDIT);
$listChirs   = $user->loadPraticiens(PERM_EDIT);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("acte"       , $acte);
$smarty->assign("code"       , $code);
$smarty->assign("activite"   , $activite);
$smarty->assign("phase"      , $phase);
$smarty->assign("liste_dents", $liste_dents);
$smarty->assign("listAnesths", $listAnesths);
$smarty->assign("listChirs"  , $listChirs);
$smarty->assign("prescriptions", $prescriptions);

$smarty->display("inc_edit_acte_ccam");
