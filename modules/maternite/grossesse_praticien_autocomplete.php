<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$praticien_str = CView::get("_prat_autocomplete", "str");
CView::checkin();

CView::enableSlave();

$ljoin = [
  "users" => "users.user_id = mediusers.user_id",
];
$where = [
  "spec_cpam_id" => CSQLDataSource::prepareIn([7, 70, 79]),
];

$mediuser = new CMediusers();
$matches  = $mediuser->getAutocompleteList($praticien_str, $where, 200, $ljoin);
foreach ($matches as $_match) {
  $_match->loadView();
}

$template = $mediuser->getTypedTemplate("autocomplete");

$smarty = new CSmartyDP("modules/system");

$smarty->assign("view_field", true);
$smarty->assign("matches", $matches);
$smarty->assign("nodebug", true);
$smarty->assign("field", null);
$smarty->assign("template", $template);
$smarty->assign("show_view", true);

$smarty->display("inc_field_autocomplete");
