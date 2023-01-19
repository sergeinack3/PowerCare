<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\CPreferences;

CCanDo::checkEdit();

$pref_id = CView::get("pref_id", "ref class|CPreferences");

CView::checkin();

$preference = new CPreferences();
$preference->load($pref_id);
$preference->loadRefsNotes();

$smarty = new CSmartyDP();
$smarty->assign("preference", $preference);
$smarty->display("inc_edit_pref.tpl");
