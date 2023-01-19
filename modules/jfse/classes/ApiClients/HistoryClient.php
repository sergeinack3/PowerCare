<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\History\DataGroupTypeEnum;

class HistoryClient extends AbstractApiClient
{
    /**
     * The data groups will be displayed by Jfse, hence the long timeout,
     * has the server won't respond until the user has closed the Jfse Window
     *
     * @param string            $invoice_id
     * @param DataGroupTypeEnum $type
     *
     * @return Response
     */
    public function getDataGroup(string $invoice_id, DataGroupTypeEnum $type): Response
    {
        return self::sendRequest(Request::forge('TAB-visualiserGroupeInfo', [
            'visualiserGroupeInfo' => [
                'idFacture' => $invoice_id,
                'typeGroupe' => $type->getValue()
            ]
        ]), 300);
    }

    /**
     * Return the invoices for the given period.
     * The user is the one set in the cache (see Utils::getJfseUserId)
     *
     * @param DateTimeImmutable $begin_date
     * @param DateTimeImmutable $end_date
     *
     * @return Response
     */
    public function getInvoiceHistory(DateTimeImmutable $begin_date, DateTimeImmutable $end_date): Response
    {
        return self::sendRequest(Request::forge('TAB-getTableauDeBord', [
            'getTableauDeBord' => [
                'dateDebut' => $begin_date->format('Ymd'),
                'dateFin'   => $end_date->format('Ymd'),
                'modeCS'    => 0,
                'archive'   => 0,
                'cplx'      => 0,
            ]
        ]), 300);
    }

    /**
     * Return the data of the given invoice
     *
     * @param string $invoice_id
     *
     * @return Response
     */
    public function getInvoiceDetails(string $invoice_id): Response
    {
        return self::sendRequest(Request::forge('TAB-getDetailFacture', [
            'getDetailFacture' => [
                'idFacture' => $invoice_id,
            ]
        ]), 300);
    }
}
