<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Sync;

use Ox\Mediboard\Jfse\ApiClients\SyncClient;
use Ox\Mediboard\Jfse\Domain\AbstractService;

class SyncService extends AbstractService
{
    /** @var SyncClient */
    protected $client;

    public function __construct(SyncClient $client = null)
    {
        $this->client = $client ?? new SyncClient();
    }

    public function syncFSE(int $jfse_id, array $invoices): bool
    {
        $this->client->syncFSE($jfse_id, ...$invoices);

        return true;
    }
}
