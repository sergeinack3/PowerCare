<?php

/**
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Resilience;

use Ox\Core\CMbSecurity;
use Ox\Core\Contracts\Client\ClientInterface;
use Ox\Mediboard\System\CExchangeSource;
use Exception;
use Stringable;
use Throwable;

final class ClientContext
{
    /** @var ClientInterface  */
    private ClientInterface $client;

    /** @var CExchangeSource  */
    private CExchangeSource $source;

    /** @var mixed */
    private $request;

    /** @var mixed  */
    private $response;

    /** @var Throwable|null  */
    private ?Throwable $throwable = null;

    private array $arguments = [];

    /**
     * @param ClientInterface $client
     * @param CExchangeSource $source
     * @param mixed           $response
     * @param Exception|null  $e
     */
    public function __construct(ClientInterface $client, CExchangeSource $source)
    {
        $this->client = $client;
        $this->source = $source;
    }

    /**
     * @param array $arguments
     *
     * @return ClientContext
     */
    public function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Exception|null|\SoapFault
     */
    public function getThrowable()
    {
        return $this->throwable;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return CExchangeSource
     */
    public function getSource(): CExchangeSource
    {
        return $this->source;
    }

    /**
     * @return ClientInterface
     */
    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    /**
     * @param mixed $request
     *
     * @return ClientContext
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @param mixed $response
     *
     * @return ClientContext
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @param Throwable|null $throwable
     *
     * @return ClientContext
     */
    public function setThrowable(?Throwable $throwable): ClientContext
    {
        $this->throwable = $throwable;

        return $this;
    }
}
