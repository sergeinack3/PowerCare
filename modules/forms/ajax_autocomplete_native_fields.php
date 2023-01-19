<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbString;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\System\Forms\CExConcept;

CCanDo::checkEdit();

$keywords = CValue::get("_native_field_view");

$list = CExConcept::getReportableFields(true);

$show_views = false;

// filtrage
if ($keywords) {
  $show_views = true;

  $re = preg_quote($keywords);
  $re = CMbString::allowDiacriticsInRegexp($re);
  $re = str_replace("/", "\\/", $re);
  $re = "/($re)/i";

  foreach ($list as $_key => $element) {
    if (!preg_match($re, $element["longview"]) && !preg_match($re, CAppUI::tr($element["class"]))) {
      unset($list[$_key]);
    }
  }
}

$smarty = new CSmartyDP();
$smarty->assign("host_fields", $list);
$smarty->assign("show_views", $show_views);
$smarty->assign("show_class", true);
$smarty->display("inc_autocomplete_native_fields.tpl");
