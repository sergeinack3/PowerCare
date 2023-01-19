<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CWhiteList;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();

$page = CView::get("page", "num default|0", true);

CView::checkin();

$whitelist = new CWhiteList();

$whitelists = $whitelist->loadGroupList(null, "email", "$page,30");

$whitelist->group_id = CGroups::loadCurrent()->_id;
$total = $whitelist->countMatchingList();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("whitelists", $whitelists);
$smarty->assign("page"      , $page);
$smarty->assign("total"     , $total);

$smarty->display("inc_list_whitelists");