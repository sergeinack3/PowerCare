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
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\System\ConfigurationException;
use ReflectionException;

/**
 * Clear local (current server) SHM or DSHM cache.
 */
class LocalCacheCleanerStrategy implements CacheCleanerStrategyInterface
{
    private string  $cache;
    private int     $layer;
    private ?string $actual_host = null;

    /**
     * @param string      $cache       Cache key to clear
     * @param int         $layer       SHM or DSHM layer to clear (see CacheManager)
     * @param string|null $actual_host Current server address
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $cache, int $layer, string $actual_host = null)
    {
        if ($actual_host === '' || $actual_host === null) {
            throw new InvalidArgumentException(CAppUI::tr('system-msg-Actual host is missing'));
        }

        $this->cache       = $cache;
        $this->layer       = $layer;
        $this->actual_host = $actual_host;
    }

    /**
     * @return void
     * @throws CMbException
     * @throws ConfigurationException
     * @throws CouldNotGetCache
     * @throws ReflectionException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws Exception
     */
    public function execute(): void
    {
        $this->log();

        CacheManager::clearCache($this->cache, $this->layer);
    }

    public function getHtmlResult(CSmartyDP $smarty = null): string
    {
        if ($smarty === null) {
            throw new InvalidArgumentException();
        }

        $smarty->assign(
            [
                'outputs'     => CacheManager::getOutputs(),
                'actual_host' => $this->actual_host,
            ]
        );

        return $smarty->fetch('view_cache_header');
    }

    /**
     * @throws Exception
     */
    private function log(): void
    {
        CApp::log(
            sprintf(
                'Clearing [%s] cache [%s] for [%s]',
                CacheManager::formatLayer($this->layer),
                $this->cache,
                $this->actual_host
            )
        );
    }
}
