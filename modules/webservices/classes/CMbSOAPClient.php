<?php
/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Webservices;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CHTTPClient;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CMbPath;
use Ox\Core\CMbXMLDocument;
use Ox\Core\CMbXPath;
use Ox\Interop\Eai\CHTTPTunnelObject;
use Ox\Mediboard\System\CExchangeSource;
use SoapClient;
use SoapFault;
use SoapHeader;

if (!class_exists("SoapClient")) {
  return;
}

/**
 * The CMbSOAPClient class
 */
class CMbSOAPClient extends SoapClient implements IShortNameAutoloadable {
  public $wsdl_url;
  public $type_echange_soap;
  public $soap_client_error = false;
  public $flatten;
  public $loggable;
  public $encoding;
  public $options;
  public $return_mode;
  public $xop_mode;
  public $response_body;
  public $ca_info;
  public $local_cert;
  public $passphrase;
  public $wsdl_original;
  public $check_option;
  public $use_tunnel;

    /** @var CSourceSOAP */
    protected $source;

  /** @var array An array of prefix => namespaces uri */
  protected $namespaces = array();

  /** @var string Response file path, when in "request to file mode" */
  public $response_file;

  /**
   * The constructor
   *
   * @param string  $rooturl            The URL of the wsdl file
   * @param string  $type               The type of exchange
   * @param array   $options            An array of options
   * @param boolean $loggable           True if you want to log all the exchanges with the web service
   * @param string  $local_cert         Path of the certifacte
   * @param string  $passphrase         Pass phrase for the certificate
   * @param bool    $safe_mode          Safe mode
   * @param boolean $verify_peer        Require verification of SSL certificate used
   * @param string  $cafile             Location of Certificate Authority file on local filesystem
   * @param String  $wsdl_external      Location of external wsdl
   * @param int     $socket_timeout     Default timeout (in seconds) for socket based streams
   * @param int     $connection_timeout Default timeout (in seconds) for connection
   * @param string  $location_for_port  Location for port
   *
   * @throws CMbException
   */
  function __construct(
      $rooturl, $type = null, $options = array(), $loggable = null, $local_cert = null, $passphrase = null, $safe_mode = false,
      $verify_peer = false, $cafile = null, $wsdl_external = null, $socket_timeout = null, $connection_timeout = null,
      $location_for_port = null
  ) {

    $this->return_mode = CMbArray::extract($options, "return_mode", "normal");
    $this->xop_mode    = CMbArray::extract($options, "xop_mode",    false);
    $this->use_tunnel  = CMbArray::extract($options, "use_tunnel",  false);

    $this->wsdl_url = $rooturl;

    if ($loggable) {
      $this->loggable = $loggable;
    }

    if ($type) {
      $this->type_echange_soap = $type;
    }

    $login    = CMbArray::get($options, "login");
    $password = CMbArray::get($options, "password");
    $check_option["local_cert"]  = $local_cert;
    $check_option["ca_cert"]     = $cafile;
    $check_option["passphrase"]  = $passphrase;
    $check_option["username"]    = $login;
    $check_option["password"]    = $password;
    $check_option['verify_peer'] = $verify_peer;
    $this->check_option = $check_option;

    if (!$safe_mode) {
      if (!$html = CHTTPClient::checkUrl($this->wsdl_url, $this->check_option, true)) {
        $this->soap_client_error = true;
        throw new CMbException("CSourceSOAP-unable-to-parse-url", $this->wsdl_url);
      }
      if (strpos($html, "<?xml") === false) {
        $this->soap_client_error = true;
        throw new CMbException("CSourceSOAP-wsdl-invalid");
      }
    }

    
    // Ajout des options personnalisées
    $connection_timeout = $connection_timeout ? $connection_timeout : CAppUI::conf("webservices connection_timeout");

    $options = array_merge($options, array("connection_timeout" => $connection_timeout));
    if (CAppUI::conf("webservices trace")) {
      $options = array_merge($options, array("trace" => true));
    }

    // Authentification HTTP
    if ($local_cert) {
      $this->local_cert = $local_cert;
      $options = array_merge($options, array("local_cert" => $local_cert));
    }
    if ($passphrase) {
      $this->passphrase = $passphrase;
      $options = array_merge($options, array("passphrase" => $passphrase));
    }

    if (array_key_exists('stream_context', $options)) {
      $context = $options['stream_context'];
    }
    else {
      $context = stream_context_create();
    }

    stream_context_set_option($context, "ssl", "verify_peer", $verify_peer);
    // Authentification SSL
    if ($verify_peer && $cafile) {
      $this->ca_info = $cafile;
      stream_context_set_option($context, "ssl", "cafile", $cafile);
    }

    // Délai maximal d'attente pour la lecture
    $socket_timeout = $socket_timeout ? $socket_timeout : CAppUI::conf("webservices response_timeout");
    if ($socket_timeout) {
      ini_set("default_socket_timeout", $socket_timeout);
    }

    // Changement du répertoire pour mettre les wsdl en cache
    CMbPath::forceDir(CAppUI::getTmpPath("soap_client"));
    ini_set('soap.wsdl_cache_dir', CAppUI::getTmpPath("soap_client"));

    $options = array_merge($options, array("stream_context" => $context));

    $this->options = $options;

    if ($wsdl_external) {
      $this->wsdl_url = $wsdl_external;
    }

    if ($location_for_port) {
      $this->__setLocation($location_for_port);
    }

    parent::__construct($this->wsdl_url, $options);
  }

