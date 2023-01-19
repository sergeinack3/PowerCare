<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Maternite\CConsultationPostNatEnfant;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();
$grossesse_id = CView::get("grossesse_id", "ref class|CGrossesse");
$print        = CView::get("print", "bool default|0");
CView::checkin();

$grossesse = new CGrossesse();
$grossesse->load($grossesse_id);
$patient = $grossesse->loadRefParturiente();
$grossesse->loadRefsSejours();
$grossesse->loadRefGroup();
$grossesse->getDateAccouchement();
$grossesse->loadRefsNaissances();

$dossier = $grossesse->loadRefDossierPerinat();
$dossier->loadRefsConsultationsPostNatale();
foreach ($dossier->_ref_consultations_post_natale as $consult) {
  $consult->loadRefConsultant();
  $consult->loadRefConstantesMaternelles();
  $consult->loadRefsConsultEnfants();
}

$naissances = $grossesse->loadRefsNaissances();
foreach ($naissances as $naissance) {
  $sejour = $naissance->loadRefSejourEnfant();
  $sejour->loadRefPatient();
}

// Liste des consultants
$mediuser        = new CMediusers();
$listConsultants = $mediuser->loadProfessionnelDeSanteByPref(PERM_EDIT);

$smarty = new CSmartyDP();
$smarty->assign("grossesse", $grossesse);
$smarty->assign("listConsultants", $listConsultants);
$smarty->assign("print", $print);
$smarty->assign("empty_postnat_enfant", new CConsultationPostNatEnfant());
$smarty->display("dossier_mater_consult_postnatale.tpl");
