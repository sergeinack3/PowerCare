<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CReleveRedon;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$sejour_id = CView::get("sejour_id", "ref class|CSejour");

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

$sejour->loadRefRedons();
$releves = [];
$qtes_for_diff = [];

foreach ($sejour->_ref_redons as $_redon) {
    $_redon->loadRefLastReleve();
    $_redon->getQteCumul();

    $releve           = new CReleveRedon();
    $releve->redon_id = $_redon->_id;
    $releve->date     = CMbDT::dateTime();
    $releve->getQteCumul();

    $releves[$_redon->_id] = $releve;

    $qtes_for_diff[$_redon->_id] = $_redon->_ref_last_releve->vidange_apres_observation ?
        0 : $_redon->_ref_last_releve->qte_observee;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejour", $sejour);
$smarty->assign("releves", $releves);
$smarty->assign("qtes_for_diff", $qtes_for_diff);

$smarty->display("inc_vw_redons");
