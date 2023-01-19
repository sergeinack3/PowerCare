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
$supervision_timed_picture_id = CView::get("supervision_timed_picture_id", "ref class|CSupervisionTimedPicture");
CView::checkin();

$picture = new CSupervisionTimedPicture();
$picture->load($supervision_timed_picture_id);
$picture->loadRefsNotes();
$picture->loadRefsFiles();

$tree = CMbPath::getTree(CSupervisionTimedPicture::PICTURES_ROOT);

$smarty = new CSmartyDP();
$smarty->assign("picture", $picture);
$smarty->assign("tree"   , $tree);
$smarty->display("inc_edit_supervision_timed_picture");
