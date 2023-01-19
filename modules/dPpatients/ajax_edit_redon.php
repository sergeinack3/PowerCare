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
use Ox\Mediboard\Patients\CRedon;

CCanDo::checkEdit();

$redon_id = CView::get("redon_id", "ref class|CRedon");

CView::checkin();

$redon = new CRedon();
$redon->load($redon_id);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("redon", $redon);

$smarty->display("inc_edit_redon");