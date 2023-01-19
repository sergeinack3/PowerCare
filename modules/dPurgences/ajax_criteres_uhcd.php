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
use Ox\Mediboard\Urgences\CRPU;

CCanDo::checkEdit();

$rpu_id = CView::get("rpu_id", "ref class|CRPU");

CView::checkin();

$rpu = new CRPU();
$rpu->load($rpu_id);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("rpu", $rpu);

$smarty->display("inc_criteres_uhcd");
