<?php

/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Webservices;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CMbString;
use Ox\Core\Contracts\Client\ClientInterface;
use Ox\Core\Contracts\Client\SOAPClientInterface;
use Ox\Interop\Eai\Resilience\ClientContext;
use Ox\Mediboard\System\CExchangeSourceAdvanced;
use Ox\Mediboard\System\CExchangeSourceStatistic;
use SoapHeader;

/**
 * Class CSourceSOAP
 * Source SOAP
 */
class CSourceSOAP extends CExchangeSourceAdvanced
{
    // Source type
    public const TYPE = 'soap';

    /** @var string */
    public const CLIENT_SOAP = 'soap_client';

    /** @var string */
    protected const DEFAULT_CLIENT = self::CLIENT_SOAP;

    /** @var string[] */
    protected const CLIENT_MAPPING = [
      self::CLIENT_SOAP => CSOAPLegacy::class
    ];

    /** @var  SOAPClientInterface **/
    public $_client;

    // DB Table key
    public $source_soap_id;

    // DB Fields
    public $wsdl_external;
    public $evenement_name;
    public $single_parameter;
    public $encoding;
    public $stream_context;
    public $type_soap;
    public $local_cert;
    public $passphrase;
    public $iv_passphrase;
    public $safe_mode;
    public $return_mode;
    public $soap_version;
    public $xop_mode;
    public $use_tunnel;
    public $socket_timeout;
    public $connection_timeout;
    public $feature;
    public $port_name;
    public $client_name;

    // Options de contexte SSL
    public $verify_peer;
    public $cafile;

    /** @var SOAPClientInterface */
    protected $_soap_client;


    /** @var string */
    public $_wsdl_url;

    /**
     * Initialize object specification
     *
     * @return CMbObjectSpec the spec
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'source_soap';
        $spec->key   = 'source_soap_id';

        return $spec;
    }

    /**
     * Get properties specifications as strings
     *
     * @return array
     * @see parent::getProps()
     *
     */
    function getProps()
    {
        $props = parent::getProps();

        $props["wsdl_external"]      = "str";
        $props["evenement_name"]     = "str";
        $props["single_parameter"]   = "str";
        $props["encoding"]           = "enum list|UTF-8|ISO-8859-1|ISO-8859-15 default|UTF-8";
        $props["type_soap"]          = "enum list|CMbSOAPClient default|CMbSOAPClient notNull";
        $props["iv_passphrase"]      = "str show|0 loggable|0";
        $props["safe_mode"]          = "bool default|0";
        $props["return_mode"]        = "enum list|normal|raw|file";
        $props["soap_version"]       = "enum list|SOAP_1_1|SOAP_1_2 default|SOAP_1_1 notNull";
        $props["xop_mode"]           = "bool default|0";
        $props["use_tunnel"]         = "bool default|0";
        $props["socket_timeout"]     = "num min|1";
        $props["connection_timeout"] = "num min|1";
        $props["feature"]            = "enum list|SOAP_SINGLE_ELEMENT_ARRAYS|SOAP_USE_XSI_ARRAY_TYPE|SOAP_WAIT_ONE_WAY_CALLS";
        $props["port_name"]          = "str";

        $props["local_cert"] = "str";
        $props["passphrase"] = "password show|0 loggable|0";

        $props["verify_peer"] = "bool default|0";
        $props["cafile"]      = "str";

        $props["stream_context"] = "str";

        return $props;
    }

    /**
     * Encrypt fields
     *
     * @return void
     */
    public function updateEncryptedFields(): void
    {
        if ($this->passphrase === "") {
            $this->passphrase = null;
        } else {
            if (!empty($this->passphrase)) {
                $this->passphrase = $this->encryptString($this->passphrase, "iv_passphrase");
            }
        }
    }

    /**
     * Calls a SOAP function
     *
     * keep this function for legacy
     *
     * @param string $function  Function name
     * @param array  $arguments Arguments
     *
     * @return void
     */
    public function __call(string $function, array $arguments = [])
    {
        $this->setData(reset($arguments));
        $this->getClient()->send($function);
    }

