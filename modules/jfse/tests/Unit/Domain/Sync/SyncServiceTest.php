<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Sync;

use Ox\Mediboard\Jfse\ApiClients\SyncClient;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class SyncServiceTest extends UnitTestJfse
{
    public function testSyncFSE(): void
    {
        $empty_return = '{"method": {"output": {}}}';
        $client = $this->makeClientFromGuzzleResponses([$this->makeJsonGuzzleResponse(200, $empty_return)]);

        $this->assertTrue((new SyncService(new SyncClient($client)))->syncFSE(12, [2, 3, 4]));
    }
}
