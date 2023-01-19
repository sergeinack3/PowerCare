<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Security\Csrf;

use Exception;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\Security\Csrf\Exceptions\CouldNotGetCsrfToken;
use Ox\Core\Security\Csrf\Exceptions\CouldNotUseCsrf;
use Ox\Core\Security\Csrf\Exceptions\CouldNotValidateToken;
use Ox\Mediboard\Admin\CUser;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Description
 */
class AntiCsrf
{
    /** @var int Default token TTL */
    private const DEFAULT_TTL = 3600;

    /** @var int */
    private const SECRET_BYTES_LENGTH = 32;

    /** @var string Token form parameter name */
    private const TOKEN_PARAMETER = '@token';

    /** @var string[] Form parameters to ignore */
    private const PROTECTED_PARAMETERS = [
        self::TOKEN_PARAMETER,
        '@class',
        '@config',
        'del',
        'm',
        'a',
        'dosql',
        'tab',
        'dialog',
        'raw',
        'ajax',
        'info',
        'enslave',
    ];

    /** @var self */
    private static $instance;

    /** @var string */
    private $identifier;

    /** @var AntiCsrfRepositoryInterface */
    private $repository;

    /** @var string */
    private $secret;

    /**
     * AntiCsrf constructor.
     *
     * @param AntiCsrfRepositoryInterface|null $repository
     *
     * @throws Exception
     */
    private function __construct(AntiCsrfRepositoryInterface $repository, string $identifier)
    {
        $this->identifier = $identifier;
        $this->repository = $repository;

        try {
            $this->secret = $this->getCsrfSecret();
        } catch (CouldNotUseCsrf $e) {
            $this->initCsrf();
        }
    }

    /**
     * @param AntiCsrfRepositoryInterface|null $repository
     * @param string|null                      $identifier
     *
     * @throws Exception
     * @throws CouldNotUseCsrf
     */
    public static function init(?AntiCsrfRepositoryInterface $repository = null, ?string $identifier = null): void
    {
        if (!CAppUI::conf('anti_csrf_enable')) {
            return;
        }

        if (self::$instance instanceof self) {
            throw CouldNotUseCsrf::alreadyInitialized();
        }

        $repository = ($repository) ?: new AntiCsrfSharedMemoryRepository(Cache::getCache(Cache::DISTR));
        $identifier = ($identifier) ?: CUser::get()->_id;

        self::$instance = new self($repository, $identifier);
    }

    /**
     * @return static
     * @throws CouldNotUseCsrf
     */
    private static function get(): self
    {
        if (!self::$instance instanceof self) {
            throw CouldNotUseCsrf::notInitialized();
        }

        return self::$instance;
    }

    /**
     * Initialize the anti-csrf manager
     *
     * @throws Exception
     */
    private function initCsrf(): void
    {
        $this->secret = $this->generateSecret();

        $this->repository->init($this->identifier, $this->secret);
    }

    /**
     * @return string
     * @throws Exception
     */
    private function generateSecret(): string
    {
        return bin2hex(random_bytes(self::SECRET_BYTES_LENGTH));
    }

    /**
     * Get the shared secret
     *
     * @return string
     * @throws CouldNotUseCsrf
     */
    private function getCsrfSecret(): string
    {
        return $this->repository->getSecret($this->identifier);
    }

    /**
     * @return AntiCsrfTokenParameterBag
     * @throws CouldNotUseCsrf
     * @throws Exception
     */
    public static function prepare(): AntiCsrfTokenParameterBag
    {
        if (!CAppUI::conf('anti_csrf_enable')) {
            return new AntiCsrfTokenParameterBagNullObject();
        }

        return new AntiCsrfTokenParameterBag(self::get());
    }

    /**
     * @param array    $parameters
     * @param int|null $ttl
     *
     * @return string
     * @throws CouldNotUseCsrf
     */
    public function getTokenFor(array $parameters = [], ?int $ttl = null): string
    {
        return $this->generateToken($parameters, $ttl)->getToken();
    }

    /**
     * Generate a token and add it to the pool.
     *
     * @param array    $parameters
     * @param int|null $ttl
     *
     * @return AntiCsrfToken
     * @throws CouldNotUseCsrf
     */
    private function generateToken(array $parameters, ?int $ttl = null): AntiCsrfToken
    {
        // $ttl = 0, not allowed
        $ttl = ($ttl) ?: self::DEFAULT_TTL;

        $token = AntiCsrfToken::generate($this->secret, $parameters, $ttl);

        $this->addTokenToPool($token);

        return $token;
    }

