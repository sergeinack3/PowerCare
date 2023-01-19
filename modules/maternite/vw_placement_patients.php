<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$group = CGroups::loadCurrent();

$service              = new CService();
$service->obstetrique = 1;
$service->cancelled   = 0;
$service->group_id    = $group->_id;

$services = $service->loadMatchingList("nom");

$bloc           = new CBlocOperatoire();
$bloc->group_id = $group->_id;
$bloc->type     = "obst";
$bloc->actif    = "1";
$blocs          = $bloc->loadMatchingList("nom");

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("services"            , $services);
$smarty->assign("blocs"               , $blocs);
$smarty->display("vw_placement_patients");