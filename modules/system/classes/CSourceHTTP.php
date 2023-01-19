<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\Contracts\Client\ClientInterface;
use Ox\Core\Contracts\Client\HTTPClientInterface;
use Ox\Interop\Eai\Resilience\ClientContext;
use Ox\Mediboard\System\Client\HTTPClientCurlLegacy;
use Ox\Mediboard\System\Client\ResilienceHTTPClient;
use Psr\Http\Message\RequestInterface;

/**
 * Source HTTP, using cURL, allowing traceability
 */
class CSourceHTTP extends CExchangeSourceAdvanced
{
    // Source type
    public const TYPE = 'http';

    /** @var string[] */
    protected const CLIENT_MAPPING = [
        self::CLIENT_HTTP_LEGACY_CURL => HTTPClientCurlLegacy::class,
    ];

    /** @var string */
    protected const DEFAULT_CLIENT = self::CLIENT_HTTP_LEGACY_CURL;

    /** @var string */
    public const CLIENT_HTTP_LEGACY_CURL = 'curl_leg';

    /** @var int Primary key */
    public $source_http_id;
    /** @var string */
    public $token;

    /** @var HTTPClientInterface */
    public $_client;

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'source_http';
        $spec->key   = 'source_http_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props          = parent::getProps();
        $props["token"] = "str";

        return $props;
    }

    /**
     * @param string|null $evenement_name
     *
     * @return string|null
     */
    public function getHost(?string $evenement_name = null): ?string
    {
        if (!$host = $this->host) {
            return null;
        }

        if ($evenement_name !== null) {
            $host = rtrim($host, "/") . "/" . ltrim($evenement_name, "/");
        }

        return $host;
    }

    /**
     * @return HTTPClientInterface
     * @throws CMbException
     */
    public function getClient(): ClientInterface
    {
        if ($client_http = $this->_client) {
            return $client_http;
        }

        /** @var HTTPClientInterface $client_http */
        $client_http = parent::getClient();

        if ($this->retry_strategy) {
            $client_http = new ResilienceHTTPClient($client_http, $this);
        }

        return $this->_client = $client_http;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param ClientContext $context
     *
     * @return void
     * @throws \Exception
     */
    public function onBeforeRequest(ClientContext $context): void
    {
        if (!$this->loggable) {
            return;
        }

        $input = null;

        if (($request = $context->getRequest()) && $request instanceof RequestInterface) {
            $uri   = $request->getUri()->__toString();
            $input = [
                'uri'     => $uri,
                'body'    => $request->getBody()->__toString(),
                'method'  => $request->getMethod(),
                'headers' => $request->getHeaders(),
            ];

            $input = serialize($input);
        }

        $function_name = $context->getArguments()['function_name'] ?? null;

        $this->_current_echange      = $echange_http = new CExchangeHTTP();
        $echange_http->date_echange  = CMbDT::dateTime();
        $echange_http->emetteur      = CAppUI::conf("mb_id");
        $echange_http->function_name = $function_name;
        $echange_http->source_class  = $this->_class;
        $echange_http->source_id     = $this->_id;
        $echange_http->destinataire  = $uri ?? null;
        $echange_http->input         = $input !== null ?
            serialize($input) : null;
    }
    
    /**
     * @inheritDoc
     */
    public function startLog(ClientContext $context): void
    {
        $function_name = $context->getArguments()['function_name'] ?? null;

        $input = $context->getRequest() ?: null;

        $this->_current_echange      = $echange_http = new CExchangeHTTP();
        $echange_http->date_echange  = CMbDT::dateTime();
        $echange_http->emetteur      = CAppUI::conf("mb_id");
        $echange_http->destinataire  = $this->host;
        $echange_http->source_class  = $this->_class;
        $echange_http->source_id     = $this->_id;
        $echange_http->function_name = $function_name ?: '';
        $echange_http->input         = $input !== null ?
            serialize($input) : null;
    }

    /**
     * @inheritDoc
     */
    public function stopLog(ClientContext $context): void
    {
        /** @var CExchangeHTTP $echange_http */
        if (!($echange_http = $this->_current_echange)) {
            return;
        }

        $echange_http->response_time = $this->_current_chronometer->total;

        // response time
        $echange_http->response_datetime = CMbDT::dateTime();

        // Truncate input and output before storing
        $output = $context->getResponse();


        $echange_http->output = $output !== null
            ? serialize($output)
            : null;


        if ($msg = $echange_http->store()) {
            CApp::log($msg);
        }
    }

    /**
     * @inheritDoc
     */
    public function exceptionLog(ClientContext $context): void
    {
        /** @var CExchangeHTTP $echange_http */
        if (!$echange_http = $this->_current_echange) {
            return;
        }

        $echange_http->response_datetime = CMbDT::dateTime();
        $throwable                       = $context->getThrowable();
        $output                          = $context->getResponse();
        if (!$output && $throwable) {
            $output = $throwable->getMessage();
        }

        $echange_http->output    = $output !== null ?
            serialize($output) : null;
        
        if ($msg = $echange_http->store()) {
            CApp::log($msg);
        }
    }
}
