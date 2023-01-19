<?php

/**
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use Ox\Core\Contracts\Client\MLLPClientInterface;
use Ox\Interop\Eai\Resilience\CircuitBreaker;
use Ox\Interop\Ftp\CircuitBreakerException;
use Ox\Interop\Ftp\CustomRequestAnalyserInterface;
use Ox\Interop\Ftp\ResponseAnalyser;
use Ox\Mediboard\System\CExchangeSource;
use Throwable;

class ResilienceMLLPClient implements MLLPClientInterface
{
    /** @var MLLPClientInterface */
    public MLLPClientInterface $client;

    /** @var CircuitBreaker */
    private CircuitBreaker $circuit;

    /** @var ResponseAnalyser */
    private ResponseAnalyser $analyser;

    /** @var CSourceMLLP */
    private CSourceMLLP $source;

    /**
     * @param MLLPClientInterface $client
     * @param CExchangeSource     $source
     */
    public function __construct(MLLPClientInterface $client, CSourceMLLP $source)
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

    /**
     * @return bool
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
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

    /**
     * @return bool
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
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

    /**
     * @return int
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
    public function getResponseTime(): int
    {
        $call = function () {
            return $this->client->getResponseTime();
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    /**
     * @return void
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
    public function send(): void
    {
        $call = function () {
            return $this->client->send();
        };

        $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    /**
     * @return string
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
    public function receive(): string
    {
        $call = function () {
            return $this->client->receive();
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    /**
     * @return \phpDocumentor\Reflection\Types\Mixed_|null
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
    public function getSocketClient()
    {
        $call = function () {
            return $this->client->getSocketClient();
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    /**
     * @return \phpDocumentor\Reflection\Types\Mixed_|null
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
    public function getData()
    {
        $call = function () {
            return $this->client->getData();
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }

    /**
     * @return \phpDocumentor\Reflection\Types\Mixed_|null
     * @throws CircuitBreakerException
     * @throws \Ox\Core\CMbException
     */
    public function getError()
    {
        $call = function () {
            return $this->client->getError();
        };

        return $this->circuit->execute($this->source, $this->client, $call, $this->analyser);
    }
}
