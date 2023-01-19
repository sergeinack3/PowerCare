<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbString;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CViewAccessToken;

CCanDo::checkEdit();

$min_validity_date = CView::get("_min_validity_date", "dateTime", true);
$max_validity_date = CView::get("_max_validity_date", "dateTime", true);
$min_usage_date    = CView::get("_min_usage_date", "dateTime", true);
$max_usage_date    = CView::get("_max_usage_date", "dateTime", true);
$actif             = CView::get("actif", "bool", true);
CView::checkin();

$token = new CViewAccessToken();

$token->_min_validity_date = $min_validity_date;
$token->_max_validity_date = $max_validity_date;
$token->_min_usage_date    = $min_usage_date;
$token->_max_usage_date    = $max_usage_date;

$modules = CModule::getInstalled();
uasort(
  $modules,
  function ($a, $b) {
    return strcmp(CMbString::removeAccents($a->_view), CMbString::removeAccents($b->_view));
  }
);

$smarty = new CSmartyDP();
$smarty->assign('token', $token);
$smarty->assign('modules', $modules);
$smarty->assign('actif', $actif);
$smarty->display("vw_edit_tokens");
