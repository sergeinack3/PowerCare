<?php

/**
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Webservices;

use Ox\Core\Contracts\Client\SOAPClientInterface;
use Ox\Interop\Eai\Resilience\CircuitBreaker;
use Ox\Interop\Ftp\CustomRequestAnalyserInterface;
use Ox\Interop\Ftp\ResponseAnalyser;
use Ox\Mediboard\System\CExchangeSource;

class ResilienceSOAPClient implements SOAPClientInterface
{
    /** @var SOAPClientInterface */
    public SOAPClientInterface $client;

    /** @var CircuitBreaker */
    private CircuitBreaker $circuit;

    /** @var ResponseAnalyser */
    private ResponseAnalyser $analyser;

    /** @var CSourceSOAP */
    private CSourceSOAP $source;

    /**
     * @param SOAPClientInterface $client
     * @param CExchangeSource     $source
     */
    public function __construct(SOAPClientInterface $client, CSourceSOAP $source)
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
            return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
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

    public function send(string $event_name = null, bool $flatten = false): bool
    {
        $call = function () use ($event_name, $flatten) {
            return $this->client->send($event_name, $flatten);
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    public function functionExist(string $function_name): bool
    {
        $call = function () use ($function_name) {
            return $this->client->functionExist($function_name);
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    /**
     * @return bool
     * @throws \Ox\Core\CMbException
     * @throws \Ox\Interop\Ftp\CircuitBreakerException
     */
    public function hasError(): bool
    {
        $call = function () {
            return $this->client->hasError();
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    public function getLastRequest(): string
    {
        $call = function () {
            return $this->client->getLastRequest();
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    public function getLastResponse(): string
    {
        $call = function () {
            return $this->client->getLastResponse();
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    public function setHeaders(array $headers): void
    {
        $call = function () use ($headers) {
            return $this->client->setHeaders($headers);
        };

        $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    public function getHeaders(): array
    {
        $call = function () {
            return $this->client->getHeaders();
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    public function setNamespaces(array $namespaces): void
    {
        $call = function () use ($namespaces) {
            return $this->client->setNamespaces($namespaces);
        };

        $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    public function getTrace(CEchangeSOAP $exchange_source): void
    {
        $call = function () use ($exchange_source) {
            return $this->client->getTrace($exchange_source);
        };

        $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    public function getFunctions(): array
    {
        $call = function () {
            return $this->client->getFunctions();
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser) ?: [];
    }

    public function getTypes(): array
    {
        $call = function () {
            return $this->client->getTypes();
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    public function checkServiceAvailability(): void
    {
        $call = function () {
            return $this->client->checkServiceAvailability();
        };

        $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    public function getError()
    {
        $call = function () {
            return $this->client->getError();
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }
}
