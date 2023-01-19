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
use Ox\Mediboard\PlanningOp\CProtocoleOperatoire;

CCanDo::checkEdit();

$protocole_operatoire_id = CView::get("protocole_operatoire_id", "ref class|CProtocoleOperatoire");

CView::checkin();

$protocole_op = new CProtocoleOperatoire();
$protocole_op->load($protocole_operatoire_id);
$protocole_op->loadRefChir();
$protocole_op->loadRefFunction();
$protocole_op->loadRefGroup();
$protocole_op->loadRefsMaterielsOperatoires(true);
$protocole_op->loadRefValidationPraticien();
$protocole_op->loadRefValidationCadreBloc();

// Cr�ation du template
$smarty = new CSmartyDP();
$smarty->assign("protocole_op", $protocole_op);
$smarty->display("inc_edit_protocole_op");
