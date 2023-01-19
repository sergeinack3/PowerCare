<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$sejour_id   = CView::get("sejour_id", "ref class|CSejour");
$class_redon = CView::get("class_redon", "str");

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);
$sejour->loadRefRedons();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejour", $sejour);
$smarty->assign("class_redon", $class_redon);

$smarty->display("inc_select_redons");
