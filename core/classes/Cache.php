<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Exception;
use Ox\Components\Cache\Adapters\APCuAdapter;
use Ox\Components\Cache\Adapters\ArrayAdapter;
use Ox\Components\Cache\Adapters\FileAdapter;
use Ox\Components\Cache\Decorators\KeySanitizerDecorator;
use Ox\Components\Cache\Adapters\YampeeRedisAdapter;
use Ox\Components\Cache\Exceptions\CouldNotGetCache;
use Ox\Components\Cache\LayeredCache;
use Ox\Core\Composer\CComposerScript;
use Ox\Mediboard\System\CRedisServer;
use Throwable;

/**
 * Multi-layers cache utility class
 * Using inner, outer or distributed layer or any combination of those
 */
class Cache
{
    private const RESERVED_CHARACTERS = '[{}()/\\@:]';
    private const CHAR_REPLACEMENT = '__________';

    /**
     * No cache layer used
     * Useful for testing purposes
     */
    public const NONE = 0;

    /**
     * INNER layer will use PHP static storage
     * Cache is available at an HTTP request level
     * Be aware: Values are manipulated by *reference* and subject to contextualisation issues
     */
    public const INNER = 1;

    /**
     * OUTER strategy will use the shared memory active engine, like APC or FileSystem
     * Cache is available at an HTTP server level
     * Values are manipulated by copy (serialization)
     */
    public const OUTER = 2;

    /**
     * DISTR stragery will user the distributed active shared memory, like Redis or any distributed key-value facility
     * Cache is available at a web servers farm level.
     * Be aware: so far no mechanism would allow the DISTR layer to prune other servers OUTER
     * So use OUTER and DISTR together very cautiously
     * Values are manipulated by copy (serialization)
     */
    public const DISTR = 4;

    /**
     * The standard and default INNER and OUTER layer combination
     * Very performant, should be used in most cases
     **/
    public const INNER_OUTER = 3;

    /**
     * The standard and default INNER and DISTR layer combination
     * Very performant, should be used in cases where you manage clearing
     **/
    public const INNER_DISTR = 5;

    private static bool $is_initialized = false;

    /** @var LayeredCache */
    private $cache;

    /** @var string */
    public $prefix;

    /** @var string */
    public $key;

    /** @var int */
    public $layers;

    /** @var mixed */
    public $value;

    /** @var int|null */
    private $ttl;

    /**
     * Cache constructor.
     *
     * @param string|null          $prefix Prefix to the key, for categorizing
     * @param string|string[]|null $key    The key of the value to access
     * @param int                  $layers Any combination of cache layers
     * @param int|null             $ttl    TTL for the key, only for OUTER and DISTR layers
     *
     * @throws CouldNotGetCache
     * @deprecated
     *
     */
    public function __construct($prefix, $key, $layers, $ttl = null)
    {
        // no cache when running composer script
        if (CComposerScript::$is_running) {
            // expected when clearing cache with cache manager
            $trace = debug_backtrace();
            if (!isset($trace[1]) || $trace[1]['class'] !== CacheManager::class) {
                $layers = static::NONE;
            }
        }

        // Todo: Care of non-serializable parameters!!
        $this->key    = is_array($key) ? implode('-', $key) : "{$key}";
        $this->prefix = $prefix;
        $this->layers = $layers;
        $this->ttl    = $ttl;

        $this->cache = self::getCache($layers);
    }

    /**
     * @param int $layers
     *
     * @return LayeredCache
     * @throws CouldNotGetCache
     */
    public static function getCache(int $layers): LayeredCache
    {
        return LayeredCache::getCache($layers);
    }

    /**
     * @return string
     */
    private function forgeKey(): string
    {
        return "{$this->prefix}-{$this->key}";
    }

    /**
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @deprecated
     */
    public function exists(): bool
    {
        return $this->cache->has($this->forgeKey());
    }

    /**
     * @return mixed|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @deprecated
     */
    public function get()
    {
        return $this->cache->get($this->forgeKey(), null, $this->ttl);
    }

    /**
     * @param mixed $value    The value to set
     * @param bool  $compress Compress data for copy strategy layers
     *
     * @return mixed The value, for return chaining
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @deprecated
     */
    public function put($value, bool $compress = false)
    {
        if ($compress) {
            $this->cache = self::getCache($this->layers)->withCompressor();
        }

        $this->cache->set($this->forgeKey(), $value, $this->ttl);

        return $value;
    }

    /**
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @deprecated
     */
    public function rem(): bool
    {
        return $this->cache->delete($this->forgeKey());
    }

    /**
     * Empty the INNER static cache.
     *
     * @throws CouldNotGetCache
     */
    public static function flushInner(): void
    {
        $cache = LayeredCache::getCache(LayeredCache::INNER);
        $cache->clear();
    }

    public static function getTotals(): array
    {
        return LayeredCache::getTotals();
    }

    public static function getTotal(): int
    {
        return LayeredCache::getTotal();
    }

    public static function getHits(): array
    {
        return LayeredCache::getHits();
    }

    public static function getAllLayers(): array
    {
        return LayeredCache::LAYERS;
    }

    /**
     * Delete keys according to given PREFIX.
     *
     * @param int    $layer
     * @param string $prefix
     *
     * @return bool
     * @throws CouldNotGetCache
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @deprecated
     */
    public static function deleteKeys(int $layer, string $prefix): bool
    {
        $cache = self::getCache($layer);

        return $cache->deleteMultiple($cache->list($prefix));
    }

