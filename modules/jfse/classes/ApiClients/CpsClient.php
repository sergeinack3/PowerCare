<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use Ox\Mediboard\Jfse\Api\Client;
use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;

/**
 * The API client for the CPS service
 *
 * @package Ox\Mediboard\Jfse\API
 */
final class CpsClient extends AbstractApiClient
{
    /** @var int The error code for a missing Cps card */
    public const ERROR_MISSING_CARD = 61441;
    /** @var int The error code for a blocked CPS */
    public const ERROR_BLOCKED_CARD = 61442;
    /** @var int The error code for a wrong cps password */
    public const ERROR_INCORRECT_PASSWORD = 61443;
    /** @var int The error code for an invalid card (or wrongly introduced in the reader) */
    public const ERROR_INVALID_CARD = 61444;
    /** @var int The error code for an error with the CPS Cryptolibs */
    public const ERROR_API_CPS = 65328;

    /** @var array The CPS error codes */
    public static $cps_errors_codes = [
        self::ERROR_MISSING_CARD, self::ERROR_BLOCKED_CARD, self::ERROR_INCORRECT_PASSWORD,
        self::ERROR_API_CPS, self::ERROR_INVALID_CARD
    ];

    /**
     * Call the API method for reading the CPS
     *
     * @param ?int $code
     *
     * @return Response
     */
    public function read(?int $code = null): Response
    {
        $request = Request::forge('LPS-lire', $code ? ['codePorteur' => $code] : []);

        return self::sendRequest($request, 180);
    }
}
