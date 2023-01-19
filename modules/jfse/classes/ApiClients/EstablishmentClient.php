<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\UserManagement\EmployeeCard;
use Ox\Mediboard\Jfse\Domain\UserManagement\Establishment;
use Ox\Mediboard\Jfse\Domain\UserManagement\EstablishmentConfiguration;
use Ox\Mediboard\Jfse\Domain\UserManagement\User;
use Ox\Mediboard\Jfse\Mappers\EmployeeCardMapper;
use Ox\Mediboard\Jfse\Mappers\EstablishmentConfigurationMapper;
use Ox\Mediboard\Jfse\Mappers\EstablishmentMapper;

final class EstablishmentClient extends AbstractApiClient
{
    /** @var string The prefix for the user management service, used in all the method names */
    private const SERVICE_NAME = 'IDE';

    /**
     * Return the full name of the method
     *
     * @param string $method
     *
     * @return string
     */
    private static function getMethod(string $method): string
    {
        return self::SERVICE_NAME . '-' . $method;
    }

    public function listEstablishments(): Response
    {
        return self::sendRequest(Request::forge(self::getMethod('getListeEtablissements')));
    }

    public function updateEstablishment(Establishment $establishment): Response
    {
        $data = [
            'updateEtablissement' => EstablishmentMapper::getApiDataFromEntity($establishment),
        ];

        return self::sendRequest(Request::forge(self::getMethod('updateEtablissement'), $data));
    }

    public function deleteEstablishment(int $establishment_id, int $delete_users = 0): Response
    {
        return self::sendRequest(Request::forge(self::getMethod('deleteEtablissement'), [
            'deleteEtablissement' => [
                'id'          => $establishment_id,
                'deleteUsers' => $delete_users,
            ],
        ]));
    }

    public function listUsersForEstablishment(int $establishment_id, bool $health_center, bool $without_cps): Response
    {
        $health_center = $health_center ? 1 : 0;
        $without_cps   = $without_cps ? 1 : 0;

        return self::sendRequest(Request::forge(self::getMethod('getListePSParEtablissement'), [
            'getListePSParEtablissement' => [
                'idEtablissement' => $establishment_id,
                'centreSante'     => $health_center,
                'sansCPS'         => $without_cps
            ]
        ]));
    }

    /**
     * @param int    $establishment_id
     * @param User[] $users
     *
     * @return Response
     */
    public function linkUserToEstablishment(int $establishment_id, array $users): Response
    {
        $users_id = [];
        foreach ($users as $user) {
            $users_id[] = ['idJfse' => $user->getId()];
        }

        return self::sendRequest(Request::forge(self::getMethod('addPSEtablissement'), [
            'addPSEtablissement' => [
                'idEtablissement' => $establishment_id,
                'lstIdJfse' => $users_id
            ]
        ])->setForceObject(false));
    }

    /**
     * @param User[] $users
     *
     * @return Response
     */
    public function unlinkUserFromEstablishment(array $users): Response
    {
        $users_id = [];
        foreach ($users as $user) {
            $users_id[] = ['idJfse' => $user->getId()];
        }

        return self::sendRequest(Request::forge(self::getMethod('deletePSEtablissement'), [
            'deletePSEtablissement' => [
                'lstIdJfse' => $users_id
            ]
        ])->setForceObject(false));
    }

    public function getEstablishmentConfiguration(int $establishment_id): Response
    {
        return self::sendRequest(Request::forge(self::getMethod('getParametrageEtablissement'), [
            'getParametrageEtablissement' => [
                'idEtablissement' => $establishment_id
            ]
        ]));
    }

    public function updateEstablishmentConfiguration(
        int $establishment_id,
        EstablishmentConfiguration $parameters
    ): Response {
        return self::sendRequest(Request::forge(self::getMethod('updateParametrageEtablissement'), [
            'updateParametrageEtablissement' => EstablishmentConfigurationMapper::getApiDataFromEntity(
                $establishment_id,
                $parameters
            )
        ]));
    }

    public function listEmployeeCards(int $establishment_id): Response
    {
        return self::sendRequest(Request::forge(self::getMethod('getListeCPEEtablissement'), [
            'getListeCPEEtablissement' => [
                'idEtablissement' => $establishment_id
            ]
        ]));
    }

    public function storeEmployeeCard(EmployeeCard $card): Response
    {
        return self::sendRequest(Request::forge(self::getMethod('addCPEEtablissement'), [
            'addCPEEtablissement' => EmployeeCardMapper::getApiDataFromEntity($card)
        ]));
    }

    /**
     * @param EmployeeCard[] $cards
     *
     * @return Response
     */
    public function deleteEmployeeCard(array $cards): Response
    {
        $cards_ids = [];
        foreach ($cards as $card) {
            $cards_ids[] = ['idCpe' => $card->getId()];
        }

        return self::sendRequest(Request::forge(self::getMethod('deleteCPEEtablissement'), [
            'deleteCPEEtablissement' => [
                'lstIdCpe' => $cards_ids
            ]
        ])->setForceObject(false));
    }
}
