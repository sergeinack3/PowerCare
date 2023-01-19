<?php

/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use Ox\Core\CMbException;
use Ox\Core\CMbSecurity;
use Ox\Core\CMbServer;
use Ox\Core\Contracts\Client\MLLPClientInterface;
use Ox\Interop\Eai\Resilience\ClientContext;
use Ox\Mediboard\System\CExchangeSource;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Throwable;

class CMLLPLegacy implements MLLPClientInterface
{
    /** @var CSourceMLLP */
    private $source;

    /** @var EventDispatcher */
    protected $dispatcher;

    /**
     * @param CExchangeSource $mllp_source
     *
     * @return void
     */
    public function init(CExchangeSource $mllp_source): void
    {
        $this->source     = $mllp_source;
        $this->dispatcher = $mllp_source->_dispatcher;
    }

    /**
     * @param string|null    $function_name
     * @param                $server
     * @param Throwable|null $throwable
     *
     * @return ClientContext
     */
    public function getContext(
        ?string $function_name = null,
        $server = null,
        ?Throwable $throwable = null
    ): ClientContext {
        $arguments = [];
        if ($function_name) {
            $arguments['function_name'] = $function_name;
        }

        if ($server) {
            $arguments['server'] = $server;
        }

        return (new ClientContext($this, $this->source))
            ->setArguments($arguments)
            ->setThrowable($throwable);
    }

    /**
     * @param callable $call
     * @param string   $function_name
     *
     * @return mixed
     */
    protected function dispatch(array $call_args, string $function_name)
    {
        if (is_array($call_args)) {
            $server = $call_args[1][0] ?? null;
        } else {
            $server = null;
        }

        $context = $this->getContext($function_name, $server);
        if (is_array($call_args)) {
            $arguments = $call_args[1] ?? [];
            $callable = $call_args[0];
            $context->setRequest($arguments);
        } else {
            $callable = $call_args;
            $arguments = [];
        }
        $this->dispatcher->dispatch($context, self::EVENT_BEFORE_REQUEST);
        $result = call_user_func($callable, $arguments);
        $context->setResponse($result);
        $this->dispatcher->dispatch($context, self::EVENT_AFTER_REQUEST);

        return $result;
    }

    /**
     * @return bool
     * @throws CMbException
     * @throws Throwable
     */
    public function isReachableSource(): bool
    {
        try {
            $this->getSocketClient();
        } catch (Exception $e) {
            $this->source->_reachable = 0;
            $this->source->_message   = $e->getMessage();
            $this->dispatcher->dispatch($this->getContext('isReachableSource', $e), self::EVENT_EXCEPTION);
            return false;
        } catch (\Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('isReachableSource', $e), self::EVENT_EXCEPTION);
            throw $e;
        }

        return true;
    }

    /**
     * @return bool
     * @throws CMbException
     * @throws Throwable
     */
    public function isAuthentificate(): bool
    {
        return $this->isReachableSource();
    }

    /**
     * @return int
     */
    public function getResponseTime(): int
    {
        $response_time = CMbServer::getUrlResponseTime($this->source->host, $this->source->port);
        return $this->source->_response_time = intval($response_time);
    }

    /**
     * @return void
     * @throws CMbException
     * @throws Throwable
     */
    public function send(): void
    {
        $data = $this->source::TRAILING . $this->source->_data . $this->source::LEADING;
        $socket = $this->getSocketClient();

        if ($this->source->timeout_period_stream) {
            stream_set_timeout($socket, $this->source->timeout_period_stream);
        }

        $call = [
            function ($args) use ($socket, $data) {
                return fwrite(...$args);
            },
            [$socket, $data, strlen($data)],
        ];

        $this->dispatch($call, 'send');

        $acq = $this->receive();

        $this->source->_acquittement = trim(str_replace("\x1C", "", $acq));
    }

    /**
     * @return string
     * @throws CMbException
     * @throws Throwable
     */
    public function receive(): string
    {
        $servers = [$this->getSocketClient()];

        if ($this->source->timeout_period_stream) {
            stream_set_timeout($this->source->_socket_client, $this->source->timeout_period_stream);
        }

        $data = "";

        try {
            do {
                $var    = $write = null;
                $except = null;
                while (@stream_select($servers, $var, $except, $this->source->timeout_socket) === false) {
                    ;
                }

                $call = [
                    function ($args) {
                        return stream_get_contents(...$args);
                    },
                    [$this->source->_socket_client],
                ];

                $buf = $this->dispatch($call, 'stream_get_contents');

                $data .= $buf;
            } while ($buf);
        } catch (Throwable $e) {
            $this->dispatcher->dispatch($this->getContext('receive', $e), self::EVENT_EXCEPTION);

            throw $e;
        }

        return $data;
    }

    /**
     * @return mixed
     * @throws CMbException
     */
    public function getSocketClient()
    {
        if ($this->source->_socket_client) {
            return $this->source->_socket_client;
        }

        $address = $this->source->host . ":" . $this->source->port;
        $context = stream_context_create();

        if ($this->source->ssl_enabled && $this->source->ssl_certificate && is_readable(
                $this->source->ssl_certificate
            )) {
            $address = "tls://$address";

            stream_context_set_option($context, 'ssl', 'local_cert', $this->source->ssl_certificate);

            if ($this->source->ssl_passphrase) {
                $ssl_passphrase = $this->source->getPassword($this->source->ssl_passphrase, "iv_passphrase");
                stream_context_set_option($context, 'ssl', 'passphrase', $ssl_passphrase);
            }
        }

        $errstr = null;
        $errno = null;

        $call = [
            function ($args) use ($address, $errstr, $errno, $context) {
                return $this->source->_socket_client = @stream_socket_client(...$args);
            },
            [
                $address,
                $errno,
                $errstr,
                $this->source->timeout_socket,
                STREAM_CLIENT_CONNECT,
                $context,
            ],
        ];

        if (!$socket_client = $this->dispatch($call, 'getSocketClient')) {
            $exception = new CMbException("CSourceMLLP-unreachable-source", $this->source->name, $errno, $errstr);
            $this->dispatcher->dispatch($this->getContext('getSocketClient', $exception), self::EVENT_EXCEPTION);

            throw $exception;
        }

        stream_set_blocking($socket_client, $this->source->set_blocking);

        return $socket_client;
    }

    /**
     * @return string
     * @throws CMbException
     * @throws Throwable
     */
    public function getData(): string
    {
        return $this->receive();
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->source->_message;
    }
}
