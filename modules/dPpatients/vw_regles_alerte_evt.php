<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

//Chargement des règles
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CRegleAlertePatient;

$group_id = CGroups::loadCurrent()->_id;

$functions = $user->loadFonctions(PERM_EDIT, $group_id);
$functions_ids = array_keys($functions);
$where = array(" group_id = '$group_id' OR function_id ".CSQLDataSource::prepareIn($functions_ids, $function_id));
$regle  = new CRegleAlertePatient();
$regles = $regle->loadList($where);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("regles", $regles);

$smarty->display("vw_regles_alerte_evt");
