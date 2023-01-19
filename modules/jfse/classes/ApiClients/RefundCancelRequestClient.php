<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;

/**
 * Class RefundRequestCancelClient
 *
 * @package Ox\Mediboard\Jfse\ApiClients
 */
class RefundCancelRequestClient extends AbstractApiClient
{
    /**
     * @param int         $jfse_id
     * @param string|null $date_debut
     * @param string|null $date_fin
     * @param string|null $numero_facture
     * @param string|null $facture_id
     *
     * @return Response
     */
    public function getListe(
        int $jfse_id,
        ?string $date_debut,
        ?string $date_fin,
        ?string $numero_facture,
        ?string $facture_id
    ): Response {
        $data = [
            "getListeDREAnnulation" => [
                "idJfse" => $jfse_id,
            ],
        ];
        if ($date_debut !== null) {
            $data["getListeDREAnnulation"]["dateDebut"] = $date_debut;
        }
        if ($date_fin !== null) {
            $data["getListeDREAnnulation"]["dateFin"] = $date_fin;
        }
        if ($numero_facture !== null) {
            $data["getListeDREAnnulation"]["noFacture"] = $numero_facture;
        }
        if ($facture_id !== null) {
            $data["getListeDREAnnulation"]["idFacture"] = $facture_id;
        }

        $request = Request::forge('DAR-getListeDREAnnulation', $data);

        return self::sendRequest($request);
    }

    /**
     * @param int    $facture_id
     * @param int    $securisation
     * @param string $date_elaboration
     *
     * @return Response
     */
    public function save(int $facture_id, int $securisation, string $date_elaboration): Response
    {
        $request = Request::forge(
            'DAR-saveDREAnnulation',
            [
                "saveDREAnnulation" => [
                    "lstIdFactures" => [
                        "idFacture"       => $facture_id,
                        "dateElaboration" => $date_elaboration,
                        "securisation"    => $securisation,
                    ],
                ],
            ]
        );

        return self::sendRequest($request);
    }

    /**
     * @param string $facture_id
     *
     * @return Response
     */
    public function getDetails(string $facture_id): Response
    {
        $request = Request::forge(
            'DAR-getDetailsFactures',
            [
                "getDetailsFactures" => [
                    "lstFactures" => [
                        "idFacture" => $facture_id,
                    ],
                ],
            ]
        );

        return self::sendRequest($request);
    }
}
