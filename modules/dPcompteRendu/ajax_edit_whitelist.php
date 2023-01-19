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

CCanDo::checkAdmin();

$whitelist_id = CView::get("whitelist_id", "ref class|CWhiteList");

CView::checkin();

$whitelist = new CWhiteList();
$whitelist->load($whitelist_id);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("whitelist", $whitelist);

$smarty->display("inc_edit_whitelist");