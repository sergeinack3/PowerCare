<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\RefundCancelRequest;

use Ox\Mediboard\Jfse\ApiClients\RefundCancelRequestClient;
use Ox\Mediboard\Jfse\Domain\AbstractService;
use Ox\Mediboard\Jfse\Mappers\RefundCancelRequestMapper;

/**
 * Class RefundCancelRequestService
 *
 * @package Ox\Mediboard\Jfse\Domain\RefundCancelRequest
 */
class RefundCancelRequestService extends AbstractService
{
    /** @var RefundCancelRequestClient */
    protected $client;

    /**
     * RefundRequestCancelService constructor.
     *
     * @param RefundCancelRequestClient|null $client
     */
    public function __construct(RefundCancelRequestClient $client = null)
    {
        $this->client = $client ?? new RefundCancelRequestClient();
    }

    /**
     * @param int         $jfse_id
     * @param string|null $date_debut
     * @param string|null $date_fin
     * @param string|null $numero_facture
     * @param string|null $facture_id
     *
     * @return array
     */
    public function getListe(
        int $jfse_id,
        ?string $date_debut,
        ?string $date_fin,
        ?string $numero_facture,
        ?string $facture_id
    ): array {
        $annulations = [];
        $response    = $this->client->getListe(
            $jfse_id,
            $date_debut,
            $date_fin,
            $numero_facture,
            $facture_id
        );

        $data = RefundCancelRequestMapper::getDataFromResponse($response);
        foreach ($data as $refund_request) {
            $annulations[] = RefundCancelRequest::hydrate($refund_request);
        }

        return $annulations;
    }

    /**
     * @param int    $facture_id
     * @param int    $securisation
     * @param string $date_elaboration
     *
     * @return bool
     */
    public function save(int $facture_id, int $securisation, string $date_elaboration): bool
    {
        $this->client->save($facture_id, $securisation, $date_elaboration);

        return true;
    }

    /**
     * @param string $invoice_id
     *
     * @return RefundCancelRequestDetails
     */
    public function getDetails(string $invoice_id): RefundCancelRequestDetails
    {
        $response = $this->client->getDetails($invoice_id);

        return RefundCancelRequestMapper::getInvoiceDetailsFromResponse($response);
    }
}
