<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use Ox\Mediboard\Jfse\Api\Client;
use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\CarePath\CarePath;
use Ox\Mediboard\Jfse\Mappers\CarePathMapper;

class CarePathClient extends AbstractApiClient
{
    public function saveCarePath(CarePath $care_path): Response
    {
        $data = CarePathMapper::getSaveRequestFromEntity($care_path);

        return self::sendRequest(Request::forge('FDS-setParcoursSoins', $data));
    }
}
