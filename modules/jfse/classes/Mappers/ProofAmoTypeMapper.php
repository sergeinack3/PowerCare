<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Api\Response;

class ProofAmoTypeMapper extends AbstractMapper
{
    public static function getArrayFromResponse(Response $response): array
    {
        $types = [];
        $data = CMbArray::get($response->getContent(), 'lstNaturePieceJustificativeAMO', []);

        foreach ($data as $index => $type) {
            $types[$index] = [
                'code' => CMbArray::get($type, 'code'),
                'label' => CMbArray::get($type, 'libelle'),
            ];
        }

        return $types;
    }
}
