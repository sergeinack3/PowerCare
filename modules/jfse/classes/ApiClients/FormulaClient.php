<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;

/**
 * Class FormulaClient
 *
 * @package Ox\Mediboard\Jfse\ApiClients
 */
class FormulaClient extends AbstractApiClient
{
    /**
     * @return Response
     */
    public function getListeOperandes(): Response
    {
        $request = Request::forge('FORM-getListeOperandes');

        return self::sendRequest($request);
    }

    /**
     * @return Response
     */
    public function getListeFormulesHorsSts(): Response
    {
        $request = Request::forge('FORM-getListeFormulesHorsSTS');

        return self::sendRequest($request);
    }

    public function save(
        string $nom,
        float $multiplicateur,
        float $plafond,
        string $operande1,
        string $operande2,
        string $operateur,
        int $idFormule = 0
    ): Response {
        $request = Request::forge(
            'FORM-updateFormuleHorsSTS',
            [
                "updateFormuleHorsSTS" => [
                    "idFormule"      => $idFormule,
                    "nomFormule"     => $nom,
                    "multiplicateur" => $multiplicateur,
                    "plafond"        => $plafond,
                    "operande1"      => $operande1,
                    "operande2"      => $operande2,
                    "operateur"      => $operateur,
                ],
            ]
        );

        return self::sendRequest($request);
    }

    public function delete(int $idFormule): Response
    {
        $request = Request::forge(
            'FORM-deleteFormuleHorsSTS',
            [
                "deleteFormuleHorsSTS" => [
                    "idFormule" => $idFormule,
                ],
            ]
        );

        return self::sendRequest($request);
    }

    public function getFormulasFromInvoice(string $invoice_id): Response
    {
        return self::sendRequest(Request::forge('FDS-getListeFormules', ["idFacture" => $invoice_id]));
    }
}
