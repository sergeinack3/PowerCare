<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use Ox\Mediboard\Jfse\Api\Client;
use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;

class SyncClient extends AbstractApiClient
{
    public function __construct(Client $client = null)
    {
        parent::__construct($client ?? new Client());
    }

    public function syncFSE(int $jfse_id, int ...$invoices): Response
    {
        $data = [
            "lstFactures" => array_map(
                function (int $invoice) use ($jfse_id): array {
                    return ["idJfse" => $jfse_id, "noFacture" => $invoice];
                },
                $invoices
            ),
        ];

        return self::sendRequest(Request::forge('SYN-synchroniser', $data));
    }
}
