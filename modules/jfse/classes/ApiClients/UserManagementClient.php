<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\Cps\Card;
use Ox\Mediboard\Jfse\Mappers\CpsMapper;

/**
 * The API client for the UsersManagement service
 *
 * @package Ox\Mediboard\Jfse\ApiClients
 */
final class UserManagementClient extends AbstractApiClient
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

    /**
     * Call the IDE-GetListePs methods, that return a (filtered or not) list of users
     *
     * @param string $last_name
     * @param string $first_name
     * @param string $invoicing_number
     * @param string $national_identifier
     *
     * @return Response
     */
    public function listUsers(
        ?string $last_name,
        ?string $first_name,
        ?string $invoicing_number,
        ?string $national_identifier
    ): Response {
        $data = [];

        $filters = [];
        if ($last_name) {
            $filters['nom'] = $last_name;
        }
        if ($first_name) {
            $filters['prenom'] = $first_name;
        }
        if ($invoicing_number) {
            $filters['noFacturation'] = $invoicing_number;
        }
        if ($national_identifier) {
            $filters['noNationnal'] = $national_identifier;
        }

        if (count($filters)) {
            $data['getListePs'] = $filters;
        }

        return self::sendRequest(Request::forge(self::getMethod('getListePs'), $data));
    }

    /**
     * Return the data of the user with the given id
     *
     * @param int         $id
     * @param string|null $login
     * @param string|null $password
     *
     * @return Response
     */
    public function getUser(int $id, string $login = null, string $password = null): Response
    {
        $data = ['idJfse' => $id];

        if ($login) {
            $data['login'] = $login;
        }

        if ($password) {
            $data['mdp'] = $password;
        }

        return self::sendRequest(Request::forge(self::getMethod('getInfoPS'), $data));
    }

    /**
     * Creates or updates a user from the Cps card
     *
     * @param Card     $cps The CPS
     *
     * @return Response
     */
    public function updateUserFromCps(Card $cps): Response
    {
        $data = [
            'updateUtilisateurViaCPS' => CpsMapper::getArrayFromCard($cps)
        ];

        return self::sendRequest(Request::forge(self::getMethod('updateUtilisateurViaCPS'), $data));
    }

    /**
     * Deletes the user with the given id
     *
     * @param int $id
     *
     * @return Response
     */
    public function deleteUser(int $id): Response
    {
        return self::sendRequest(
            Request::forge(
                self::getMethod('deleteUtilisateur'),
                [
                    'deleteUtilisateur' => [
                        'idUtilisateur' => $id,
                    ],
                ]
            )
        );
    }

    /**
     * Returns the list of the parameters of the user
     *
     * Note : Id of the user is the one send in the request
     *
     * @return Response
     */
    public function getUserParameters(): Response
    {
        return self::sendRequest(Request::forge(self::getMethod('getParamsPs'), ['getParamsPs' => []]));
    }

    /**
     * Updates (or creates) a parameter with the given code
     *
     * Note : Id of the user is the one send in the request
     *
     * @param int    $code  The code of the parameter
     * @param string $value The value of the parameter
     *
     * @return Response
     */
    public function updateUserParameter(int $code, string $value): Response
    {
        return self::sendRequest(
            Request::forge(
                self::getMethod('updateParamsPs'),
                [
                    'updateParamsPs' => [
                        'code'   => $code,
                        'valeur' => $value,
                    ],
                ]
            )
        );
    }

    /**
     * Deletes a parameter with the given code
     *
     * Note : Id of the user is the one send in the request
     *
     * @param int $code The code of the parameter
     *
     * @return Response
     */
    public function deleteUserParameter(int $code): Response
    {
        return self::sendRequest(
            Request::forge(
                self::getMethod('deleteParamsPs'),
                [
                    'deleteParamsPs' => [
                        'code' => $code,
                    ],
                ]
            )
        );
    }

    /**
     * Returns the list of the tariffs contracts
     *
     * @return Response
     */
    public function getListTariffContracts(): Response
    {
        return self::sendRequest(Request::forge(self::getMethod('getListeContratsTarifaires'), []));
    }

    /**
     * Returns the signature of the user with the given id
     *
     * @param int $user_id
     *
     * @return Response
     */
    public function getUserSignature(int $user_id): Response
    {
        return self::sendRequest(
            Request::forge(
                self::getMethod('getSignature'),
                [
                    'getSignature' => [
                        'idJfse' => $user_id,
                    ],
                ]
            )
        );
    }

    /**
     * Updates or creates the signature off given user
     *
     * @param int    $user_id   The id of the user
     * @param string $signature The signature image, encoded in base 64
     *
     * @return Response
     */
    public function updateUserSignature(int $user_id, string $signature): Response
    {
        return self::sendRequest(
            Request::forge(
                self::getMethod('updateSignature'),
                [
                    'updateSignature' => [
                        'idJfse'    => $user_id,
                        'signature' => $signature,
                    ],
                ]
            )
        );
    }

    /**
     * Deletes the signature of the user with the given id
     *
     * @param int $user_id
     *
     * @return Response
     */
    public function deleteUserSignature(int $user_id): Response
    {
        return self::sendRequest(
            Request::forge(
                self::getMethod('deleteSignature'),
                [
                    'deleteSignature' => [
                        'idJfse' => $user_id,
                    ],
                ]
            )
        );
    }
}
