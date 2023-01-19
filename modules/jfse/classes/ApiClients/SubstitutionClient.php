<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;

final class SubstitutionClient extends AbstractApiClient
{
    public function getSubstitutesList(): Response
    {
        return self::sendRequest(Request::forge('MED-getListeMedecinsRemplacants'));
    }

    public function setSubstituteSessionActivation(string $substitute_id, bool $activation): Response
    {
        return self::sendRequest(Request::forge('MED-updateActivationMedecinRemplacant', [
            'updateActivationMedecinRemplacant' => [
                'id'         => $substitute_id,
                'activation' => (int)$activation
            ]
        ]));
    }
}
