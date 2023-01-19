<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\HttpClient;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Ox\Core\Auth\Authenticators\ApiTokenAuthenticator;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\Chronometer;
use Ox\Mediboard\System\CExchangeHTTP;
use Ox\Mediboard\System\CSourceHTTP;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Client
 */
class Client
{
    /** @var string */
    public const METHOD_GET = 'GET';

    /** @var string */
    public const METHOD_POST = 'POST';

    /** @var string */
    public const METHOD_PUT = 'PUT';

    /** @var string */
    public const METHOD_DELETE = 'DELETE';

    /** @var string */
    public const METHOD_PATCH = 'PATCH';

    /** @var array */
    public const METHODS = [
        self::METHOD_GET,
        self::METHOD_POST,
        self::METHOD_PUT,
        self::METHOD_DELETE,
        self::METHOD_PATCH,
    ];

    /** @var int */
    public const DEFAULT_CONNECT_TIMEOUT = 5;

    /** @var int */
    public const DEFAULT_TIMEOUT = 5;

    /** @var CSourceHTTP */
    private $source_http;

    /** @var GuzzleClient */
    private $guzzle_client;

    /** @var array */
    private $headers = [];

    /** @var array */
    private $options = [];

    /** @var Chronometer */
    private $chrono;

    /** @var CExchangeHTTP */
    private $last_exchange_http;

    /**
     * CClient constructor.
     *
     * @param CSourceHTTP       $source_http   The HTTP source
     * @param GuzzleClient|null $guzzle_client Allow to mock the Guzzle client for testing purpose (see
     *                                         https://docs.guzzlephp.org/en/v6/testing.html)
     */
    public function __construct(CSourceHTTP $source_http, ?GuzzleClient $guzzle_client = null)
    {
        // Source
        $this->source_http = $source_http;
        if ($this->source_http->user && $this->source_http->password) {
            $this->options['auth'] = [$this->source_http->user, $this->source_http->password];
        }

        if (!$guzzle_client) {
            $configs = [
                'base_uri'        => $this->source_http->host, // https://tools.ietf.org/html/rfc3986#section-3.3
                'connect_timeout' => self::DEFAULT_CONNECT_TIMEOUT,
                'timeout'         => self::DEFAULT_TIMEOUT,
            ];

            $guzzle_client = new GuzzleClient($configs);
        }

        if (null === $guzzle_client->getConfig('connect_timeout')) {
            $this->options['connect_timeout'] = self::DEFAULT_CONNECT_TIMEOUT;
        }

        if (null === $guzzle_client->getConfig('timeout')) {
            $this->options['timeout'] = self::DEFAULT_TIMEOUT;
        }

        $this->guzzle_client = $guzzle_client;
    }

