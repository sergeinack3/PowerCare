<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\UserManagement;

use Ox\Core\Cache;
use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\ApiClients\EstablishmentClient;
use Ox\Mediboard\Jfse\Domain\AbstractService;
use Ox\Mediboard\Jfse\Exceptions\ApiMessageException;
use Ox\Mediboard\Jfse\Exceptions\JfseException;
use Ox\Mediboard\Jfse\Exceptions\UserManagement\EstablishmentException;
use Ox\Mediboard\Jfse\Mappers\EmployeeCardMapper;
use Ox\Mediboard\Jfse\Mappers\EstablishmentConfigurationMapper;
use Ox\Mediboard\Jfse\Mappers\EstablishmentMapper;
use Ox\Mediboard\Jfse\Mappers\UserMapper;

class EstablishmentService extends AbstractService
{
    /** @var EstablishmentClient */
    protected $client;

    /** @var Cache The establishment cache */
    protected $cache;

    public function __construct(EstablishmentClient $client = null, Cache $cache = null)
    {
        parent::__construct($client ?? new EstablishmentClient());
        $this->cache = $cache ?? new Cache('Jfse-Establishment', 'List', Cache::OUTER, 14400);
    }

    /**
     * @return Establishment[]
     */
    public function listEstablishments(): array
    {
        $establishments = $this->getEstablishmentsFromCache();
        if (empty($establishments)) {
            $establishments = $this->getEstablishmentsFromApi();
            $this->putEstablishmentsInCache($establishments);
        }

        return $establishments;
    }

    public function searchEstablishment(string $name = null, string $category = null, int $type = null): array
    {
        $results = [];

        $establishments = $this->listEstablishments();

        foreach ($establishments as $establishment) {
            if (
                ($name === null || str_contains(strtolower($establishment->getName()), strtolower($name)))
                && ($category === null || $establishment->getCategory() === $category)
                && ($type === null || $establishment->getType() === $type)
            ) {
                $results[] = $establishment;
            }
        }

        return $results;
    }

    public function getEstablishment(int $id): Establishment
    {
        $establishments = $this->listEstablishments();

        if (!array_key_exists($id, $establishments)) {
            throw EstablishmentException::unknownEstablishmentId($id);
        }

        return $establishments[$id];
    }

    public function createEstablishment(
        Establishment $establishment,
        string $object_class = null,
        int $object_id = null
    ): bool {
        $response = $this->client->updateEstablishment($establishment);

        $this->deleteEstablishmentsCache();
        if ($id = CMbArray::get('idEtablissement', $response->getContent())) {
            $establishment = Establishment::hydrate(['id' => $id]);
            $establishment->createDataModel();

            if ($object_class && $object_id) {
                $this->linkEstablishmentToObject($id, $object_class, $object_id);
            }
        }

        return true;
    }

    public function updateEstablishment(Establishment $establishment): bool
    {
        $this->client->updateEstablishment($establishment);

        $this->deleteEstablishmentsCache();

        return true;
    }

    public function linkEstablishmentToObject(string $establishment_id, string $object_class, int $object_id): bool
    {
        $establishment = Establishment::hydrate(['id' => $establishment_id]);
        $establishment->linkDataModelToObject($object_id, $object_class);

        return true;
    }

    public function unlinkEstablishmentToObject(string $establishment_id): bool
    {
        $establishment = Establishment::hydrate(['id' => $establishment_id]);
        $establishment->unlinkDataModelFromObject();

        return true;
    }


    public function deleteEstablishment(int $id, int $delete_users = 0): bool
    {
        $establishment = Establishment::hydrate(['id' => $id]);

        $this->client->deleteEstablishment($id, $delete_users);
        $this->deleteEstablishmentsCache();
        $establishment->loadDataModel();

        return $establishment->deleteDataModel();
    }

    /**
     * @param Establishment $establishment
     * @param bool          $health_center
     * @param bool          $without_cps
     *
     * @return Establishment
     */
    public function listUsersForEstablishment(
        Establishment $establishment,
        bool $health_center = false,
        bool $without_cps = false
    ): Establishment {
        $users = UserMapper::getUsersFromListResponse(
            $this->client->listUsersForEstablishment($establishment->getId(), $health_center, $without_cps)
        );

        return $establishment->setUsers($users);
    }

    public function linkUserToEstablishment(string $establishment_id, int $user_id): bool
    {
        $user = User::hydrate(['id' => $user_id]);
        $this->client->linkUserToEstablishment($establishment_id, [$user]);

        $user->linkDataModelToEstablishment($establishment_id);

        return true;
    }

    public function unlinkUserFromEstablishment(int $user_id): bool
    {
        $user = User::hydrate(['id' => $user_id]);

        $r = $this->client->unlinkUserFromEstablishment([$user]);

        $user->unlinkDataModelFromEstablishment();

        return true;
    }

    public function getEstablishmentConfiguration(Establishment $establishment): EstablishmentConfiguration
    {
        $response = $this->client->getEstablishmentConfiguration($establishment->getId());

        $configuration = EstablishmentConfigurationMapper::getEntityFromResponse($response);
        $establishment->setConfiguration($configuration);

        return $configuration;
    }

    public function storeEstablishmentConfiguration(
        string $establishment_id,
        EstablishmentConfiguration $configuration
    ): bool {
        $this->client->updateEstablishmentConfiguration($establishment_id, $configuration);

        return true;
    }

    /**
     * @param Establishment $establishment
     *
     * @return EmployeeCard[]
     */
    public function listEmployeeCards(Establishment $establishment): array
    {
        $response = $this->client->listEmployeeCards($establishment->getId());

        $cards = EmployeeCardMapper::getLisFromResponse($response);
        $establishment->setEmployeeCards($cards);

        return $cards;
    }

    public function storeEmployeeCard(EmployeeCard $card): bool
    {
        $this->client->storeEmployeeCard($card);

        return true;
    }

    public function deleteEmployeeCard(EmployeeCard $card): bool
    {
        $this->client->deleteEmployeeCard([$card]);

        return true;
    }

    /**
     * @return Establishment[]
     */
    protected function getEstablishmentsFromApi(): array
    {
        $establishments = EstablishmentMapper::getListFromResponse($this->client->listEstablishments());

        /* In cases where the data model has not been created, we create it */
        foreach ($establishments as $establishment) {
            $establishment->loadDataModel();

            if (!$establishment->getDataModel()->_id) {
                $establishment->createDataModel();
            }
        }

        return $establishments;
    }

    /**
     * @return Establishment[]
     */
    protected function getEstablishmentsFromCache(): array
    {
        $establishments = [];

        $data = $this->cache->get();
        if ($data) {
            foreach ($data as $datum) {
                $establishment = Establishment::hydrate($datum);
                $establishments[$establishment->getId()] = $establishment;
            }
        }

        return $establishments;
    }

    /**
     * @param Establishment[] $establishments
     */
    protected function putEstablishmentsInCache(array $establishments): void
    {
        $data = [];

        foreach ($establishments as $establishment) {
            $data[$establishment->getId()] = EstablishmentMapper::getArrayFromEntity($establishment);
        }

        $this->cache->put($data);
    }

    protected function deleteEstablishmentsCache(): void
    {
        if ($this->cache->exists()) {
            $this->cache->rem();
        }
    }
}
