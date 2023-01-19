<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;

CCanDo::checkEdit();

// Récuperation des utilisateurs recherchés
$filter    = CView::get("filter", "str", true);
$user_type = CView::get("user_type", "str", true);
$user_id   = CView::get("user_id", "ref class|CUser");
$template  = CView::get("template", "str", true);
$tab_name  = CView::get("tab_name", "str default|identity");
CView::checkin();

CMbArray::naturalSort(CUser::$types);

$smarty = new CSmartyDP();
$smarty->assign("template", $template);
$smarty->assign("tab_name", $tab_name);
$smarty->assign("user_type", $user_type);
$smarty->assign("user_id", $user_id);
$smarty->assign("filter", $filter);
$smarty->assign("utypes", CUser::$types);

$smarty->display("vw_edit_users");
