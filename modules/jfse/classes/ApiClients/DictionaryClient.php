<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;

class DictionaryClient extends AbstractApiClient
{
    /** @var string The prefix for the user management service, used in all the method names */
    private const SERVICE_NAME = 'DIC';

    /**
     * Return the full name of the method
     *
     * @param string $method
     *
     * @return string
     */
    private static function getMethod(string $method): string
    {
        return self::SERVICE_NAME . '-' . $method;
    }

    /**
     * Returns a list of the regimes for the Social Security
     *
     * @return Response
     */
    public function listRegimes(): Response
    {
        return self::sendRequest(Request::forge(self::getMethod('getListeRegimes'), []));
    }

    /**
     * Returns the list of the organisms (social security centers)
     * As of December 2021, the API method does not take into account the parameters
     *
     * @param string|null $regime_code
     * @param string|null $managing_fund
     * @param string|null $managing_center
     *
     * @return Response
     */
    public function listOrganisms(
        string $regime_code = null,
        string $managing_fund = null,
        string $managing_center = null
    ): Response {
        $parameters = [];

        if ($regime_code) {
            $parameters['codeRegime'] = $regime_code;
        }

        if ($managing_fund) {
            $parameters['codeCaisse'] = $managing_fund;
        }

        if ($managing_center) {
            $parameters['codeCentre'] = $managing_center;
        }

        return self::sendRequest(Request::forge(
            self::getMethod('getListeOrganismes'),
            ['getListeOrganismes' => $parameters]
        ));
    }

    /**
     * Returns the list of the managing codes
     *
     * @return Response
     */
    public function listManagingCodes(): Response
    {
        return self::sendRequest(Request::forge(self::getMethod('getListeCodeGestions'), []));
    }
}
