<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CProgrammeClinique;

$programme_id = CView::get("programme_id", "ref class|CProgrammeClinique");
CView::checkin();

$user       = CMediusers::get();
$praticiens = $user->loadProfessionnelDeSanteByPref(PERM_READ, $user->function_id);

$programme = new CProgrammeClinique();
$programme->load($programme_id);

// Vérification des droits sur le programme
$programme->canDo();
if (!$programme->_can->read) {
  $programme->_can->denied();
}

if ($programme->coordinateur_id) {
  $prat_id = $programme->coordinateur_id;
}
else {
  $prat_id = $user->_id;
}

$smarty = new CSmartyDP();

$smarty->assign("programme", $programme);
$smarty->assign("praticiens", $praticiens);
$smarty->assign("prat_id", $prat_id);

$smarty->display("vw_edit_programme.tpl");
