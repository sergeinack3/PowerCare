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
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Soins\CTimeUserSejour;

CCanDo::checkEdit();

$timing_id = CView::get("timing_id", "ref class|CTimeUserSejour");
CView::checkin();

$timing = new CTimeUserSejour();
$timing->load($timing_id);

if (!$timing->_id) {
  $timing->group_id = CGroups::loadCurrent()->_id;
}

$smarty = new CSmartyDP();

$smarty->assign("timing", $timing);

$smarty->display("vw_edit_timing_personnel");
