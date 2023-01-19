<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\RefundCancelRequest\RefundCancelRequestDetails;

/**
 * Class RefundRequestCancelMapper
 *
 * @package Ox\Mediboard\Jfse\Mappers
 */
class RefundCancelRequestMapper extends AbstractMapper
{
    /**
     * @param Response $response
     *
     * @return array
     */
    public static function getDataFromResponse(Response $response): array
    {
        $refund_requests_cancel = [];

        $data = CMbArray::get($response->getContent(), 'lstDREAnnulations', []);
        foreach ($data as $refund_request) {
            $refund_requests_cancel[] = [
                "type"           => CMbArray::get($refund_request, "type"),
                "jfse_id"        => CMbArray::get($refund_request, "idJfse"),
                "dre_lot_number" => CMbArray::get($refund_request, "noLotDRE"),
                "fse_lot_number" => CMbArray::get($refund_request, "noLotFSE"),
                "invoice_number" => CMbArray::get($refund_request, "noFacture"),
                "invoice_id"     => CMbArray::get($refund_request, "idFacture"),
            ];
        }

        return $refund_requests_cancel;
    }

    /**
     * @param Response $response
     *
     * @return RefundCancelRequestDetails
     */
    public static function getInvoiceDetailsFromResponse(Response $response): RefundCancelRequestDetails
    {
        $data = CMbArray::get($response->getContent(), 'lstDetailsFactures', '');

        return RefundCancelRequestDetails::hydrate([
            "dre_number"             => CMbArray::get($data, "numeroDre"),
            "invoice_id"             => intval(CMbArray::get($data, "idFacture")),
            "invoice_number"         => CMbArray::get($data, "noFacture"),
            "beneficiary_last_name"  => CMbArray::get($data, "nomBeneficiaire"),
            "beneficiary_first_name" => CMbArray::get($data, "prenomBeneficiaire"),
            "securisation"           => intval(CMbArray::get($data, "securisation")),
            "ps_name"                => CMbArray::get($data, "nomPs"),
            "date_elaboration"       => intval(CMbArray::get($data, "dateElaboration")),
        ]);
    }
}