    /**
     * Set SOAP header
     *
     * @param string $namespace      The namespace of the SOAP header element.
     * @param string $name           The name of the SoapHeader object
     * @param mixed  $data           A SOAP header's content. It can be a PHP value or a SoapVar object
     * @param bool   $mustUnderstand Value must understand
     * @param null   $actor          Value of the actor attribute of the SOAP header element
     *
     * @return void
     */
    public function setHeaders(
        ?string $namespace,
        ?string $name,
        $data,
        ?bool $mustUnderstand = false,
        $actor = null
    ): void {
        $soapClient = $this->getClient();
        $headers    = $soapClient->getHeaders();
        if ($actor) {
            $headers[] = new SoapHeader($namespace, $name, $data, $mustUnderstand, $actor);
        } else {
            $headers[] = new SoapHeader($namespace, $name, $data);
        }

        $soapClient->setHeaders($headers);
    }

    /**
     * @return ClientInterface
     * @throws CMbException
     */
    public function getClient(): SOAPClientInterface
    {
        if ($client_soap = $this->_client) {
            return $client_soap;
        }

        /** @var SOAPClientInterface $client_soap */
        $client_soap = parent::getClient();

        if ($this->retry_strategy) {
            $client_soap = new ResilienceSOAPClient($client_soap, $this);
        }

        return $this->_client = $client_soap;
    }

    /**
     * @param string $function_name
     * @param array  $arguments
     *
     * @return CEchangeSOAP
     * @throws Exception
     */
    public function onBeforeRequest(ClientContext $context): void
    {
        $function_name = $context->getArguments()["function_name"] ?? null;
        $datas = $context->getRequest();

        $this->_current_echange = $echange_soap = new CEchangeSOAP();

        $wsdl_url                   = $this->_wsdl_url ?: $this->host;
        $echange_soap->date_echange = CMbDT::dateTime();
        $echange_soap->emetteur     = CAppUI::conf("mb_id");
        $echange_soap->destinataire = $wsdl_url;
        $echange_soap->type         = $this->type_echange;
        $echange_soap->source_id    = $this->_id;
        $echange_soap->source_class = $this->_class;

        $url                            = parse_url($wsdl_url);
        $path                           = explode("/", $url['path']);
        $echange_soap->web_service_name = end($path);
        $echange_soap->function_name = $function_name;
        $echange_soap->input = $datas !== null ?
            serialize($datas) : null;

        if ($this->loggable) {
            $echange_soap->store();
        }
    }

    /**
     * @inheritDoc
     */
    public function startLog(ClientContext $context): void
    {
        $function_name = $context->getArguments()['function_name'] ?? null;

        $input = $context->getRequest() ?: null;
        $this->_current_echange     = $echange_soap = new CEchangeSOAP();
        $echange_soap->date_echange  = CMbDT::dateTime();
        $echange_soap->emetteur      = CAppUI::conf("mb_id");
        $echange_soap->destinataire  = $this->host;
        $echange_soap->source_class  = $this->_class;
        $echange_soap->source_id     = $this->_id;
        $echange_soap->function_name = $function_name ?: '';
        $echange_soap->input         = $input !== null ?
            serialize($input) : null;
    }

    /**
     * @inheritDoc
     */
    public function stopLog(ClientContext $context): void
    {
        /** @var CEchangeSOAP $echange_soap */
        if (!($echange_soap = $this->_current_echange)) {
            return;
        }

        $echange_soap->response_time = $this->_current_chronometer->total;

        // response time
        $echange_soap->response_datetime = CMbDT::dateTime();

        // Truncate input and output before storing
        $output = $context->getResponse();

        $this->_client->getTrace($echange_soap);

        if ($echange_soap->soapfault != 1) {
            $echange_soap->output = $output !== null
                ? serialize($output)
                : null;
        }

        if ($msg = $echange_soap->store()) {
            CApp::log($msg);
        }
    }

    /**
     * @inheritDoc
     */
    public function exceptionLog(ClientContext $context): void
    {
        /** @var CEchangeSOAP $echange_soap */
        if (!$echange_soap = $this->_current_echange) {
            return;
        }

        $echange_soap->response_datetime = CMbDT::dateTime();
        $throwable                      = $context->getThrowable();
        $output                         = $context->getResponse();
        if (!$output && $throwable) {
            $output = $throwable->getMessage();
        }

        $echange_soap->output    = $output !== null ?
            serialize($output) : null;

        if ($msg = $echange_soap->store()) {
            CApp::log($msg);
        }
    }
    /**
     * @param $string
     *
     * @return string
     */
    public static function truncate($string)
    {
        if (!is_string($string)) {
            return $string;
        }

        // Truncate
        $max    = 1024;
        $result = CMbString::truncate($string, $max);

        // Indicate true size
        $length = strlen($string);
        if ($length > 1024) {
            $result .= " [$length bytes]";
        }

        return $result;
    }
}
