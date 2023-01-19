<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Ssr\CCategorieGroupePatient;

CCanDo::checkRead();
$show_inactive = CView::get("show_inactive", "bool default|0", true);
$date          = CView::get("day_used", "date default|now", true);
CView::checkin();

global $m;
$group = CGroups::loadCurrent();

$categories_groupe_patient = [];

$categorie_groupe_patient           = new CCategorieGroupePatient();
$categorie_groupe_patient->type     = $m;
$categorie_groupe_patient->group_id = $group->_id;
$categories_groupe_patient          = $categorie_groupe_patient->loadMatchingList("nom ASC");

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("show_inactive", $show_inactive);
$smarty->assign("categories_groupe_patient", $categories_groupe_patient);
$smarty->assign("date", $date);
$smarty->display("vw_planning_groupe_patient");
