<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;

final class NoemieClient extends AbstractApiClient
{
    /**
     * @param int                    $jfse_user_id
     * @param DateTimeImmutable|null $date_min
     * @param DateTimeImmutable|null $date_max
     * @param string|null            $invoice_id
     *
     * @return Response
     */
    public function getInvoicesThirdPartyPayments(
        int $jfse_user_id,
        DateTimeImmutable $date_min = null,
        DateTimeImmutable $date_max = null,
        string $invoice_id = null
    ): Response {
        $data = [
            'idJfse' => $jfse_user_id
        ];

        if ($date_min) {
            $data['dateDebut'] = $date_min->format('Ymd');
        }
        if ($date_max) {
            $data['dateFin'] = $date_max->format('Ymd');
        }

        if ($invoice_id) {
            $data['idFacture'] = $invoice_id;
        }

        return self::sendRequest(
            Request::forge('NOE-getListeFacturesTP', ['getListeFacturesTP' => $data])->setForceObject(false),
            30
        );
    }

    /**
     * @param string                 $jfse_user_id
     * @param DateTimeImmutable|null $date_min
     * @param DateTimeImmutable|null $date_max
     *
     * @return Response
     */
    public function getPayments(
        string $jfse_user_id,
        DateTimeImmutable $date_min = null,
        DateTimeImmutable $date_max = null
    ): Response {
        $data = [
            'idJfse' => $jfse_user_id
        ];

        if ($date_min) {
            $data['dateDebut'] = $date_min->format('Ymd');
        }
        if ($date_max) {
            $data['dateFin'] = $date_max->format('Ymd');
        }

        $request = Request::forge('NOE-getListeVirements', ['getListeVirements' => $data])->setForceObject(false);

        return self::sendRequest($request, 30);
    }

    /**
     * Make an API call to the method NOE-getDetailsVirements
     *
     * @param string $payment_id
     *
     * @return Response
     */
    public function getPaymentDetails(string $payment_id): Response
    {
        return self::sendRequest(Request::forge('NOE-getDetailsVirements', [
            'getDetailsVirements' => ['idVirement' => $payment_id]
        ])->setForceObject(false));
    }

    /**
     * @param string        $jfse_user_id
     * @param DateTimeImmutable|null $date_min
     * @param DateTimeImmutable|null $date_max
     * @param bool          $all_acknowledgements If false, only the new acknowledgement will be returned
     *
     * @return Response
     */
    public function getPositiveAcknowledgements(
        string $jfse_user_id,
        DateTimeImmutable $date_min = null,
        DateTimeImmutable $date_max = null,
        bool $all_acknowledgements = true
    ): Response {
        $data = [
            'idJfse' => $jfse_user_id
        ];

        if ($date_min) {
            $data['dateDebut'] = $date_min->format('Ymd');
        }
        if ($date_max) {
            $data['dateFin'] = $date_max->format('Ymd');
        }

        $data['allARLs'] = intval($all_acknowledgements);

        return self::sendRequest(
            Request::forge('NOE-getListeARLPositifs', ['getListeARLPositifs' => $data])->setForceObject(false)
        );
    }

    /**
     * @param string        $jfse_user_id
     *
     * @return Response
     */
    public function getNegativeAcknowledgements(string $jfse_user_id): Response
    {
        return self::sendRequest(Request::forge('NOE-getListeARLNegatifs', [
            'getListeARLNegatifs' => ['idJfse' => $jfse_user_id]
        ])->setForceObject(false));
    }

    /**
     * @param array        $set_numbers  An array containing one or more set number
     * @param string|null  $jfse_user_id
     *
     * @return Response
     */
    public function getInvoicesBySets(array $set_numbers, string $jfse_user_id = null): Response
    {
        $data = [
            'lstLots' => []
        ];
        if ($jfse_user_id) {
            $data['idJfse'] = $jfse_user_id;
        }

        foreach ($set_numbers as $set_number) {
            $data['lstlots'][] = [
                'noLot' => $set_number
            ];
        }

        return self::sendRequest(Request::forge('NOE-getListeFacturesLots', [
            'getListeFacturesLots' => $data
        ])->setForceObject(false));
    }
}
