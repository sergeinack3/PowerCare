<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\PlanningOp\CMaterielOperatoire;

CCanDo::checkAdmin();

// Création de template
$smarty = new CSmartyDP();

$smarty->assign("materiel_op", new CMaterielOperatoire());

$smarty->display("vw_replace_product");
