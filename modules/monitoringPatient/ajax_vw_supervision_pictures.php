<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbPath;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\MonitoringPatient\CSupervisionTimedPicture;

CCanDo::checkAdmin();
$timed_picture_id = CView::get("timed_picture_id", "ref class|CSupervisionTimedPicture");
CView::checkin();

$tree = CMbPath::getTree(CSupervisionTimedPicture::PICTURES_ROOT);

$smarty = new CSmartyDP();
$smarty->assign("tree", $tree);
$smarty->assign("timed_picture_id", $timed_picture_id);
$smarty->display("inc_vw_supervision_pictures");
