<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\MedicalAct\MedicalAct;
use Ox\Mediboard\Jfse\Mappers\MedicalActMapper;

/**
 * Class MedicalActClient
 *
 * @package Ox\Mediboard\Jfse\ApiClients
 */
class MedicalActClient extends AbstractApiClient
{
    public function setMedicalAct(
        string $invoice_id,
        MedicalAct $act
    ): Response {
        $data = [
            "idFacture"    => $invoice_id,
            "lstCotations" => MedicalActMapper::getArrayFromMedicalAct($act),
        ];
        $request = Request::forge('FDS-setCotation', $data)->setForceObject(false);

        return self::sendRequest($request, 30);
    }

    public function deleteAct(string $invoice_id, string $act_id): Response
    {
        $data = [
            'idFacture' => $invoice_id,
            'lstCotations' => [
                'id' => $act_id
            ]
        ];
        $request = Request::forge('FDS-removeCotation', $data);

        return self::sendRequest($request);
    }
}
