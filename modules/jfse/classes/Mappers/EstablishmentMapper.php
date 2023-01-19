<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\UserManagement\Establishment;

class EstablishmentMapper extends AbstractMapper
{
    public static function getApiDataFromEntity(Establishment $establishment): array
    {
        $data = [];
        self::addOptionalValue('id', $establishment->getId(), $data);

        $data['type']          = $establishment->getType();
        $data['libelle']       = $establishment->getExonerationLabel();
        $data['noCentre']      = $establishment->getHealthCenterNumber();
        $data['nom']           = $establishment->getName();
        $data['categorie']     = $establishment->getCategory();
        $data['statut']        = $establishment->getStatus();
        $data['modeTarifaire'] = $establishment->getInvoicingMode();

        return $data;
    }

    public static function getArrayFromEntity(Establishment $establishment): array
    {
        $data = [
            'id'                   => $establishment->getId(),
            'type'                 => $establishment->getType(),
            'exoneration_label'    => $establishment->getExonerationLabel(),
            'health_center_number' => $establishment->getHealthCenterNumber(),
            'name'                 => $establishment->getName(),
            'category'             => $establishment->getCategory(),
            'status'               => $establishment->getStatus(),
            'invoicing_mode'       => $establishment->getInvoicingMode()
        ];

        if (!$data['id']) {
            unset($data['id']);
        }

        return $data;
    }

    /**
     * @param Response $response
     *
     * @return Establishment[]
     */
    public static function getListFromResponse(Response $response): array
    {
        $response = $response->getContent();
        $establishments = [];

        $list = CMbArray::get($response, 'lst', []);
        foreach ($list as $item) {
            $establishment = Establishment::hydrate([
                'id'                   => CMbArray::get($item, 'id'),
                'type'                 => CMbArray::get($item, 'type'),
                'exoneration_label'    => CMbArray::get($item, 'libelle'),
                'health_center_number' => CMbArray::get($item, 'noCentre'),
                'name'                 => CMbArray::get($item, 'nom'),
                'category'             => CMbArray::get($item, 'categorie'),
                'status'               => CMbArray::get($item, 'statut'),
                'invoicing_mode'       => CMbArray::get($item, 'modeTarifaire')
            ]);

            $establishments[$establishment->getId()] = $establishment;
        }

        return $establishments;
    }
}
