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
use Ox\Mediboard\PlanningOp\CProtocoleOperatoire;

CCanDo::checkEdit();

$protocole_operatoire_id = CView::get("protocole_operatoire_id", "ref class|CProtocoleOperatoire");
$operation_id            = CView::get("operation_id", "ref class|COperation");

CView::checkin();

CAccessMedicalData::logAccess("COperation-$operation_id");

$protocole_operatoire = new CProtocoleOperatoire();
$protocole_operatoire->load($protocole_operatoire_id);

$protocole_operatoire->loadRefsMaterielsOperatoires(true);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("protocole_operatoire", $protocole_operatoire);
$smarty->assign("operation_id", $operation_id);

$smarty->display("inc_apply_protocole_op");
