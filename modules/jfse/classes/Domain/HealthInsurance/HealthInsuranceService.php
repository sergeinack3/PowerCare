<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\HealthInsurance;

use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\ApiClients\HealthInsuranceClient;
use Ox\Mediboard\Jfse\Domain\AbstractService;
use Ox\Mediboard\Jfse\Exceptions\HealthInsurance\HealthInsuranceException;
use Ox\Mediboard\Jfse\Mappers\HealthInsuranceMapper;

/**
 * Class HealtInsuranceService
 *
 * @package Ox\Mediboard\Jfse\Domain\HealthInsurance
 */
class HealthInsuranceService extends AbstractService
{
    /** @var HealthInsuranceClient */
    protected $client;

    /**
     * HealthInsuranceService constructor.
     *
     * @param HealthInsuranceClient|null $client
     */
    public function __construct(HealthInsuranceClient $client = null)
    {
        parent::__construct($client);

        $this->client = $client ?? new HealthInsuranceClient();
    }

    /**
     * @return array
     */
    public function search(
        int $type,
        int $mode,
        string $name = null,
        array $ids = null,
        int $etablissement_id = null
    ): array {
        $this->client->setMessagesHandler([$this, 'handleHealthInsuranceErrorsMessage']);
        $response  = $this->client->search($type, $mode, $name, $ids, $etablissement_id);
        $responses = [];
        $data      = HealthInsuranceMapper::getArrayFromResponse($response);
        foreach ($data as $row) {
            $responses[] = HealthInsurance::hydrate($row);
        }

        return $responses;
    }

    /**
     * @param string|null $code
     * @param string|null $name
     *
     * @return bool
     */
    public function save(string $code = null, string $name = null): bool
    {
        $this->client->setMessagesHandler([$this, 'handleHealthInsuranceErrorsMessage']);

        $this->client->save($code, $name);

        return true;
    }

    public function delete(string $code = ""): Response
    {
        $this->client->setMessagesHandler([$this, 'handleHealthInsuranceErrorsMessage']);

        return $this->client->delete($code);
    }

    /**
     * @param Response $response
     */
    public function handleHealthInsuranceErrorsMessage(Response $response): void
    {
        if ($response->hasMessages()) {
            foreach ($response->getMessages() as $message) {
                if ($message->getSource() === 3202) {
                    //Champ code vide sur le noeud "deleteMutuelle"

                    throw HealthInsuranceException::invalidCode();
                } elseif ($message->getSource() === 3201) {
                    //Champs vide sur le noeud "updateMutuelle"

                    throw HealthInsuranceException::invalidForm();
                } elseif ($message->getSource() === 3200) {
                    //Champs vide sur le noeud "getListeMutuelle"

                    throw HealthInsuranceException::unknownSearchMode();
                }
            }
        }
    }
}
