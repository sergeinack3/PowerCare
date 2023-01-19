<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Client;

use Exception;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbSecurity;
use Ox\Core\CMbServer;
use Ox\Core\Contracts\Client\HTTPClientInterface;
use Ox\Core\HttpClient\ClientException;
use Ox\Interop\Eai\Resilience\ClientContext;
use Ox\Mediboard\System\CExchangeHTTPClient;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceHTTP;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Throwable;

class HTTPClientCurlLegacy implements HTTPClientInterface
{
    /** @var int */
    protected const DEFAULT_CONNECT_TIMEOUT = 5;

    /** @var int */
    protected const DEFAULT_TIMEOUT = 60;

    /** @var string */
    private const CUSTOM_TOKEN_HEADER_KEY = 'X-OXAPI-KEY';

    /** @var CSourceHTTP */
    private $source;

    /** @var CExchangeHTTPClient */
    private $http;

    /** @var EventDispatcher */
    protected $dispatcher;

    public function __construct()
    {
        // keep for use client without source
        $this->init(new CSourceHTTP());
    }

    /**
     * @param string|null    $function_name
     * @param Throwable|null $throwable
     *
     * @return ClientContext
     */
    private function getContext(?string $function_name = null, ?Throwable $throwable = null): ClientContext
    {
        $arguments = [];
        if ($function_name) {
            $arguments['function_name'] = $function_name;
        }

        return (new ClientContext($this, $this->source))
            ->setArguments($arguments)
            ->setThrowable($throwable);
    }

    protected function dispatch($call_args, string $function_name)
    {
        $context = $this->getContext($function_name);

        if (is_array($call_args)) {
            $arguments = $call_args[1] ?? [];
            $callable  = $call_args[0];
            $context->setRequest($arguments);
        } else {
            $callable  = $call_args;
            $arguments = [];
        }


        $this->dispatcher->dispatch($context, self::EVENT_BEFORE_REQUEST);
        $result = call_user_func($callable, $arguments);
        $context->setResponse($result);
        $this->dispatcher->dispatch($context, self::EVENT_AFTER_REQUEST);

        return $result;
    }

    /**
     * Create and send an HTTP request.
     *
     * @param string $method
     * @param string $uri
     * @param array  $options
     *
     * @return ResponseInterface
     * @throws Exception|Throwable
     */
    public function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        $request = new Request($method, $uri, $options['headers'] ?? []);