    /**
     * Add a token to the pool.
     *
     * @param AntiCsrfToken $token
     *
     * @throws CouldNotUseCsrf
     */
    private function addTokenToPool(AntiCsrfToken $token): void
    {
        $this->repository->persistToken($this->identifier, $token, $token->getTTL());
    }

    /**
     * @param AntiCsrfToken $token
     *
     * @throws CouldNotUseCsrf
     */
    private function invalidateToken(AntiCsrfToken $token): void
    {
        $this->repository->invalidateToken($this->identifier, $token);
    }

    /**
     * Validate a token according to given parameters.
     *
     * @param array $parameters
     *
     * @return array
     * @throws Exception
     */
    public static function validateParameters(array $parameters): array
    {
        if (!CAppUI::conf('anti_csrf_enable')) {
            return $parameters;
        }

        try {
            $instance = self::get();

            return $instance->validate($parameters);
        } catch (CouldNotUseCsrf | CouldNotGetCsrfToken | CouldNotValidateToken $e) {
            CAppUI::accessDenied();
        }
    }

    /**
     * Validate a token according to $_POST parameters.
     *
     * @return array
     * @throws Exception
     */
    public static function validatePOST(): array
    {
        return self::validateParameters($_POST);
    }

    /**
     * Validate a token according to given ServerRequestInterface.
     *
     * @param ServerRequestInterface $request
     *
     * @return array
     * @throws CouldNotValidateToken
     */
    public static function validateRequest(ServerRequestInterface $request): array
    {
        if (!CAppUI::conf('anti_csrf_enable')) {
            $body = $request->getParsedBody();

            if (is_object($body)) {
                throw CouldNotValidateToken::unsupportedBody();
            }

            return ($body) ?? [];
        }

        try {
            $instance = self::get();

            $body = $request->getParsedBody();

            if (is_object($body)) {
                throw CouldNotValidateToken::unsupportedBody();
            }

            return $instance->validate($body);
        } catch (CouldNotUseCsrf | CouldNotGetCsrfToken | CouldNotValidateToken $e) {
            CAppUI::accessDenied();
        }
    }

    /**
     * @param array|null $parameters
     *
     * @return array The array of parameters hydrated with token enforced values.
     * @throws CouldNotGetCsrfToken
     * @throws CouldNotUseCsrf
     * @throws CouldNotValidateToken
     */
    private function validate(?array $parameters = []): array
    {
        $received_token = $this->extractToken($parameters);

        if ($received_token === null) {
            throw CouldNotValidateToken::tokenNotProvided();
        }

        // Filter protected keys
        $token_parameters = array_diff_key($parameters, array_fill_keys(self::PROTECTED_PARAMETERS, null));

        $token = $this->retrieveTokenFromPool($received_token);

        // External matching check
        if (!$token->match($received_token)) {
            //$this->invalidateToken($token);

            throw CouldNotValidateToken::tokenDoesNotMatch();
        }

        if (!$token->isValid($token_parameters)) {
            //$this->invalidateToken($token);

            throw CouldNotValidateToken::parametersAreNotValid();
        }

        return $this->getHydratedParameters($parameters, $token);
    }

    /**
     * @param array         $parameters
     * @param AntiCsrfToken $token
     *
     * @return array
     */
    private function getHydratedParameters(array $parameters, AntiCsrfToken $token): array
    {
        return array_merge($parameters, $token->getEnforcedParameters(true));
    }

    /**
     * @param string $received_token
     *
     * @return AntiCsrfToken
     * @throws CouldNotGetCsrfToken
     * @throws CouldNotUseCsrf
     */
    private function retrieveTokenFromPool(string $received_token): AntiCsrfToken
    {
        return $this->repository->retrieveToken($this->identifier, $received_token);
    }

    /**
     * Extract the token parameter form an array of parameters
     *
     * @param array|null $parameters
     *
     * @return string|null
     */
    private function extractToken(?array $parameters = []): ?string
    {
        // No parameters, not event a token so we quit
        if ($parameters === null) {
            return null;
        }

        return ($parameters[self::TOKEN_PARAMETER]) ?? null;
    }
}
