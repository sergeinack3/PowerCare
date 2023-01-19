<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\History;

use Ox\Mediboard\Jfse\ApiClients\HistoryClient;
use Ox\Mediboard\Jfse\Domain\AbstractService;

class HistoryService extends AbstractService
{
    /** @var HistoryClient The API Client */
    protected $client;

    /**
     * InvoicingService constructor.
     *
     * @param HistoryClient|null $client
     */
    public function __construct(HistoryClient $client = null)
    {
        parent::__construct($client ?? new HistoryClient());
    }

    public function getDataGroups(string $invoice_id, DataGroupTypeEnum $type): bool
    {
        $response = $this->client->getDataGroup($invoice_id, $type);

        return true;
    }
}
