<?php

/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$sejour_id = CView::get("sejour_id", "ref class|CSejour");
CView::checkin();

$sejour = new CSejour();
if ($sejour_id) {
    $sejour->load($sejour_id);

    CAccessMedicalData::logAccess($sejour);

    $sejour->getPerm(PERM_READ);
    $sejour->loadRefsFwd();
    $praticien =& $sejour->_ref_praticien;
    $patient =& $sejour->_ref_patient;
    $patient->loadRefsSejours();
    $sejours =& $patient->_ref_sejours;
    $sejour->loadRefsOperations();
    foreach ($sejour->_ref_operations as $_operation) {
        $_operation->loadRefsFwd();
        $_operation->_ref_chir->loadRefFunction();
        $_operation->loadRefChir2()->loadRefFunction();
        $_operation->loadRefChir3()->loadRefFunction();
        $_operation->loadRefChir4()->loadRefFunction();
        $_operation->loadRefBrancardage();
        $_operation->loadRefVisiteAnesth()->loadRefFunction();
        $_operation->loadRefSBrancardages();
    }
    $sejour->loadRefsConsultAnesth();
    $sejour->_ref_consult_anesth->loadRefConsultation();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("sejour", $sejour);
$smarty->display("inc_info_list_operations");
