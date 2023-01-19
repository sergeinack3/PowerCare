<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Version;

use Ox\Mediboard\Jfse\ApiClients\VersionClient;
use Ox\Mediboard\Jfse\Domain\AbstractService;
use Ox\Mediboard\Jfse\Domain\Version\ApiVersion;
use Ox\Mediboard\Jfse\Mappers\VersionMapper;

class VersionService extends AbstractService
{
    /** @var VersionClient */
    protected $client;

    public function __construct(VersionClient $client = null)
    {
        parent::__construct($client ?? new VersionClient());
    }

    public function getApiVersion(int $code_cps): ApiVersion
    {
        $response = $this->client->getApiVersions($code_cps);

        $api_version = (new VersionMapper())->arrayToApiVersion($response->getContent());

        return $api_version;
    }

    public function getVersion(int $jfse_id): Version
    {
        $response = $this->client->getVersion($jfse_id);

        return (new VersionMapper())->arrayToVersion($response->getContent());
    }
}
