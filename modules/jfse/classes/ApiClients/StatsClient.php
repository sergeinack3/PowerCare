<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use DateTime;
use DateTimeImmutable;
use Ox\Mediboard\Jfse\Api\Client;
use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Mappers\StatsMapper;

class StatsClient extends AbstractApiClient
{
    /** @var StatsMapper */
    private $mapper;

    public function __construct(Client $client = null, StatsMapper $mapper = null)
    {
        parent::__construct($client);

        $this->mapper = $mapper ?? new StatsMapper();
    }

    public function getStats(array $stats_requests): Response
    {
        $data = $this->mapper->statsRequestsToArray($stats_requests);

        $request = Request::forge('STAT-getStatistiques', $data);
        $request->setForceObject(false);

        return self::sendRequest($request);
    }
}
