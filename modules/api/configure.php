<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 */

use Ox\Api\CAPITiers;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceHTTP;

CCanDo::checkAdmin();
$config        = array(
  "name_api" => CAPITiers::getAPIList(),
  "url_api"  => array(
    "source_api",
    "authentification_api"
  )
);
$configuration = array();
foreach ($config["url_api"] as $value) {
  foreach ($config["name_api"] as $name) {
    $configuration[$name][$name . "_" . $value] = CExchangeSource::get($name . "_" . $value, CSourceHTTP::TYPE);
  }
}

$user   = new CUser();
$smarty = new CSmartyDP();
$smarty->assign('configuration', $configuration);
$smarty->assign('liste_apis', CAPITiers::getAPIList());
$smarty->assign('users', $user->loadList(array("is_robot" =>"='1'")));
$smarty->display("configure.tpl");
