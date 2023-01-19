<?php
/**
 * @package Mediboard\Urgences
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

$sejour_id = CView::get("sejour_id", "ref class|CSejour", true);
$form      = CView::get("form", "str default|editSejour");
$with_form = CView::get("with_form", "bool default|0");
$size_th   = CView::get("size_th", "str");

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadExtDiagnostics();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("sejour", $sejour);
$smarty->assign("form", $form);
$smarty->assign("with_form", $with_form);
$smarty->assign("size_th", $size_th);

$smarty->display("inc_diagnostic_principal");
