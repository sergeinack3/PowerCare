<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Security\Csrf;

use Exception;
use Ox\Core\Security\Csrf\Exceptions\CouldNotGetCsrfToken;
use Ox\Core\Security\Csrf\Exceptions\CouldNotUseCsrf;
use Psr\SimpleCache\CacheInterface;

/**
 * Class AntiCsrfSharedMemoryRepository
 */
class AntiCsrfSharedMemoryRepository implements AntiCsrfRepositoryInterface
{
    private const POOL_KEY        = 'anti_csrf';
    private const POOL_SECRET_KEY = 'secret';
    private const POOL_TOKENS_KEY = 'tokens';

    /** @var CacheInterface */
    private $cache;

    /**
     * AntiCsrfSharedMemoryRepository constructor.
     *
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public function init(string $identifier, string $secret): void
    {
        try {
            if (!$this->cache->set($this->forgeSecretKey($identifier), $secret)) {
                throw CouldNotUseCsrf::notInitialized();
            }
        } catch (Exception $e) {
            throw CouldNotUseCsrf::notInitialized();
        }
    }

    /**
     * @param string $identifier
     *
     * @return mixed
     * @throws CouldNotUseCsrf
     */
    private function getOrFail(string $key)
    {
        $value = $this->cache->get($key);

        if ($value === null) {
            throw CouldNotUseCsrf::notInitialized();
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getSecret(string $identifier): string
    {
        return $this->getOrFail($this->forgeSecretKey($identifier));
    }

    /**
     * @inheritDoc
     */
    public function retrieveToken(string $identifier, string $candidate): AntiCsrfToken
    {
        try {
            $token = $this->getOrFail($this->forgeTokenKey($identifier, $candidate));
        } catch (CouldNotUseCsrf $e) {
            throw CouldNotGetCsrfToken::notFound();
        }

        if (!$token instanceof AntiCsrfToken) {
            throw CouldNotGetCsrfToken::notFound();
        }

        if ($token->hasExpired()) {
            $this->invalidateToken($identifier, $token);

            throw CouldNotGetCsrfToken::hasExpired();
        }

        return $token;
    }

    /**
     * @inheritDoc
     */
    public function persistToken(string $identifier, AntiCsrfToken $token, int $ttl): void
    {
        if (!$this->cache->set($this->forgeTokenKey($identifier, $token->getToken()), $token, $ttl)) {
            throw CouldNotUseCsrf::notInitialized();
        }
    }

    /**
     * @inheritDoc
     */
    public function invalidateToken(string $identifier, AntiCsrfToken $token): bool
    {
        return $this->cache->delete($this->forgeTokenKey($identifier, $token->getToken()));
    }

    /**
     * @param string $identifier
     *
     * @return string
     * @throws CouldNotUseCsrf
     */
    private function forgeKey(string $key): string
    {
        return self::POOL_KEY . '-' . $key;
    }

    /**
     * @param string $identifier
     *
     * @return string
     * @throws CouldNotUseCsrf
     */
    private function forgeSecretKey(string $identifier): string
    {
        return $this->forgeKey(self::POOL_SECRET_KEY . '-' . $identifier);
    }

    /**
     * @param string $identifier
     * @param string $name
     *
     * @return string
     * @throws CouldNotUseCsrf
     */
    private function forgeTokenKey(string $identifier, string $token): string
    {
        return $this->forgeKey(self::POOL_TOKENS_KEY . '-' . $identifier . '-' . $token);
    }
}
