<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Api\Client;
use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Exceptions\Invoice\InvoiceException;

/**
 * The API client for the AMO proof service
 *
 * @package Ox\Mediboard\Jfse\ApiClients
 */
class ProofAmoClient extends AbstractApiClient
{
    public const INVOICE_ERROR = 2001;

    /**
     * Call the API method for listing proofs
     *
     * @return Response
     */
    public function listProofTypes(): Response
    {
        $request = Request::forge('PJAMO-getListeNaturePieceJustificativeAMO', []);

        return self::sendRequest($request);
    }

    public function saveProofAmo(string $invoice_id, int $nature, ?DateTimeImmutable $date, ?int $origin): Response
    {
        $data = [
            "idFacture"             => $invoice_id,
            "pieceJustificativeAMO" => [
                "nature" => $nature,
            ],
        ];
        if ($date) {
            $data["pieceJustificativeAMO"]["date"] = $date->format('Ymd');
        }
        if ($origin) {
            $data["pieceJustificativeAMO"]["origine"] = $origin;
        }

        $request = Request::forge('FDS-setPieceJustificativeAMO', $data);

        return self::sendRequest($request);
    }
}
