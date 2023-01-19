<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Api\Response;

/**
 * Class HealthInsuranceMapper
 *
 * @package Ox\Mediboard\Jfse\Mappers
 */
class HealthInsuranceMapper extends AbstractMapper
{
    public static function getArrayFromResponse(Response $response): array
    {
        $insurances = [];
        $data       = CMbArray::get($response->getContent(), 'lst', []);
        foreach ($data as $insurance) {
            $insurances[] = [
                "code"                 => CMbArray::get($insurance, "code"),
                "name"                 => CMbArray::get($insurance, "nom"),
                "type_of_organization" => CMbArray::get($insurance, "typeOrganisme"),
            ];
        }

        return $insurances;
    }

    /**
     * @param string $code
     * @param string $name
     * @param array  $ids
     * @param int    $etablissement_id
     *
     * @return array[]
     */
    public function getArrayFromData(
        string $code = "",
        string $name = "",
        array $ids = [],
        int $etablissement_id = 0
    ): array {
        $data = [
            "updateMutuelle" => [
                "nom"  => $name,
                "code" => $code,
            ],
        ];
        self::addOptionalValue("idEtablissement", $etablissement_id, $data['updateMutuelle']);
        self::addOptionalValue("lstIdJfse", $ids, $data['updateMutuelle']);

        return $data;
    }
}
