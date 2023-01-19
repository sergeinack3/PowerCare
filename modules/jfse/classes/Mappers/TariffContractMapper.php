<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Api\Response;

class TariffContractMapper extends AbstractMapper
{
    public static function getArrayFromResponse(Response $response): array
    {
        $contracts = [];
        $data = CMbArray::get($response->getContent(), 'lst', []);

        foreach ($data as $type) {
            $code = intval(CMbArray::get($type, 'code'));
            $contracts[$code] = [
                'code' => $code,
                'label' => CMbArray::get($type, 'libelle'),
            ];
        }

        return $contracts;
    }
}
