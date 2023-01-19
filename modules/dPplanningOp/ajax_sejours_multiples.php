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
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$type = CView::get('type', array('enum', 'list' => join('|', CSejour::$types)));

CView::checkin();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign('type', $type);
$smarty->display("inc_sejours_multiples");