    /**
     * @return string|null
     */
    public function getWsdlUrl(): ?string
    {
        return $this->wsdl_url;
    }

  /**
   * Calls a SOAP function
   *
   * @param string $function_name  The name of the SOAP function to call
   * @param array  $arguments      An array of the arguments to pass to the function
   * @param array  $options        An associative array of options to pass to the client
   * @param mixed  $input_headers  An array of headers to be sent along with the SOAP request
   * @param array  $output_headers If supplied, this array will be filled with the headers from the SOAP response
   *
   * @throws Exception|SoapFault
   *
   * @return mixed SOAP functions may return one, or multiple values
   */
  public function call($function_name, $arguments, $options = null, $input_headers = null, &$output_headers = null) {
    try {
      $result = parent::__soapCall($function_name, $arguments, $options, $input_headers, $output_headers);

      if ($this->return_mode == "raw") {
        return $this->response_body;
      }

      return $result;
    }
    catch(SoapFault $fault) {
      throw $fault;
    }
  }

  /**
   * Execute the request
   *
   * @param string $request  Request to execute
   * @param string $location Webservice URL
   * @param string $action   Action
   * @param int    $version  SOAP version
   * @param int    $one_way  One way
   *
   * @see parent::__doRequest
   *
   * @return null|string
   * @throws CMbException
   */
  public function __doRequest($request, $location, $action, $version, $one_way = 0): ?string {
    $ca_file = $this->ca_info;
    if ($this->use_tunnel) {
      $tunnel_exist = false;
      $tunnel_pass = CAppUI::conf("eai tunnel_pass");
      $tunnel_object = new CHTTPTunnelObject();
      $tunnels = $tunnel_object->loadActiveTunnel();

      foreach ($tunnels as $_tunnel) {
        if ($_tunnel->checkStatus()) {
          $location = preg_replace("#[^/]*//[^/]*#", $_tunnel->address, $location);
          $ca_file = $_tunnel->ca_file;
          $tunnel_exist = true;
          break;
        }
      }
      if (!$tunnel_exist && $tunnel_pass === "0") {
        throw new CMbException("Pas de tunnel actif");
      }
    }

    if (!empty($this->namespaces)) {
      $this->applyNamespaces($request);
      /* The __last_request property must be updated, otherwise the __getLastRequest method will not return the same
         XML as the one sent to the service */
      $this->__last_request = $request;
    }

    $response = null;

    if ($this->return_mode == "file") {
      $this->doRequestToFile($request, $location, $action, $ca_file);
      return "";
    }
    elseif ($this->xop_mode) {
      $response = $this->doRequestXOP($request, $location, $action, $ca_file);
    }
    else {
      $response = parent::__doRequest($request, $location, $action, $version, $one_way);
    }

    if ($this->return_mode == "normal") {
      return $response;
    }

    if (!$response) {
      return null;
    }

    $document = new CMbXMLDocument();
    $document->loadXML($response, null, true);

    $xpath = new CMbXPath($document);
    $documentElement = $document->documentElement;
    $xpath->registerNamespace($documentElement->prefix, $documentElement->namespaceURI);
    $body = $xpath->queryUniqueNode("/$documentElement->prefix:Envelope/$documentElement->prefix:Body");

    $new_document = new CMbXMLDocument("UTF-8");
    $new_document->appendChild($new_document->importNode($body->firstChild, true));

    $this->response_body = $new_document->saveXML();

    return $response;
  }

