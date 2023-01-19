<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\UserManagement;

use Exception;
use Ox\Core\Cache;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Mediboard\Jfse\ApiClients\UserManagementClient;
use Ox\Mediboard\Jfse\DataModels\CJfseUser;
use Ox\Mediboard\Jfse\Domain\AbstractService;
use Ox\Mediboard\Jfse\Domain\Cps\Card;
use Ox\Mediboard\Jfse\Exceptions\Cps\CpsException;
use Ox\Mediboard\Jfse\Exceptions\UserManagement\UserException;
use Ox\Mediboard\Jfse\Mappers\TariffContractMapper;
use Ox\Mediboard\Jfse\Mappers\UserMapper;
use Ox\Mediboard\Mediusers\CMediusers;

final class UserManagementService extends AbstractService
{
    /** @var UserManagementClient The API Client */
    protected $client;

    /** @var Cache */
    private $tariff_contracts_cache;

    /**
     * UserManagementService constructor.
     *
     * @param UserManagementClient|null $client
     * @param Cache|null                $tariff_contracts_cache
     */
    public function __construct(UserManagementClient $client = null, Cache $tariff_contracts_cache = null)
    {
        parent::__construct($client ?? new UserManagementClient());
        $this->tariff_contracts_cache = $tariff_contracts_cache ?? new Cache(
            'Jfse-UserManagement',
            "tariff_contracts",
            Cache::OUTER,
            86400
        );
    }

    /**
     * @param string $last_name
     * @param string $first_name
     * @param string $invoicing_number
     * @param string $national_identifier
     *
     * @return User[]
     */
    public function listUsers(
        ?string $last_name,
        ?string $first_name,
        ?string $invoicing_number,
        ?string $national_identifier
    ): array {
        return UserMapper::getUsersFromListResponse($this->client->listUsers(
            $last_name,
            $first_name,
            $invoicing_number,
            $national_identifier
        ));
    }

    /**
     * @param int         $id
     * @param string|null $login
     * @param string|null $password
     *
     * @return User
     */
    public function getUser(int $id, string $login = null, string $password = null): User
    {
        $user = UserMapper::getUserFromGetInformationResponse($this->client->getUser($id, $login, $password));
        $user->loadDataModel();

        return $user;
    }

    /**
     * Creates a new User by reading the CPS and sending its data to Jfse
     * In case of multiples situations in the card, one must be selected
     *
     * @param Card $cps
     * @param int  $mediuser_id A CMediuser's id to link
     *
     * @throws Exception
     * @throws CpsException
     *
     * @return User
     */
    public function createUserFromCps(Card $cps, int $mediuser_id = null): User
    {
        if ($cps->countSituations() !== 1) {
            throw CpsException::noSituationSelected();
        }

        if (!$cps->isSpecialityAuthorized()) {
            $situations = $cps->getSituations();
            $situation = reset($situations);
            throw CpsException::unauthorizedSpeciality(
                $situation->getSpecialityCode(),
                $situation->getSpecialityLabel()
            );
        }

        $user = UserMapper::getUserFromGetInformationResponse($this->client->updateUserFromCps($cps));
        if (!$user->getId()) {
            $user->setIdFromSituation();
        }

        $user->createDataModel();

        if ($mediuser_id) {
            $user->linkDataModelToMediuser($mediuser_id);
        }

        return $user;
    }

    /**
     * Updates a User by reading the CPS and sending its data to Jfse
     *
     * @param Card $cps
     *
     * @throws CpsException
     *
     * @return User
     */
    public function updateUserFromCps(Card $cps): User
    {
        if ($cps->countSituations() !== 1) {
            throw CpsException::noSituationSelected();
        }

        return UserMapper::getUserFromGetInformationResponse($this->client->updateUserFromCps($cps));
    }

    /**
     * @param int $user_id
     * @param int $mediuser_id
     *
     * @return bool
     * @throws Exception
     */
    public function linkUserToMediuser(int $user_id, int $mediuser_id): bool
    {
        $user = User::hydrate(['id' => $user_id]);

        return $user->linkDataModelToMediuser($mediuser_id);
    }

    /**
     * @param int $user_id
     *
     * @return bool
     * @throws Exception
     */
    public function unlinkUserToMediuser(int $user_id): bool
    {
        $user = User::hydrate(['id' => $user_id]);

        return $user->unlinkDataModelFromMediuser();
    }

    /**
     * Deletes the user with the given id
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteUser(int $id): bool
    {
        $this->client->deleteUser($id);
        $user = User::hydrate(['id' => $id]);

        return $user->deleteDataModel();
    }

    /**
     * @return UserConfiguration
     */
    public function getUserParameters(): UserConfiguration
    {
        return UserMapper::getUserConfigFromGetParametersResponse($this->client->getUserParameters());
    }

