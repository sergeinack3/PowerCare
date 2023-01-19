<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use DOMNode;
use Ox\Interop\Webservices\CSoapHandler;

/**
 * Class CWSDL
 * Web Services Description Language
 */
class CWSDL extends CMbXMLDocument {
  /** @var string WSDL name */
  private $name;

  /**
   * @var array
   */
  public $xsd = array(
    "string"       => "string",
    "bool"         => "boolean",
    "boolean"      => "boolean",
    "int"          => "integer",
    "integer"      => "integer",
    "double"       => "double",
    "float"        => "float",
    "number"       => "float",
    "resource"     => "anyType",
    "mixed"        => "anyType",
    "unknown_type" => "anyType",
    "anyType"      => "anyType",
    "xml"          => "anyType",
  );

  /**
   * @var CSoapHandler
   */
  public $_soap_handler;

  /**
   * Construct
   *
   * @return CWSDL
   */
  function __construct() {
    parent::__construct();
    $this->documentfilename = "tmp/document.wsdl";
    $this->addComment($this, "WSDL Mediboard genere permettant de decrire le service web.");
    $this->addComment($this, "Partie 1 : Definitions");
    $definitions = $this->addElement($this, "definitions", null, "http://schemas.xmlsoap.org/wsdl/");
    $this->addNameSpaces($definitions);
  }

  /**
   * Set the WSDL name
   *
   * @param string $name
   *
   * @return void
   */
  public function setName(string $name) {
    $this->name = $name;
  }

  /**
   * Get the WSDL name
   *
   * @return string|null
   */
  public function getName(): ?string {
    return $this->name;
  }

  /**
   * Add namespaces
   *
   * @param DOMNode $elParent Parent element
   *
   * @return void
   */
  function addNameSpaces(DOMNode $elParent) {
    // Ajout des namespace
    $this->addAttribute($elParent, "name", "MediboardWSDL");
    $this->addAttribute($elParent, "targetNamespace", "http://soap.mediboard.org/wsdl/");
    $this->addAttribute($elParent, "xmlns:typens", "http://soap.mediboard.org/wsdl/");
    $this->addAttribute($elParent, "xmlns:xsd", "http://www.w3.org/2001/XMLSchema");
    $this->addAttribute($elParent, "xmlns:soap", "http://schemas.xmlsoap.org/wsdl/soap/");
    $this->addAttribute($elParent, "xmlns:soapenc", "http://schemas.xmlsoap.org/soap/encoding/");
    $this->addAttribute($elParent, "xmlns:wsdl", "http://schemas.xmlsoap.org/wsdl/");
  }

  /**
   * Add text
   *
   * @param DOMNode $elParent  Parent element
   * @param string  $elName    Element name
   * @param string  $elValue   The value of the element.
   * @param int     $elMaxSize Maximum size
   *
   * @return mixed
   */
  function addTexte($elParent, $elName, $elValue, $elMaxSize = 100) {
    $elValue = substr($elValue, 0, $elMaxSize);

    return $this->addElement($elParent, $elName, $elValue);
  }

  /**
   * Add service
   *
   * @param string $login     Login
   * @param string $token     Token
   * @param string $module    Module name
   * @param string $tab       Tab name
   * @param string $classname Class name
   * @param string $wsdl_mode The WSDL mode (CWSDLRPCEncoded or CWSDLRPCLiteral)
   *
   * @return void
   */
  function addService($login, $token, $module, $tab, $classname, $wsdl_mode) {
    $definitions = $this->documentElement;
    $this->addComment($definitions, "Partie 7 : Service");

    $service = $this->addElement($definitions, "service");
    $this->addAttribute($service, "name", "MediboardService");

    $this->addTexte($service, "documentation", "Documentation du WebService");

    $partie8 = $this->createComment("partie 8 : Port");
    $service->appendChild($partie8);
    $port = $this->addElement($service, "port");
    $this->addAttribute($port, "name", "MediboardPort");
    $this->addAttribute($port, "binding", "typens:MediboardBinding");

    $soapaddress = $this->addElement($port, "soap:address", null, "http://schemas.xmlsoap.org/wsdl/soap/");

    $authentification = $token ? "token=$token" : $login;
    $url              = "/?$authentification&m=$module&a=$tab&class=$classname&wsdl_mode=$wsdl_mode&suppressHeaders=1";

    $base_url = CAppUI::conf("webservices wsdl_root_url");
    $this->addAttribute($soapaddress, "location", $base_url ? $base_url . $url : CApp::getBaseUrl() . $url);
  }

  /**
   * Generate WSDL
   *
   * @param string $login     Login
   * @param string $token     Token
   * @param string $module    Module name
   * @param string $tab       Tab name
   * @param string $classname Class name
   * @param string $wsdl_mode The WSDL mode (CWSDLRPCEncoded or CWSDLRPCLiteral)
   *
   * @return string XML document
   */
  static function generateWSDL($login, $token, $module, $tab, $classname, $wsdl_mode) {
    $soap_server_local_file = CAppUI::getTmpPath("soap_server");
    CMbPath::forceDir($soap_server_local_file);

    $username = null;

    // login with "login=user:password"
    if (strpos($login, ":") !== false) {
      list($username, $password) = explode(":", $login, 2);
    }

    $filename               = $token ? $token : $username;
    $soap_server_local_file .= "/local_file_$classname" . "_$filename.xml";
    if (!file_exists($soap_server_local_file)) {
      $wsdlFile = new $wsdl_mode;
      // Pour garder en référence les fonctions a decrire
      $wsdlFile->_soap_handler = new $classname;
      $wsdlFile->addTypes();
      $wsdlFile->addMessage();
      $wsdlFile->addPortType();
      $wsdlFile->addBinding();
      $wsdlFile->addService($login, $token, $module, $tab, $classname, $wsdl_mode);

      file_put_contents($soap_server_local_file, $wsdlFile->saveXML());
    }

    return file_get_contents($soap_server_local_file);
  }
}
