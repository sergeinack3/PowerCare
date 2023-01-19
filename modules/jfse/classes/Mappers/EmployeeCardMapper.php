<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\UserManagement\EmployeeCard;

class EmployeeCardMapper extends AbstractMapper
{
    /**
     * @param Response $response
     *
     * @return EmployeeCard[]
     */
    public static function getLisFromResponse(Response $response): array
    {
        $entities = [];
        foreach (CMbArray::get($response->getContent(), 'lst', []) as $data) {
            $entities[CMbArray::get($data, 'id')] = EmployeeCard::hydrate([
                'id'               => CMbArray::get($data, 'id'),
                'name'             => CMbArray::get($data, 'nom'),
                'invoicing_number' => CMbArray::get($data, 'noFacturation'),
            ]);
        }

        return $entities;
    }

    public static function getApiDataFromEntity(EmployeeCard $card): array
    {
        return [
            'idEtablissement' => $card->getEstablishmentId(),
            'nom'             => $card->getName(),
            'noFacturation'   => $card->getInvoicingNumber(),
        ];
    }
}
