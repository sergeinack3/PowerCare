<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\CarePath;

use Ox\Mediboard\Jfse\ApiClients\CarePathClient;
use Ox\Mediboard\Jfse\Domain\AbstractService;

class CarePathService extends AbstractService
{
    /** @var CarePathClient */
    protected $client;

    /** @var string The value of the field source_library of the messages that concerns the care path */
    protected const ERROR_SOURCE = 'PARCOURS DE SOINS';

    /**
     * CarePathService constructor.
     *
     * @param CarePathClient|null $client
     */
    public function __construct(CarePathClient $client = null)
    {
        parent::__construct($client ?? new CarePathClient());
    }

    public function saveCarePath(array $content): bool
    {
        $this->client->setMessagesHandler(
            [$this, 'handleErrorAndWarningMessagesForSourcesLibraryOnly'],
            [self::ERROR_SOURCE]
        );

        $content['doctor'] = CarePathDoctor::hydrate($content);

        $this->client->saveCarePath(CarePath::hydrate($content));

        return true;
    }
}
