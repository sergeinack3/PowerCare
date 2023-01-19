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
use Ox\Mediboard\Maternite\CExamenNouveauNe;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();
$examen_id    = CView::get("examen_nouveau_ne_id", "ref class|CExamenNouveauNe");
$grossesse_id = CView::get("grossesse_id", "ref class|CGrossesse");
CView::checkin();

$examen = new CExamenNouveauNe();
if (!$examen->load($examen_id)) {
  $examen->grossesse_id = $grossesse_id;
}

$examen->getOEAExam();
$examen->checkGuthrieExam();

$grossesse = new CGrossesse();
$grossesse->load($grossesse_id);
$naissances = $grossesse->loadRefsNaissances();
$enfants    = array();
foreach ($naissances as $naissance) {
  $sejourEnfant             = $naissance->loadRefSejourEnfant();
  $enfant                   = $sejourEnfant->loadRefPatient();
  $enfants[$naissance->_id] = $enfant;
}

// Liste des consultants
$mediuser        = new CMediusers();
$listConsultants = $mediuser->loadProfessionnelDeSanteByPref(PERM_EDIT);

$smarty = new CSmartyDP();
$smarty->assign("examen"         , $examen);
$smarty->assign("enfants"        , $enfants);
$smarty->assign("listConsultants", $listConsultants);
$smarty->display("edit_examen_nouveau_ne");

