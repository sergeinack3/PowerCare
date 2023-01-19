<?php
/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;

/**
 * Filter functions
 */
CCanDo::checkRead();

$service             = CView::get("service", "str", true);
$web_service         = CView::get("web_service", "str", true);
$fonction            = CView::get("fonction", "str", true);
$service_demande     = CView::get("service_demande", "str");
$web_service_demande = CView::get("web_service_demande", "str");
$type                = CView::get("type", "str");

CView::checkin();

$web_services = array();
$functions    = array();
$ds = CSQLDataSource::get("std");

if ($type == "web_service") {
  $query = "SELECT web_service_name FROM echange_soap GROUP BY web_service_name";
  $web_services = CMbArray::pluck($ds->loadList($query), "web_service_name");
  foreach ($web_services as $key => $_web_service) {
    $query = "SELECT `type` FROM echange_soap WHERE `web_service_name` = '$_web_service' LIMIT 1";
    $type_web_service = CMbArray::pluck($ds->loadList($query), "type");
    if ($type_web_service[0] != $service_demande) {
      unset($web_services[$key]);
    }
  }
}
else {
  $query = "SELECT function_name FROM echange_soap GROUP BY function_name";
  $functions = CMbArray::pluck($ds->loadList($query), "function_name");
  foreach ($functions as $key => $_function) {
    $query = "SELECT `web_service_name` FROM echange_soap WHERE `function_name` = '$_function' LIMIT 1 ";
    $web_service_name = CMbArray::pluck($ds->loadList($query), "web_service_name");
    if ($web_service_name[0] != $web_service_demande) {
      unset($functions[$key]);
    }
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign('web_services', $web_services);
$smarty->assign('fonctions'   , $functions);
$smarty->assign("service"     , $service);
$smarty->assign("web_service" , $web_service);
$smarty->assign("fonction"    , $fonction);
$smarty->assign("type"        , $type);
$smarty->display("inc_filter_web_func.tpl");