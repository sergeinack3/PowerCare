<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Soins\CTimeUserSejour;

CCanDo::checkAdmin();

CView::checkin();

$timing  = new CTimeUserSejour();
$timings = $timing->loadGroupList(null, "name", null, "sejour_timing_id");

$smarty = new CSmartyDP();

$smarty->assign("timings", $timings);

$smarty->display("vw_timings_affectation_sejour");