  /**
   * Get an HTTP client based on cURL
   *
   * @param string $location SOAP URL
   * @param string $ca_file  Certification authority file path
   * @param array  $headers  HTTP headers
   *
   * @return CHTTPClient
   */
  protected function getHttpClient($location, $ca_file, $headers) {
    $http_client = new CHTTPClient($location);

    if ($this->local_cert) {
      $http_client->setSSLAuthentification($this->local_cert, $this->passphrase);
    }
    if ($ca_file) {
      $http_client->setSSLPeer($ca_file);
    }

    $http_client->header = $headers;

    return $http_client;
  }

  /**
   * Make a XOP SOAP request
   *
   * @param string $request  Request
   * @param string $location SOAP URL
   * @param string $action   SOAP action
   * @param string $ca_file  Certification authority file path
   *
   * @throws CMbException
   * @return string
   */
  protected function doRequestXOP($request, $location, $action, $ca_file) {
    if (!$action && isset($this->__default_headers)) {
      foreach ($this->__default_headers as $_default_header) {
        if (!$_default_header instanceof SoapHeader) {
          continue;
        }

        /** @var SoapHeader $_default_header */
        if ($_default_header->name == "Action") {
          $action = $_default_header->data;
          break;
        }
      }
    }

    $boundary = "Part_".uniqid("", true);
    $head = "--$boundary
Content-Type: application/xop+xml; charset=UTF-8; type=\"application/soap+xml\"
Content-Transfer-Encoding: binary
Content-ID: <0.urn:uuid:$boundary@apache.org>
";

    $foot = utf8_encode("\n--$boundary--\n");

    $request = preg_replace('#^<\?xml[^>]*>#', '', $request);
    $request = $head.$request.$foot;

    if ($this->_soap_version == "1") {
      $headers = array(
        "Content-Type: multipart/related",
        "boundary=\"$boundary\"",
        "type=\"application/xop+xml\"",
        "start=\"<0.urn:uuid:$boundary@apache.org>\"",
        "MIME-Version: 1.0",
        "SOAPAction: $action",
        "Content-Length: ".strlen($request),
      );
    }
    else {
      $headers = array(
        "Content-Type: multipart/related; boundary=$boundary; type=\"application/xop+xml\"; start=\"0.urn:uuid:$boundary@apache.org\"; start-info=\"application/soap+xml\"; action=\"$action\""
      );
    }

    try {
      $http_client = $this->getHttpClient($location, $ca_file, $headers);
      $result = $http_client->post($request);
    }
    catch (Exception $e) {
      throw new CMbException("Error: ".$e->getMessage());
    }

    preg_match("#<.*Envelope>#", $result, $matches);
    return $matches[0];
  }

