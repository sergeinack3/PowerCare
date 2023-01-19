<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkAdmin();

$date = CView::get("date", "date default|now", true);

CView::checkin();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("date", $date);
$smarty->display("vw_idx_identito_vigilance.tpl");
