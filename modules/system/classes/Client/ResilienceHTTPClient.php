<?php

/**
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Client;

use Ox\Core\Contracts\Client\HTTPClientInterface;
use Ox\Interop\Eai\Resilience\CircuitBreaker;
use Ox\Interop\Ftp\CustomRequestAnalyserInterface;
use Ox\Interop\Ftp\ResponseAnalyser;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceHTTP;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ResilienceHTTPClient implements HTTPClientInterface
{
    /** @var HTTPClientInterface */
    public HTTPClientInterface $client;

    /** @var CircuitBreaker */
    private CircuitBreaker $circuit;

    /** @var ResponseAnalyser */
    private ResponseAnalyser $analyser;

    /** @var CSourceHTTP */
    private CSourceHTTP $source;

    /**
     * @param HTTPClientInterface $client
     * @param CExchangeSource     $source
     */
    public function __construct(HTTPClientInterface $client, CSourceHTTP $source)
    {
        $this->client = $client;

        $this->analyser = $client instanceof CustomRequestAnalyserInterface
            ? $client->getRequestAnalyser() : new ResponseAnalyser();

        $this->source  = $source;
        $this->circuit = new CircuitBreaker();
    }

    /**
     * @param CExchangeSource $source
     *
     * @return void
     */
    public function init(CExchangeSource $source): void
    {
        $this->client->init($source);
    }


    public function isReachableSource(): bool
    {
        $call = function () {
            return $this->client->isReachableSource();
        };
        try {
            return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
        } catch (Throwable $e) {
            $this->source->_message = $e->getMessage();

            return false;
        }
    }

    public function isAuthentificate(): bool
    {
        $call = function () {
            return $this->client->isAuthentificate();
        };
        try {
            return $this->circuit->isAuthentificate($this->source, $this->client, $call, $this->analyser);
        } catch (Throwable $e) {
            $this->source->_message = $e->getMessage();

            return false;
        }
    }

    public function getResponseTime(): int
    {
        $call = function () {
            return $this->client->getResponseTime();
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    public function send(RequestInterface $request, array $options = []): ResponseInterface
    {
        $call = function () use ($request, $options) {
            return $this->client->send($request, $options);
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    public function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        $call = function () use ($method, $uri, $options) {
            return $this->client->request($method, $uri, $options);
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }
}