  /**
   * Make a SOAP request and get the result in a file
   *
   * @param string $request  Request
   * @param string $location SOAP URL
   * @param string $action   SOAP action
   * @param string $ca_file  Certification authority file path
   *
   * @throws CMbException
   * @return void
   */
  protected function doRequestToFile($request, $location, $action, $ca_file) {
    $headers = array(
      "Content-Type: text/xml; charset=utf-8",
      "SOAPAction: \"$action\"",
      "Content-Length: ".strlen($request),
    );

    try {
      $http_client = $this->getHttpClient($location, $ca_file, $headers);

      $result_file = tempnam("./tmp", "so");
      $f = fopen($result_file, "w+");

      $http_client->setOption(CURLOPT_FILE, $f);
      $http_client->setOption(CURLOPT_RETURNTRANSFER, false);
      $http_client->setOption(CURLOPT_SSL_VERIFYPEER, false);

      if (isset($this->options["login"]) && isset($this->options["password"])) {
        $username = $this->options["login"];
        $password = $this->options["password"];
        $http_client->setOption(CURLOPT_USERPWD, "$username:$password");
        $http_client->setOption(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      }

      $http_client->post($request);

      fclose($f);

      // Remove soap enveloppe...
      /**
       * <?xml version="1.0" encoding="utf-8"?>
      <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
      <soap:Body>
      .....
       */
      $fr = fopen($result_file, "r");

      $this->response_file = tempnam("./tmp", "so");
      $fw = fopen($this->response_file, "w+");

      $status = "xmlheader";
      $soapenvns = "";
      $buffer = "";
      $size = 0;
      $size_truncate = null;
      while (!feof($fr)) {
        switch ($status) {
          // <?xml version="1.0" encoding="utf-8"? >
          case "xmlheader":
            $c = fgetc($fr);
            $buffer .= $c;

            if (strpos($buffer, "?>") !== false) {
              $xmlheader = $buffer;
              $buffer = "";
              $status = "soapenvns";

              $size += fwrite($fw, $xmlheader."\n");
            }
            break;

          // <soap:
          case "soapenvns":
            $c = fgetc($fr);
            $buffer .= $c;

            if (strpos($buffer, ":") !== false) {
              $soapenvns = substr($buffer, strrpos($buffer, "<"));
              $buffer = "";
              $status = "soapenv";
            }
            break;

          // <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
          // xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
          // xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body>
          case "soapenv":
            $c = fgetc($fr);
            $buffer .= $c;

            if (stripos($buffer, "{$soapenvns}Body>") !== false) {
              $buffer = "";
              $status = "content";
            }
            break;

          case "content":
            $s = fgets($fr, 1024);
            $buffer = substr($buffer.$s, -1024);

            $size += fwrite($fw, $s);

            // Check if we have the closing soapenv:body
            $soapenvend = "</".substr($soapenvns, 1);
            $pos = stripos($buffer, $soapenvend);
            if ($pos !== false) {
              $size_truncate = $size - (strlen($buffer) - $pos);
              break 2;
            }
            break;

          default:
            // do nothing
        }
      }

      fclose($fr);
      unlink($result_file);

      ftruncate($fw, $size_truncate);
      fclose($fw);
    }
    catch (Exception $e) {
      throw new CMbException("Error: ".$e->getMessage());
    }
  }

  /**
   * Set the request and the response of the exchange and return it
   *
   * @param CEchangeSOAP $exchange The exchangeSOAP
   */
  public function getTrace(CEchangeSOAP $exchange): void
  {
    $exchange->trace                 = true;
    $exchange->last_request_headers  = $this->__getLastRequestHeaders();
    $exchange->last_request          = $this->__getLastRequest();
    $exchange->last_response_headers = $this->__getLastResponseHeaders();
    $exchange->last_response         = $this->__getLastResponse();
  }

  /**
   * Defines headers to be sent along with the SOAP requests
   *
   * @param array $soapheaders The headers to be set
   *
   * @return boolean True on success or False on failure
   */
  public function setHeaders($soapheaders): void
  {
    $this->__setSoapHeaders($soapheaders);
  }

  /**
   * Set the namespaces that will be used in the SOAP Envelope.
   * If a namespace is already present, it's prefix will be replaced by the one specified
   *
   * @param array $namespaces An array of namespaces (prefix => uri)
   *
   * @return void
   */
  public function setNamespaces($namespaces = array()) {
    $this->namespaces = $namespaces;
  }

  /**
   * Change the prefix for the valued namespace uri by the corresponding prefix
   *
   * @param string $xml The XML
   *
   * @return string
   */
  public function applyNamespaces(&$xml) {
    foreach ($this->namespaces as $prefix => $uri) {
      if (strpos($xml, $uri) !== false) {
        $uri = str_replace(array('/', '.'), array('\/', '\.'), $uri);
        preg_match("#xmlns:([a-z0-9]+)=\"$uri\"#", $xml, $matches);
        $old_prefix = $matches[1];
        $xml = preg_replace("#(xmlns:)[a-z0-9]+(=\"$uri\")#", "$1$prefix$2", $xml);

        $xml = str_replace($old_prefix, $prefix, $xml);
      }
    }

    return $xml;
  }

  /**
   * Check service availability
   *
   * @throws CMbException
   *
   * @return void
   */
  public function checkServiceAvailability(): void
  {
    $url = $this->wsdl_url;
    if ($this->wsdl_original) {
      $url = $this->wsdl_original;
    }

    $context = null;
    if (CMbArray::get($this->options, 'login') && CMbArray::get($this->options, 'password')) {
      $context = stream_context_create(
        array(
          'http' => array(
            'header' => 'Authorization: Basic ' . base64_encode("{$this->options['login']}:{$this->options['password']}")
          )
        )
      );
    }
    $xml = file_get_contents($url, false, $context);

    $dom = new CMbXMLDocument();
    $dom->loadXML($xml);

    $xpath = new CMbXPath($dom);
    $xpath->registerNamespace("wsdl", "http://schemas.xmlsoap.org/wsdl/");
    $xpath->registerNamespace("soap", "http://schemas.xmlsoap.org/wsdl/soap/");
    $xpath->registerNamespace("soap12", "http://schemas.xmlsoap.org/wsdl/soap12");

    $login    = CMbArray::get($this->options, "login");
    $password = CMbArray::get($this->options, "password");

    $service_nodes = $xpath->query("//wsdl:service");
    foreach ($service_nodes as $_service_node) {
      $service_name = $_service_node->getAttribute("name");

      $port_nodes = $xpath->query("wsdl:port", $_service_node);
      foreach ($port_nodes as $_port_node) {
        $address = $xpath->queryAttributNode("soap:address|soap12:address", $_port_node, "location");

        if (!$address) {
          continue;
        }

        if ($login && $password) {
          $address = str_replace("://", "://$login:$password@", $address);
        }

        // Url exist
        $url_exist = CHTTPClient::checkUrl($address, $this->check_option);

        if (!$url_exist) {
          throw new CMbException("Service '$service_name' injoignable à l'adresse : <em>$address</em>");
        }
      }
    }
  }

  /**
   * Check if the given function is an available SOAP function
   *
   * @param string $function_name
   *
   * @return bool
   */
  public function functionExist($function_name): bool
  {
    $functions = $this->__getFunctions();

    foreach ($functions as $_function) {
      if (preg_match("/$function_name/", $_function) == 1) {
        return true;
      }
    }

    return false;
  }

    /**
     * @param CExchangeSource $source
     *
     * @return void
     */
    public function init(CExchangeSource $source): void
    {
        $this->source = $source;
        $this->source->_wsdl_url = $this->wsdl_url;
    }

    public function hasError(): bool
    {
        return $this->soap_client_error;
    }
}
