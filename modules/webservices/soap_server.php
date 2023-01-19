<?php
/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Interop\Webservices\Wsdl\WSDLFactory;
use Ox\Interop\Webservices\Wsdl\WSDLRepository;

/**
 * SOAP Server EAI
 */
global $m, $a;

CCanDo::checkRead();

$wsdl      = CView::request('wsdl', "str");
$login     = CView::request('login', 'str');
$username  = CView::request('username', 'str');
$password  = CView::request('password', 'str');
$token     = CView::request('token', 'str');
$classname = CView::request('class', 'str default|CEAISoapHandler');
$wsdl_mode = CView::request('wsdl_mode', 'str default|CWSDLRPCEncoded');
$encoding  = CView::request('encoding', 'str');
CView::checkin();

if ($username && $password) {
  $login = "login=$username:$password";
}

// login with "login=user:password"
if (strpos($login, ":") !== false) {
  list($username, $password) = explode(":", $login, 2);
}

$wsdl_repo = new WSDLRepository();
// Génération du fichier WSDL
if (isset($wsdl)) {
  if (!$classname || !class_exists($classname, true)) {
    return;
  }

  header('Content-Type: application/xml; charset=iso-8859-1');
  $wsdl_object = $wsdl_repo->find($login, $token, $m, $a, $classname, $wsdl_mode);
  if (!$wsdl_object) {
    $wsdl_object = WSDLFactory::create($login, $token, $m, $a, $classname, $wsdl_mode);
    $wsdl_repo->save($wsdl_object);
  }

  echo $wsdl_object->saveXML();

  return;
}

if (!$classname || !class_exists($classname, true)) {
  throw new SoapFault("1", "Error : classname is not valid");
}

// on indique au serveur à quel fichier de description il est lié
try {
  $base_url = CAppUI::conf("webservices wsdl_root_url");
  $base = $base_url ? $base_url : CApp::getBaseUrl();

  $authentification = $token ? "token=$token" : $login;
  $wsdl_path = $base."/?$authentification&m=$m&a=$a&class=$classname&wsdl";

  $serverSOAP = new SoapServer(
    $wsdl_path,
    array("encoding" => $encoding ? $encoding : CAppUI::conf("webservices soap_server_encoding"))
  );
}
catch (Exception $e) {
  echo $e->getMessage();
}

$serverSOAP->setClass($classname);

// Lance le serveur
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // start output buffering
  ob_end_flush();
  ob_start();

  // finaly call SoapServer::handle() - store result
  $result = $serverSOAP->handle();

  // flush buffer
  ob_flush();
}
else {
  echo '<strong>Le serveur SOAP Mediboard peut gérer les fonctions suivantes : </strong>';
  echo '<ul>';
  foreach ($serverSOAP->getFunctions() as $_function) {
    echo '<li>', $_function, '</li>';
  }
  echo '</ul>';
}