    /**
     * @return CSourceHTTP
     */
    public function getSourceHttp(): CSourceHTTP
    {
        return $this->source_http;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @param bool  $merge
     *
     * @return self
     */
    public function setHeaders(array $headers, bool $merge = true): self
    {
        if ($merge) {
            $headers = array_merge($this->headers, $headers);
        }
        $this->headers = $headers;

        return $this;
    }

    /**
     * Set OX API header (route authentication purpose)
     *
     * @return $this
     */
    public function setTokenHeader()
    {
        if ($token = $this->getSourceHttp()->token) {
            $this->setHeaders(
                [
                    ApiTokenAuthenticator::TOKEN_HEADER_KEY => $token,
                ]
            );
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }


    /**
     * Set Guzzle request options
     * http://docs.guzzlephp.org/en/stable/request-options.html
     *
     * @param array $options
     * @param bool  $merge
     *
     * @return self
     */
    public function setOptions(array $options, bool $merge = true): self
    {
        if ($merge) {
            $options = array_merge($this->options, $options);
        }
        $this->options = $options;

        return $this;
    }


    /**
     * @param string      $method
     * @param string      $path
     * @param null|string $body
     * @param bool        $verbose
     *
     * @return Response
     * @throws ClientException
     * @throws Exception
     */
    public function call(string $method, string $path, string $body = null, bool $verbose = false): Response
    {
        // Method
        if (!in_array($method, self::METHODS, true)) {
            throw new ClientException('Undefined method ' . $method);
        }

        // Options
        $options = array_merge($this->options, ['headers' => $this->headers], ['body' => $body]);

        // Exchange start
        $this->onBeforeRequest($method, $path, $options);

        // Request
        try {
            $request  = new Request($method, $path);
            $response = $this->getGuzzleClient()->send($request, $options);
        } catch (GuzzleException $e) {
            if (method_exists($e, 'hasResponse') && $e->hasResponse()) {
                $response = $e->getResponse();
            } else {
                $this->onExceptionRequest($e);
            }
        }

        // Exchange stop
        $this->onAfterRequest($response);

        // Verbose
        if ($verbose) {
            CApp::log(__METHOD__, $response);
        }

        return new Response($response, $this->last_exchange_http);
    }


    /**
     * @param string $method
     * @param string $url
     * @param array  $options
     *
     * @return void
     * @throws Exception
     */
    private function onBeforeRequest(string $method, string $url, array $options): void
    {
        if ($this->source_http->loggable) {
            $exchange_http = new CExchangeHTTP();

            $exchange_http->date_echange  = 'now';
            $exchange_http->emetteur      = CAppUI::conf('mb_id');
            $exchange_http->function_name = $method;
            $exchange_http->source_class  = $this->source_http->_class;
            $exchange_http->source_id     = $this->source_http->_id;
            $exchange_http->destinataire  = $url;
            $exchange_http->input         = serialize($options);
            $exchange_http->store();

            $this->last_exchange_http = $exchange_http;

            CApp::$chrono->stop();

            $this->chrono = new Chronometer();
            $this->chrono->start();
        }

        // trace
        $this->source_http->startCallTrace();
    }


    /**
     * @param GuzzleException $exception
     *
     * @return void
     * @throws Exception
     */
    private function onExceptionRequest(GuzzleException $exception): void
    {
        if ($this->source_http->loggable) {
            $this->chrono->stop();
            CApp::$chrono->start();

            $output = [
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
            ];

            $exchange_http                = $this->last_exchange_http;
            $exchange_http->http_fault    = true;
            $exchange_http->response_time = $this->chrono->total;
            $exchange_http->output        = serialize($output);
            $exchange_http->store();
        }

        $this->source_http->stopCallTrace();

        throw new ClientException($exception->getMessage(), $exception->getCode(), $exception);
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     * @throws Exception
     */
    private function onAfterRequest(ResponseInterface $response): void
    {
        if ($this->source_http->loggable) {
            $this->chrono->stop();
            CApp::$chrono->start();

            $output = [
                'StatusCode'   => $response->getStatusCode(),
                'ReasonPhrase' => $response->getReasonPhrase(),
                'BodySize'     => $response->getBody()->getSize(),
                'Headers'      => $response->getHeaders(),
            ];
            if ($response->getStatusCode() >= 300) {
                $output['BodyContent'] = $response->getBody()->__toString();
            }

            $exchange_http                = $this->last_exchange_http;
            $exchange_http->date_echange  = 'now';
            $exchange_http->status_code   = $response->getStatusCode();
            $exchange_http->response_time = $this->chrono->total;
            $exchange_http->output        = serialize($output);
            $exchange_http->store();
        }

        $this->source_http->stopCallTrace();
    }

    /**
     * @return GuzzleClient
     */
    public function getGuzzleClient(): GuzzleClient
    {
        return $this->guzzle_client;
    }

    public function getConnectTimeout(): ?int
    {
        return ($this->options['connect_timeout']) ?? $this->guzzle_client->getConfig('connect_timeout');
    }

    public function getTimeout(): ?int
    {
        return ($this->options['timeout']) ?? $this->guzzle_client->getConfig('timeout');
    }
}