    /**
     * Updates or creates a parameter with the given code and value
     *
     * @param int   $parameter_id
     * @param mixed $value
     *
     * @return bool
     */
    public function updateUserParameter(int $parameter_id, $value): bool
    {
        $this->client->updateUserParameter($parameter_id, $value);

        return true;
    }

    /**
     * Deletes a user parameter with the given code
     *
     * @param int $parameter_id
     *
     * @return bool
     */
    public function deleteUserParameter(int $parameter_id): bool
    {
        $this->client->deleteUserParameter($parameter_id);

        return true;
    }

    /**
     * @return TariffContract[]
     */
    public function getListTariffContracts(): array
    {
        $data = $this->tariff_contracts_cache->get();

        if (!$data) {
            $data = TariffContractMapper::getArrayFromResponse($this->client->getListTariffContracts());
            $this->tariff_contracts_cache->put($data);
        }

        $contracts = [];
        foreach ($data as $contract) {
            $contracts[] = TariffContract::hydrate($contract);
        }

        return $contracts;
    }

    /**
     * Get the content of the signature file of the user with the given id
     *
     * @param int $user_id
     *
     * @throws UserException
     *
     * @return string
     */
    public function getUserSignature(int $user_id): string
    {
        $response = $this->client->getUserSignature($user_id);
        $signature = CMbArray::get($response->getContent(), 'signature', '');

        if (!empty($signature)) {
            $signature = base64_decode($signature);
        } else {
            throw UserException::signatureNotFound();
        }

        return $signature;
    }

    /**
     * Sets or updates the signature (used for signing the PDFs version of the invoices) of the user with the given id
     *
     * @param int    $user_id
     * @param string $signature The content of the signature image
     *
     * @return bool
     */
    public function updateUserSignature(int $user_id, string $signature): bool
    {
        $this->client->updateUserSignature($user_id, base64_encode($signature));

        return true;
    }

    /**
     * Deletes the signature of the user with the given id
     *
     * @param int $user_id
     *
     * @return bool
     */
    public function deleteUserSignature(int $user_id): bool
    {
        $this->client->deleteUserSignature($user_id);

        return true;
    }

    /**
     * @return CJfseUser[]
     * @throws Exception
     */
    public function searchJfseUsersFromName(string $name): array
    {
        return (new CJfseUser())->getAutocompleteJfseUsers($name);
    }

    /**
     * Convert the domain filter name into the Jfse API filter name for the listing of users
     *
     * @param string $filter
     *
     * @return string|null
     */
    private static function getListUserClientFilter(string $filter): ?string
    {
        switch ($filter) {
            case 'last_name':
                $client_filter = 'nom';
                break;
            case 'first_name':
                $client_filter = 'prenom';
                break;
            case 'facturation_number':
                $client_filter = 'noFacturation';
                break;
            case 'national_identifier':
                $client_filter = 'noNationnal';
                break;
            default:
                $client_filter = null;
        }

        return $client_filter;
    }

    public static function userHasAccount(CMediusers $user): bool
    {
        return CJfseUser::isUserLinked($user);
    }

    /**
     * Checks if the given user can select another user account to view it's dashboard
     *
     * @param CMediusers $user
     *
     * @return bool
     */
    public static function canUserSelectOtherAccount(CMediusers $user = null): bool
    {
        if (is_null($user)) {
            $user = CMediusers::get();
        }

        return (!$user->isProfessionnelDeSante() && !self::userHasAccount($user)) || $user->isAdmin();
    }

    /**
     * Returns the list of CMediusers on which the connected user has EDIT rights, and that are linked to a CJfseUser
     *
     * @return CJfseUser[]
     * @throws Exception
     */
    public static function getJfseUsersListByPerm(int $perm_type = PERM_EDIT): array
    {
        $users = [];
        $jfse_users = (new CJfseUser())->loadList();
        $mediusers = CMbObject::massLoadFwdRef($jfse_users, 'mediuser_id');
        CMediusers::filterByPerm($mediusers, $perm_type);

        $users = [];
        foreach ($mediusers as $mediuser) {
            /** @var CJfseUser $jfse_user */
            $jfse_user = $mediuser->loadUniqueBackRef('jfse_user');
            if ($jfse_user && $jfse_user->_id) {
                $jfse_user->_mediuser   = $mediuser;
                $users[$jfse_user->_id] = $jfse_user;
            }
        }

        return $users;
    }
}
