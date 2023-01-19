<?php

/**
 * @author  SAS OpenXtrem <methodesetoutils@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Module;

use Ox\Components\Cache\Exceptions\CouldNotGetCache;
use Ox\Core\Cache;
use Ox\Core\CacheManager;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Psr\SimpleCache\InvalidArgumentException as PsrSimpleCacheInvalidArgumentException;
use ReflectionException;

/**
 * Main abstract periodical task
 */
abstract class AbstractModuleCache implements IModuleCache
{
    protected array $shm_patterns = [];

    protected array $dshm_patterns = [];

    abstract protected function getModuleName(): string;

    /**
     * @param int  $layer
     * @param bool $special_actions
     *
     * @return void
     * @throws CouldNotGetCache
     * @throws PsrSimpleCacheInvalidArgumentException
     * @throws ReflectionException
     */
    public function clear(int $layer): void
    {
        if ($this->hasSHMPatterns() && ($layer & CacheManager::SHM)) {
            foreach ($this->shm_patterns as $shm_pattern) {
                Cache::deleteKeys(Cache::OUTER, trim($shm_pattern));

                CacheManager::output(
                    "module-system-msg-cache-keys-removal %s %s",
                    CAppUI::UI_MSG_OK,
                    "Outer",
                    $shm_pattern,
                );
            }
        }

        if ($this->hasDSHMPatterns() && ($layer & CacheManager::DSHM)) {
            foreach ($this->dshm_patterns as $dshm_pattern) {
                // Namespaced classes have double \ in redis
                $dshm_pattern = str_replace('\\', '\\\\', $dshm_pattern);
                Cache::deleteKeys(Cache::DISTR, trim($dshm_pattern));

                CacheManager::output(
                    "module-system-msg-cache-keys-removal %s %s",
                    CAppUI::UI_MSG_OK,
                    "Distributed",
                    $dshm_pattern,
                );
            }
        }

        // If module has special actions and module is active
        if (
            $this->hasSpecialActions() && ($layer & CacheManager::SPECIAL)
            && CModule::getActive($this->getModuleName())
        ) {
            $this->clearSpecialActions();
        }
    }

    /**
     * @return void
     */
    public function clearSpecialActions(): void
    {
        /* Override in subclass if necessary */
        CacheManager::output(
            "module-system-msg-cache-special-actions-removal %s",
            CAppUI::UI_MSG_OK,
            $this->getModuleName()
        );
    }

    /**
     * @throws ReflectionException
     */
    public function hasSpecialActions(): bool
    {
        return CApp::isMethodOverridden(static::class, 'clearSpecialActions');
    }

    public function getSHMPatterns(): ?array
    {
        return $this->shm_patterns;
    }

    public function hasSHMPatterns(): bool
    {
        return !empty($this->shm_patterns);
    }

    public function getDSHMPatterns(): ?array
    {
        return $this->dshm_patterns;
    }

    public function hasDSHMPatterns(): bool
    {
        return !empty($this->dshm_patterns);
    }
}
