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
use Ox\Mediboard\Maternite\CGrossesse;

CCanDo::checkEdit();
$grossesse_id = CView::get("grossesse_id", "ref class|CGrossesse");
CView::checkin();

$grossesse = new CGrossesse();
$grossesse->load($grossesse_id);
$grossesse->loadRefGroup();
$grossesse->getDateAccouchement();
$grossesse->loadRefsNaissances();
$grossesse->loadRefsNotes();

$sejour = $grossesse->loadLastSejour();
if ($sejour && $sejour->_id) {
    $sejour->loadNDA($grossesse->group_id);
    $sejour->loadRefPraticien()->loadRefFunction();
}

$patient = $grossesse->loadRefParturiente();
if ($patient && $patient->_id) {
    $patient->loadIPP($grossesse->group_id);
    $patient->loadRefsCorrespondants();
    $patient->loadRefsCorrespondantsPatient();
    $patient_insnir = $patient->loadRefPatientINSNIR();
    $patient_insnir->createDatamatrix($patient_insnir->createDataForDatamatrix());
}

$dossier = $grossesse->loadRefDossierPerinat();

$smarty = new CSmartyDP();
$smarty->assign("grossesse", $grossesse);
$smarty->display("dossier_mater_identification");
