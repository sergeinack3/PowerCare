<?php

/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CAutorisationPermission;

CCanDo::checkEdit();

$autorisation_permission_id = CView::get("autorisation_permission_id", "ref class|CAutorisationPermission");
$sejour_id                  = CView::get("sejour_id", "ref class|CSejour");

CView::checkin();

CAccessMedicalData::logAccess("CSejour-$sejour_id");

$autorisation_permission = new CAutorisationPermission();

if (!$autorisation_permission->load($autorisation_permission_id)) {
    $autorisation_permission->praticien_id = CMediusers::get()->_id;
    $autorisation_permission->sejour_id    = $sejour_id;
    $autorisation_permission->debut        = CMbDT::dateTime();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("autorisation_permission", $autorisation_permission);

$smarty->display("inc_edit_autorisation_permission");
