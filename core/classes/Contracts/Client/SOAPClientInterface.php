<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Contracts\Client;

use Ox\Core\CMbException;
use Ox\Interop\Webservices\CEchangeSOAP;
use Ox\Mediboard\System\CExchangeSource;

interface SOAPClientInterface extends ClientInterface
{
    /**
     * @param CExchangeSource $source
     */
    public function init(CExchangeSource $source): void;

    /**
     * @param string|null $event_name
     * @param bool   $flatten
     *
     * @return bool
     */
    public function send(string $event_name = null, bool $flatten = false): bool;

    /**
     * @param string $function_name
     *
     * @return bool
     */
    public function functionExist(string $function_name): bool;

    /**
     * @return bool
     */
    public function hasError(): bool;

    /**
     * @return string
     */
    public function getLastRequest(): string;

    /**
     * @return string
     */
    public function getLastResponse(): string;

    /**
     * @param array $headers
     *
     * @return void
     */
    public function setHeaders(array $headers): void;

    /**
     * @return array
     */
    public function getHeaders(): array;

    /**
     * @param array $namespaces
     *
     * @return void
     */
    public function setNamespaces(array $namespaces): void;

    /**
     * @param CEchangeSOAP $exchange_source
     *
     * @return void
     */
    public function getTrace(CEchangeSOAP $exchange_source): void;

    /**
     * Return the list of the operation of the WSDL
     *
     * @return array An array who contains all the operation of the WSDL
     */
    public function getFunctions(): array;

    /**
     * Returns an array of functions described in the WSDL for the Web service.
     *
     * @return array The array of SOAP function prototype
     */
    public function getTypes(): array;


    /**
     * Check service availability
     *
     * @throws CMbException
     *
     * @return void
     */
    public function checkServiceAvailability(): void;
}