    /**
     * @param string|array $key
     *
     * @return string
     */
    public static function sanitize($key): string
    {
        if (is_array($key)) {
            $key = implode('-', $key);
        } else {
            $key = "{$key}";
        }

        return strtr($key, self::RESERVED_CHARACTERS, self::CHAR_REPLACEMENT);
    }

    public static function init(string $namespace, bool $logging = false): void
    {
        global $dPconfig;

        $outer    = ($dPconfig['shared_memory']) ?? 'disk';
        $root_dir = ($dPconfig['root_dir']) ?? dirname(__DIR__, 2);

        $inner_adapter = [
            'adapter' => new ArrayAdapter(),
            'options' => ['namespaced' => false, 'engine' => 'PHP Array', 'engine_version' => 'N/A'],
        ];

        switch ($outer) {
            case 'apc':
            case 'apcu':
                $outer_adapter = [
                    'adapter' => new APCuAdapter(),
                    'options' => ['namespaced' => false, 'engine' => 'APCu', 'engine_version' => phpversion('apcu')],
                ];
                break;

            case 'disk':
            default:
                $outer_adapter = [
                    'adapter' => new FileAdapter(rtrim($root_dir, '/') . '/tmp/shared'),
                    'options' => ['namespaced' => false, 'engine' => 'Disk', 'engine_version' => 'N/A'],
                ];
        }

        // By default, OUTER and DISTR are the same
        LayeredCache::init($namespace)
            ->setAdapter(
                LayeredCache::INNER,
                (new KeySanitizerDecorator($inner_adapter['adapter']))->enableLogging($logging),
                $inner_adapter['options']
            )->setAdapter(
                LayeredCache::OUTER,
                (new KeySanitizerDecorator($outer_adapter['adapter']))->enableLogging($logging),
                $outer_adapter['options']
            )
            ->setAdapter(
                LayeredCache::DISTR,
                (new KeySanitizerDecorator($outer_adapter['adapter']))->enableLogging($logging),
                $outer_adapter['options']
            );

        static::$is_initialized = true;
    }

    public static function initDistributed(bool $logging = false): void
    {
        if (!LayeredCache::isInitialized()) {
            throw new Exception('Cache is not initialized');
        }

        $adapter  = new APCuAdapter();
        $metadata = ['engine' => 'APCu', 'engine_version' => phpversion('apcu')];

        if (CAppUI::conf('shared_memory_distributed') === 'redis') {
            try {
                $client = CRedisServer::getClient();

                if ($client === null) {
                    throw new Exception('No available client');
                }

                $adapter = new YampeeRedisAdapter($client);
                $adapter->setScanCount(1000);

                $info     = $client->parseMultiLine($client->getStats());
                $metadata = ['engine' => 'Redis', 'engine_version' => $info['redis_version']];
            } catch (Throwable $t) {
                switch (CAppUI::conf('shared_memory')) {
                    case 'apc':
                    case 'apcu':
                        $adapter  = new APCuAdapter();
                        $metadata = ['engine' => 'APCu', 'engine_version' => phpversion('apcu')];
                        break;

                    case 'disk':
                    default:
                        $adapter  = new FileAdapter(rtrim(CAppUI::conf('root_dir'), '/') . '/tmp/shared');
                        $metadata = ['engine' => 'Disk', 'engine_version' => 'N/A'];
                }
            }
        }

        $metadata = array_merge(['namespaced' => false], $metadata);

        // Adapter and Layer Metadata are shared by reference
        LayeredCache::getCache(LayeredCache::NONE)->setAdapter(
            LayeredCache::DISTR,
            (new KeySanitizerDecorator($adapter))->enableLogging($logging),
            $metadata
        );
    }

    public static function getLayerEngine(int $layer): ?string
    {
        return self::getLayerMetadata($layer, 'engine');
    }

    public static function getLayerEngineVersion(int $layer): ?string
    {
        return self::getLayerMetadata($layer, 'engine_version');
    }

    private static function getLayerMetadata(int $layer, string $data_name): ?string
    {
        $cache = self::getCache($layer);

        $metadata = $cache->getMetadata();

        $data = $metadata->get($layer, $data_name);

        if ($data === null) {
            return $data;
        }

        return (string)$data;
    }

    /**
     * @param int $layer
     *
     * @return array
     * @throws Exception
     * @deprecated
     */
    public static function getInfo(int $layer): array
    {
        $engine = Cache::getLayerEngine($layer);

        switch ($engine) {
            case 'APCu':
                return CacheInfo::getAPCuInfo(self::getLayerEngineVersion($layer));

            case 'Redis':
                return CacheInfo::getRedisInfo(self::getLayerEngineVersion($layer));

            case 'Disk':
                return CacheInfo::getDiskInfo(CAppUI::conf('root_dir') . '/tmp/shared/');

            default:
                return [];
        }
    }

    /**
     * @param int         $layer
     * @param string|null $prefix
     *
     * @return array
     * @throws Exception
     * @deprecated
     */
    public static function getKeysInfo(int $layer, ?string $prefix = null): array
    {
        $engine = Cache::getLayerEngine($layer);

        switch ($engine) {
            case 'APCu':
                return CacheInfo::getAPCuKeysInfo($prefix);

            case 'Redis':
                return CacheInfo::getRedisKeysInfo($prefix);

            case 'Disk':
                return CacheInfo::getDiskKeysInfo(CAppUI::conf('root_dir') . '/tmp/shared/', $prefix);

            default:
                return [];
        }
    }

    public static function isInitialized(): bool
    {
        return static::$is_initialized;
    }
}
