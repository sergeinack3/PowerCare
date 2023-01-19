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
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$patient_id     = CView::get('patient_id', 'ref class|CPatient');
$entree_prevue  = CView::get('entree_prevue', 'dateTime');
$sortie_prevue  = CView::get('sortie_prevue', 'dateTime');
$sejour_id      = CView::get('sejour_id', 'ref class|CSejour');
$group_id       = Cview::get('group_id', 'ref class|CGroups');

CView::checkin();

$sejours = array();
$collisions = array();

$sejour = new CSejour();

if ($sejour_id) {
  $sejour->load($sejour_id);
}

if ($patient_id) {
  $patient = new CPatient();
  $patient->load($patient_id);
  $sejours = $patient->loadRefsSejours();

  foreach ($sejours as $_sejour) {
    $_sejour->loadNDA();
    $_sejour->loadRefPraticien();
    $_sejour->loadRefEtablissement();
  }

  if ($entree_prevue || $sortie_prevue) {
    $sejour->patient_id = $patient_id;
    $sejour->group_id   = $group_id;

    $sejour->entree = $entree_prevue;
    $sejour->sortie = $sortie_prevue;

    $collisions = $sejour->getCollisions();

    foreach ($collisions as $_sejour) {
      $_sejour->loadNDA();
      $_sejour->loadRefPraticien();
      $_sejour->loadRefEtablissement();
    }
  }
}

$smarty = new CSmartyDP();
$smarty->assign('sejour', $sejour);
$smarty->assign('sejours', $sejours);
$smarty->assign('collisions', $collisions);
$smarty->display('dhe/inc_list_sejours');