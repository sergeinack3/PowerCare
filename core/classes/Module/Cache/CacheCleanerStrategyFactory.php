<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Module\Cache;

use InvalidArgumentException;
use Ox\Core\CacheManager;
use Ox\Core\CAppUI;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Cache cleaner strategy factory returning a specific cleaning strategy depending on parameters.
 */
class CacheCleanerStrategyFactory
{
    private string  $cache;
    private string  $target;
    private int     $layer;
    private array   $hosts       = [];
    private ?string $cookie      = null;
    private ?string $actual_host = null;

    /**
     * @param string $cache  Cache key to clear
     * @param string $target Targetted address
     * @param int    $layer  SHM or DSHM layer to clear (see CacheManager)
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $cache, string $target, int $layer)
    {
        if ($cache === '') {
            throw new InvalidArgumentException(CAppUI::tr('system-msg-Cache key is missing'));
        }

        if (!$layer) {
            throw new InvalidArgumentException(CAppUI::tr('system-msg-Layer is missing'));
        }

        $this->cache  = $cache;
        $this->target = $target;
        $this->layer  = $layer;
    }

    /**
     * @return CacheCleanerStrategyInterface
     */
    public function create(): CacheCleanerStrategyInterface
    {
        // If layer ALL, it concerns DSHM / SHM caches
        if ($this->layer >= CacheManager::ALL) {
            return new AllCacheCleanerStrategy(
                $this->cache,
                HttpClient::create(),
                $this->hosts,
                $this->cookie,
                $this->actual_host
            );
        }

        // If layer DSHM, only one "local" clear is enough
        if ($this->layer & CacheManager::DSHM) {
            return new LocalCacheCleanerStrategy($this->cache, $this->layer, $this->actual_host);
        }

        // If target is local server or remote server is the local one, just perform a local cleanup and not a Remote
        if ($this->target === 'local' || ($this->actual_host && ($this->target === $this->actual_host))) {
            return new LocalCacheCleanerStrategy($this->cache, $this->layer, $this->actual_host);
        }

        return new RemoteCacheCleanerStrategy(
            $this->cache,
            $this->layer,
            HttpClient::create(),
            $this->target,
            $this->cookie
        );
    }

    public function withHosts(array $hosts): self
    {
        $this->hosts = $hosts;

        return $this;
    }

    public function withCookie(string $cookie): self
    {
        $this->cookie = $cookie;

        return $this;
    }

    public function withActualHost(string $actual_host): self
    {
        $this->actual_host = $actual_host;

        return $this;
    }
}
