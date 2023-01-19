<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Cps;

use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\ApiClients\AbstractApiClient;
use Ox\Mediboard\Jfse\ApiClients\CpsClient;
use Ox\Mediboard\Jfse\Domain\AbstractService;
use Ox\Mediboard\Jfse\Domain\UserManagement\UserManagementService;
use Ox\Mediboard\Jfse\Exceptions\Cps\CpsException;
use Ox\Mediboard\Jfse\Mappers\CpsMapper;

/**
 * Class CpsService
 *
 * @package Ox\Mediboard\Jfse\Domain\CPS
 */
final class CpsService extends AbstractService
{
    /** @var CpsClient The API Client */
    protected $client;


    /**
     * CpsService constructor.
     *
     * @param CpsClient|null $client
     */
    public function __construct(CpsClient $client = null)
    {
        parent::__construct($client ?? new CpsClient());

        $this->client->setErrorsHandler([$this, 'handleCpsErrors']);
    }

    /**
     * Read the CPS card, get its data
     *
     * @param ?int $code
     *
     * @return Card
     */
    public function read(int $code = null): Card
    {
        if ($code && !self::isCodeValid($code)) {
            throw CpsException::invalidCode();
        }

        return CpsMapper::getCardFromReadResponse($this->client->read($code));
    }

    /**
     * Load the users for each situation on the card
     *
     * @param Card $card
     */
    public function loadUsersFromCard(Card $card): void
    {
        $user_service = new UserManagementService();

        foreach ($card->getSituations() as $situation) {
            if ($situation->getPractitionerId()) {
                $user = $user_service->getUser($situation->getPractitionerId());

                if ($user) {
                    $situation->setUser($user);
                }
            }
        }
    }

    /**
     * Handles the CPS specifics errors
     *
     * @param Response $response
     *
     * @return void
     *
     * @throws CpsException
     */
    public function handleCpsErrors(Response $response): void
    {
        foreach (CpsClient::$cps_errors_codes as $error_code) {
            if ($response->hasError($error_code)) {
                $error = $response->getError($error_code);
                throw CpsException::readingError($error->getDescription(), $error->getDetails());
            }
        }

        $this->handleErrors($response);
    }

    /**
     * @param int $code
     *
     * @return bool
     */
    private static function isCodeValid(int $code): bool
    {
        return ($code > 0 && $code < 10000);
    }
}
