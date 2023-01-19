<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;

class VersionClient extends AbstractApiClient
{

    public function getApiVersions(int $code_cps): Response
    {
        return self::sendRequest(Request::forge('CFG-lectureApi', ['codePorteur' => $code_cps]), 30);
    }

    public function getVersion(int $jfse_id): Response
    {
        return self::sendRequest(Request::forge('CFG-version', ['version' => ["idJfse" => $jfse_id]]));
    }
}
