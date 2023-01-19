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

CCanDo::checkEdit();
$grossesse_id = CView::get("grossesse_id", "ref class|CGrossesse");
CView::checkin();

$grossesse = new CGrossesse();
$grossesse->load($grossesse_id);
$grossesse->loadRefParturiente();
$grossesse->loadRefGroup();
$grossesse->getDateAccouchement();
$grossesse->loadRefsNaissances();

/** @var CExamenNouveauNe[] $examens */
$examens = $grossesse->loadBackRefs("examens_nouveau_ne", "date ASC");
foreach ($examens as $examen) {
  $examen->getOEAExam();
  $examen->checkGuthrieExam();
  $naissance = $examen->loadRefNaissance();
  $sejour_enfant = $naissance->loadRefSejourEnfant();
  $enfant        = $sejour_enfant->loadRefPatient();
  $examen->getJours();
  $examen->loadRefExaminateur();
}

$smarty = new CSmartyDP();
$smarty->assign("grossesse", $grossesse);
$smarty->display("dossier_mater_examens_nouveau_ne");

