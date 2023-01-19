<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Substitute;

use Ox\Mediboard\Jfse\ApiClients\SubstitutionClient;
use Ox\Mediboard\Jfse\Domain\AbstractService;
use Ox\Mediboard\Jfse\Mappers\SubstitutionMapper;

final class SubstitutionService extends AbstractService
{
    /** @var SubstitutionClient */
    protected $client;

    public function __construct(SubstitutionClient $client = null)
    {
        parent::__construct($client ?? new SubstitutionClient());
    }

    /**
     * @return Substitute[]
     */
    public function getSubstitutesList(): array
    {
        return SubstitutionMapper::getSubstitutesFromResponse($this->client->getSubstitutesList());
    }

    public function getSubstitute(string $national_id): ?Substitute
    {
        $substitutes = $this->getSubstitutesList();

        $substitute = null;
        foreach ($substitutes as $_substitute) {
            if ($_substitute->getNationalId() === $national_id) {
                $substitute = $_substitute;
                break;
            }
        }

        return $substitute;
    }

    public function activateSession(int $substitute_id): void
    {
        $this->client->setSubstituteSessionActivation($substitute_id, true);
    }

    public function deactivateSession(int $substitute_id): void
    {
        $this->client->setSubstituteSessionActivation($substitute_id, false);
    }
}
