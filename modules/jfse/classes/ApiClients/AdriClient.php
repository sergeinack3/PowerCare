<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use DateTime;
use Ox\Mediboard\Jfse\Api\Client;
use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\Vital\Beneficiary;
use Ox\Mediboard\Jfse\Domain\Vital\VitalCard;
use Ox\Mediboard\Jfse\Mappers\AdriMapper;

class AdriClient extends AbstractApiClient
{
    public function __construct(Client $client = null)
    {
        parent::__construct($client ?? new Client());
    }

    public function getInfosInvoiceAdri(string $invoice_id): Response
    {
        return self::sendRequest(Request::forge('FDS-getAdriFacture', ['idFacture' => $invoice_id]), 300);
    }

    private function getInfosAdri(string $method, Beneficiary $beneficiary, string $code = null): Response
    {
        $vital_card_array = (new AdriMapper())->beneficiaryToArray($beneficiary);

        $data = ["teleservice" => $vital_card_array];

        if ($code) {
            $data['codePorteur'] = $code;
        }

        return self::sendRequest(Request::forge($method, $data), 300);
    }

    public function getInfos(Beneficiary $beneficiary, ?string $code = null): Response
    {
        return $this->getInfosAdri('TEL-getAdri', $beneficiary, $code);
    }
}
