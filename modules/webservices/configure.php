<?php
/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Interop\Webservices\CSourceSOAP;
use Ox\Mediboard\System\CExchangeSource;

/**
 * Configure
 */
CCanDo::checkAdmin();

// MB SOAP server
$mb_soap_server = CExchangeSource::get("mb_soap_server", CSourceSOAP::TYPE, true, null, false);
if (!$mb_soap_server->_id) {
  $mb_soap_server->host = CApp::getBaseUrl()."/index.php?login=1&username=%u&password=%p&m=webservices&a=soap_server&wsdl";
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("mb_soap_server", $mb_soap_server);
$smarty->display("configure.tpl");
