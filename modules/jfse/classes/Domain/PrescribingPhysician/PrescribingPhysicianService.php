<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\PrescribingPhysician;

use DateTimeImmutable;
use Ox\Core\Cache;
use Ox\Mediboard\Jfse\ApiClients\PrescribingPhysicianClient;
use Ox\Mediboard\Jfse\Domain\AbstractService;
use Ox\Mediboard\Jfse\Mappers\PrescribingPhysicianMapper;

class PrescribingPhysicianService extends AbstractService
{
    /** @var PrescribingPhysicianClient */
    protected $client;

    /** @var string|null */
    private $user_guid;

    /** @var PrescribingPhysicianMapper */
    private $mapper;

    /** @var string The value of the field source_library of the messages that concerns the care path */
    protected const ERROR_SOURCE = 'MEDECIN PRESCRIPTEUR';

    public function __construct(?string $user_guid, PrescribingPhysicianClient $client = null)
    {
        parent::__construct($client ?? new PrescribingPhysicianClient());

        $this->user_guid = $user_guid;
        $this->mapper = new PrescribingPhysicianMapper();
    }

    /**
     * @return Physician[]
     */
    public function getPrescribingPhysiciansWithFilters(
        string $first_name,
        string $last_name,
        string $national_id,
        Cache $cache = null
    ): array {
        $physicians_list = $this->getPrescribingPhysicianList($cache);

        return array_filter(
            $physicians_list,
            function (Physician $physician) use ($first_name, $last_name, $national_id) {
                $first_name_pos = $this->getStringPosition($physician->getFirstName(), $first_name);
                $first_name_pos = ($first_name_pos !== null) ? (int)$first_name_pos : false;

                $last_name_pos = $this->getStringPosition($physician->getLastName(), $last_name);
                $last_name_pos = ($last_name_pos !== null) ? (int)$last_name_pos : false;

                $national_id_pos = ($national_id) ? strpos($physician->getNationalId(), $national_id) : false;

                return $first_name_pos !== false || $last_name_pos !== false || $national_id_pos !== false;
            }
        );
    }

    public function getPrescribingPhysicianList(Cache $cache = null): array
    {
        $cache = $cache ?? new Cache(
            'Jfse-PrescribingPhysician',
            'PrescribingPhysicianList-' . $this->user_guid,
            Cache::OUTER
        );

        $prescribing_array = $cache->get();

        if (!$prescribing_array) {
            $prescribing_array = $this->client->getPrescribingPhysicianList()->getContent();

            $cache->put($prescribing_array);
        }

        return $this->mapper->getPrescribingPhysiciansFromArray($prescribing_array);
    }

    /**
     * A method which finds the position of a string in an other string
     * Both strings aren't case sensitive
     * Will return null if the string isn't found in the haystack
     *
     * @param string $haystack
     * @param string $needle
     *
     * @return int|null
     */
    private function getStringPosition(string $haystack, string $needle): ?int
    {
        if (!$needle) {
            return null;
        }

        $position = strpos(strtolower($haystack), strtolower($needle));

        return ($position !== false) ? $position : null;
    }

    public function hasPrescribingPhysician(string $id): bool
    {
        foreach ($this->getPrescribingPhysicianList() as $_physician) {
            if ($_physician->getId() === $id) {
                return true;
            }
        }

        return false;
    }

    public function getPhysicianSpecialityByCode(string $code): PhysicianSpeciality
    {
        $speciality = array_filter(
            $this->getPhysicianSpecialitiesList(),
            function (PhysicianSpeciality $speciality) use ($code) {
                return $speciality->getCode() == $code;
            }
        );

        return reset($speciality);
    }

    public function getPhysicianSpecialitiesList(Cache $cache = null): array
    {
        $cache           = $cache ?? new Cache('Jfse-PrescribingPhysician', 'PhysicianSpecialities', Cache::OUTER);
        $speciality_list = $cache->get();

        if (!$speciality_list) {
            $speciality_list = $this->client->getPhysicianSpecialities()->getContent();

            $cache->put($speciality_list);
        }

        $specialities = [];
        foreach ($speciality_list['lstSpecialite'] as $_speciality) {
            $specialities[] = PhysicianSpeciality::hydrate(
                [
                    'code'   => $_speciality['code'],
                    'label'  => $_speciality['libelle'],
                    'family' => $_speciality['famille'],
                ]
            );
        }

        return $specialities;
    }

    public function storePhysician(Physician $physician): bool
    {
         $this->client->addOrUpdatePhysician($physician);

        $this->setPrescribingPhysicianListIntoCache();

        return true;
    }

    protected function setPrescribingPhysicianListIntoCache(Cache $cache = null): void
    {
        $cache    = $cache ?? new Cache(
            'Jfse-PrescribingPhysician',
            'PrescribingPhysicianList-' . $this->user_guid,
            Cache::OUTER
        );
        $response = $this->client->getPrescribingPhysicianList();

        $cache->put($response->getContent());
    }

    public function deletePhysician(int $physician_id): bool
    {
        $this->client->deletePhysician($physician_id);

        $this->setPrescribingPhysicianListIntoCache();

        return true;
    }

    public function setPrescribingPhysician(
        string $invoice_id,
        DateTimeImmutable $date,
        string $origin,
        Physician $physician
    ): bool {
        $this->client->setMessagesHandler(
            [$this, 'handleErrorAndWarningMessagesForSourcesLibraryOnly'],
            [self::ERROR_SOURCE]
        );

        $this->client->setPrescribingPhysician($invoice_id, $date, $origin, $physician);

        return true;
    }

    public function getPhysicianTypeByCode(int $code): ?PhysicianType
    {
        $type = array_filter(
            $this->getPhysicianTypesList(),
            function (PhysicianType $type) use ($code) {
                return $type->getCode() === $code;
            }
        );

        return (count($type) > 0) ? reset($type) : null;
    }

    public function getPhysicianTypesList(Cache $cache = null): array
    {
        $cache     = $cache ?? new Cache('Jfse-PrescribingPhysician', 'PhysicianTypes', Cache::OUTER);
        $type_list = $cache->get();

        // Get and add to cache
        if (!$type_list) {
            $type_list = $this->client->getPhysicianTypes()->getContent();

            $cache->put($type_list);
        }

        // Make list types
        $types = [];
        foreach ($type_list['lstType'] as $_type) {
            $types[] = PhysicianType::hydrate(['code' => $_type['code'], 'label' => $_type['libelle']]);
        }

        return $types;
    }
}