        return $this->send($request, $options);
    }

    /**
     * @param CSourceHTTP $source
     *
     * @return void
     */
    public function init(CExchangeSource $source): void
    {
        $this->source     = $source;
        $this->dispatcher = $source->_dispatcher;
    }

    /**
     * @return bool
     * @throws CMbException
     */
    public function isReachableSource(): bool
    {
        $call = [
            function ($args) {
                return $this->url_exists(...$args);
            },
            [$this->source->host, 'GET'],
        ];

        if (!$this->dispatch($call, 'isReachableSource')) {
            throw new CMbException("test", $this->source->host);
        }


        return $this->url_exists($this->source->host, 'GET');
    }

    /**
     * Check wether a URL exists (200 HTTP Header)
     *
     * @param string $url    URL to check
     * @param string $method HTTP method (GET, POST, HEAD, PUT, ...)
     *
     * @return bool
     */
    public function url_exists($url, $method = null): bool
    {
        $old = ini_set('default_socket_timeout', 5);

        if ($method) {
            // By default get_headers uses a GET request to fetch the headers.
            // If you want to send a HEAD request instead,
            // you can change method with a stream context
            stream_context_set_default(
                [
                    'http' => [
                        'method' => $method,
                    ],
                ]
            );
        }

        //get_header return false when bad url given
        if (($headers = @get_headers($url) ) === false) {
            return false;
        }

        ini_set('default_socket_timeout', $old);

        return (preg_match("|200|", $headers[0]));
    }


    /**
     * @return bool
     */
    public function isAuthentificate(): bool
    {
        return false;
    }

    /**
     * @return int
     */
    public function getResponseTime(): int
    {
        return CMbServer::getUrlResponseTime($this->source->host, '80');
    }

    /**
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return ResponseInterface
     * @throws Exception
     * @throws Throwable
     */
    public function send(RequestInterface $request, array $options = []): ResponseInterface
    {
        $uri            = $this->getUri($request, $options);
        $this->http     = $http = new CExchangeHTTPClient($uri);
        $http->_source  = $this->source;
        $http->loggable = $this->source->loggable ?? false;
        $http->setOption(CURLOPT_HEADER, true);
        $http->setOption(CURLINFO_HEADER_OUT, true);

        // auth
        $request = $this->setAuth($request, $options);

        // Date header
        if (empty($request->getHeader('Date'))) {
            $request = $request->withHeader('Date', CMbDT::dateTimeGMT());
        }

        // disable peer verification
        if (($options['verify_peer'] ?? true) === false) {
            $http->setOption(CURLOPT_SSL_VERIFYPEER, false);
            $http->setOption(CURLOPT_SSL_VERIFYHOST, false);
        }

        // connect timeout
        $connect_timeout = $options['connect_timeout'] ?? self::DEFAULT_CONNECT_TIMEOUT;
        $http->setOption(CURLOPT_CONNECTTIMEOUT_MS, intval(floatval($connect_timeout) * 1000));

        // timeout
        $timeout = $options['timeout'] ?? self::DEFAULT_TIMEOUT;
        $http->setOption(CURLOPT_TIMEOUT_MS, intval(floatval($timeout) * 1000));

        // set Content Type header
        $request = $this->checkContentTypeHeader($request, $options);

        $body   = $this->getBody($request, $options);
        $method = $request->getMethod();

        if (is_array($body)) {
            throw new CMbException('HTTPClientCurlLegacy-msg-error invalid body parameter given');
        }

        // Content-Length
        if (in_array(strtoupper($method), ['PUT', 'POST'])) {
            if (empty($request->getHeader("Content-Length")) && is_string($body)) {
                $request = $request->withHeader('Content-Length', strlen($body));
            }
        }

        // headers
        foreach ($request->getHeaders() as $header_name => $header_value) {
            if (is_array($header_value)) {
                $header_value = implode(', ', $header_value);
            }

            $http->header[] = "$header_name: $header_value";
        }

        // call
        $response = $this->call($method, $body);

        // set response on resource
        $this->source->_acquittement = $response->getBody()->__toString();

        return $response;
    }

    /**
     * @param string            $method
     * @param string|resource|StreamInterface|null $body
     *
     * @return Response
     * @throws ClientException
     * @throws Throwable
     */
    protected function call(string $method, $body): Response
    {
        $request = new Request($method, $this->http->url, $this->http->header ?? [], $body);
        $context = $this->getContext('send')
            ->setRequest($request);

        $this->dispatcher->dispatch($context, self::EVENT_BEFORE_REQUEST);
        try {
            switch (strtoupper($method)) {
                case 'GET':
                    $full_response = $this->http->get();
                    break;
                case 'DELETE':
                    $full_response = $this->http->delete();
                    break;
                case 'POST':
                    $full_response = $this->http->post($body);
                    break;
                case 'PUT':
                    $full_response = $this->http->put($body);
                    break;
                default:
                    throw new ClientException('Undefined method ' . $method);
            }

            [$headers, $response] = explode("\r\n\r\n", $full_response, 2);

            $parsed_headers = $this->http->parseHeaders($headers);
            $response_psr   = new Response(
                $parsed_headers['HTTP_Code'] ?: '500',
                $parsed_headers,
                $response,
                $parsed_headers['HTTP_Version'] ?: '1.1',
                $parsed_headers["HTTP_Message"] ?: null
            );

            $context->setResponse($response_psr);
            $this->dispatcher->dispatch($context, self::EVENT_AFTER_REQUEST);

            return $response_psr;
        } catch (Throwable $exception) {
            $this->dispatcher->dispatch($this->getContext('send', $exception), self::EVENT_EXCEPTION);
            throw $exception;
        }
    }

    /**
     * Set auth header if not already set
     *
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return void
     */
    protected function setAuth(RequestInterface $request, array $options): RequestInterface
    {
        if (!empty($request->getHeader('Authorization'))) {
            return $request;
        }

        $http = $this->http;

        if ($ox_token = $options[self::CUSTOM_TOKEN_HEADER_KEY] ?? null) { // ox header auth
            $request = $request->withHeader(self::CUSTOM_TOKEN_HEADER_KEY, $ox_token);
        } elseif ($auth_bearer = ($options['auth_bearer'] ?? null)) { // auth Bearer with classic token
            $auth_bearer = str_replace('Bearer ', '', $auth_bearer);

            $request = $request->withHeader('Authorization', "Bearer $auth_bearer");
        } elseif ($this->source->user && $this->source->password) { // auth with user name password
            $http->setHTTPAuthentification($this->source->user, $this->source->password);
        } elseif ($auth_basic = ($options['auth_basic'] ?? null)) { // Basic
            if (is_array($auth_basic)) {
                $auth_basic = implode(":", $auth_basic);
            }

            $http->setOption(CURLOPT_USERPWD, $auth_basic);
        }

        return $request;
    }

    /**
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return  string|resource|StreamInterface|null
     */
    protected function getBody(RequestInterface $request, array $options = [])
    {
        if ($request->getBody() && $request->getBody()->__toString()) {
            return $request->getBody();
        }

        if ($options['json'] ?? false) {
            return is_string($options['json']) ? $options['json'] : json_encode($options['json']);
        }

        $body = $options['body'] ?? null;
        if (($options['form_params'] ?? false) || is_array($body)) {
            $body = $body ?: $options['form_params'];

            return http_build_query($body, '', '&');
        }

        return $body;
    }

    /**
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return string
     * @throws InvalidArgumentException
     */
    protected function getUri(RequestInterface $request, array $options): string
    {
        $uri        = $request->getUri();
        $position   = strpos($uri, '?');
        $parameters = ($position !== false) ? substr($uri, $position + 1) : '';
        $uri        = substr($uri, 0, $position !== false ? $position : strlen($uri));

        if ($query = ($options['query'] ?? null)) {
            if (is_array($query)) {
                $query = http_build_query($query, null, '&', PHP_QUERY_RFC3986);
            }

            if (!is_string($query)) {
                throw new InvalidArgumentException('query must be a string or array');
            }
        }

        if ($parameters = $query ? "$parameters&" . ltrim($query, '&') : $parameters) {
            return rtrim($uri, '?') . '?' . ltrim($parameters, '?');
        }

        return rtrim($uri, '?');
    }

    public function getError(): ?string
    {
        return $this->source->_message;
    }

    /**
     * Set content type if possible (not set and deduction is possible)
     *
     * @param RequestInterface  $request
     * @param array             $options
     *
     * @return RequestInterface
     */
    private function checkContentTypeHeader(RequestInterface $request, array $options): RequestInterface
    {
        if (empty($request->getHeader('Content-Type'))) {
            // Content-Type application/json
            if ($options['json'] ?? false) {
                return $request->withHeader('Content-Type', 'application/json');
            }

            // Content-Type application/x-www-form-urlencoded
            if (($options['form_params'] ?? false) || is_array($options['body'] ?? false)) {
                return $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
            }
        }

        return $request;
    }
}
