<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Module\Cache;

use Exception;
use InvalidArgumentException;
use Ox\Components\Cache\Exceptions\CouldNotGetCache;
use Ox\Core\CacheManager;
use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\System\ConfigurationException;
use ReflectionException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Clear all servers SHM and only once DSHM cache.
 */
class AllCacheCleanerStrategy implements CacheCleanerStrategyInterface
{
    private string              $cache;
    private HttpClientInterface $client;
    private array               $hosts;
    private string              $cookie;
    private ?string             $actual_host = null;

    /** @var CacheCleanerStrategyInterface[] */
    private array $strategies = [];

    /**
     * @param string              $cache       Cache key to clear
     * @param HttpClientInterface $client      HttpClient for request execution
     * @param array               $hosts       All remote server addresses
     * @param string              $cookie      Cookie for authentication
     * @param null|string         $actual_host Current server address
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        string $cache,
        HttpClientInterface $client,
        array $hosts,
        string $cookie,
        string $actual_host = null
    ) {
        if (count($hosts) < 1) {
            throw new InvalidArgumentException(CAppUI::tr('system-msg-You need to pass at least 1 host'));
        }

        if ($cookie === '') {
            throw new InvalidArgumentException(CAppUI::tr('system-msg-Cookie is missing'));
        }

        $this->cache       = $cache;
        $this->client      = $client;
        $this->hosts       = $hosts;
        $this->cookie      = $cookie;
        $this->actual_host = $actual_host;
    }

    /**
     * @return void
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws CouldNotGetCache
     * @throws CMbException
     * @throws ConfigurationException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws ReflectionException
     * @throws Exception
     */
    public function execute(): void
    {
        $this->strategies = [];

        // clear DSHM cache
        $this->strategies[] = $local_dshm = new LocalCacheCleanerStrategy(
            $this->cache,
            CacheManager::DSHM_SPECIAL,
            $this->actual_host
        );

        $local_dshm->execute();

        // clear local current server SHM cache
        $this->strategies[] = $local_shm = new LocalCacheCleanerStrategy(
            $this->cache,
            CacheManager::SHM_SPECIAL,
            $this->actual_host
        );

        $local_shm->execute();

        if ($this->hosts[0] !== "") {
            // clear all others servers SHM cache
            foreach ($this->hosts as $host) {
                // If host is current server, prevent clearing twice current server (local and remote)
                if ($this->actual_host && ($this->actual_host == $host)) {
                    continue;
                }

                $this->strategies[] = $remote_shm = new RemoteCacheCleanerStrategy(
                    $this->cache,
                    CacheManager::SHM_SPECIAL,
                    $this->client,
                    $host,
                    $this->cookie
                );

                $remote_shm->execute();
            }
        }
    }

    public function getHtmlResult(CSmartyDP $smarty = null): string
    {
        $outputs = [];
        foreach ($this->strategies as $strategy) {
            $outputs[] = $strategy->getHtmlResult($smarty);
        }

        return implode('', $outputs);
    }
}
