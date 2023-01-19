<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Module;

use InvalidArgumentException;
use Ox\Core\CacheManager;
use Ox\Core\Module\Cache\AllCacheCleanerStrategy;
use Ox\Core\Module\Cache\CacheCleanerStrategyFactory;
use Ox\Core\Module\Cache\CacheCleanerStrategyInterface;
use Ox\Core\Module\Cache\LocalCacheCleanerStrategy;
use Ox\Core\Module\Cache\RemoteCacheCleanerStrategy;
use Ox\Tests\OxUnitTestCase;

class CacheCleanerStrategyFactoryTest extends OxUnitTestCase
{
    private const ALL    = 'all';
    private const LOCAL  = 'local';
    private const HOST   = '127.0.0.1';
    private const COOKIE = 'lorem=ipsum';

    /**
     * @dataProvider validStrategyProvider
     */
    public function testFactoryCreateValidStrategy(
        string $cache,
        string $target,
        int $layer,
        ?array $hosts,
        ?string $cookie,
        ?string $actual_host,
        ?string $expected_class = null
    ): void {
        $strategy = $this->createFactory($cache, $target, $layer, $hosts, $cookie, $actual_host);

        $this->assertInstanceOf($expected_class, $strategy);
    }

    /**
     * @dataProvider invalidStrategyProvider
     */
    public function testFactoryCreateInvalidStrategyAndThrowException(
        string $cache,
        string $target,
        int $layer,
        ?array $hosts,
        ?string $cookie,
        ?string $actual_host
    ): void {
        $this->expectException(InvalidArgumentException::class);

        $this->createFactory($cache, $target, $layer, $hosts, $cookie, $actual_host);
    }

    public function validStrategyProvider(): array
    {
        return [
            "all local (SHM)"                         => [
                self::ALL,
                self::LOCAL,
                CacheManager::SHM,
                null,
                null,
                self::LOCAL,
                LocalCacheCleanerStrategy::class,
            ],
            "all local with different target (SHM)"   => [
                self::ALL,
                self::HOST,
                CacheManager::SHM,
                null,
                null,
                self::HOST,
                LocalCacheCleanerStrategy::class,
            ],
            "all remote (SHM)"                        => [
                self::ALL,
                self::HOST,
                CacheManager::SHM,
                null,
                self::COOKIE,
                null,
                RemoteCacheCleanerStrategy::class,
            ],
            "all layers with target local (SHM+DSHM)" => [
                self::ALL,
                self::LOCAL,
                CacheManager::ALL,
                [self::HOST],
                self::COOKIE,
                self::LOCAL,
                AllCacheCleanerStrategy::class,
            ],
            "all (DSHM)"                              => [
                self::ALL,
                self::ALL,
                CacheManager::DSHM,
                null,
                null,
                self::LOCAL,
                LocalCacheCleanerStrategy::class,
            ],
            "all (SHM+DSHM)"                          => [
                self::ALL,
                self::ALL,
                CacheManager::ALL,
                [self::HOST],
                self::COOKIE,
                self::LOCAL,
                AllCacheCleanerStrategy::class,
            ],
        ];
    }

    public function invalidStrategyProvider(): array
    {
        return [
            "all local without actual host (SHM)" => [
                self::ALL,
                self::LOCAL,
                CacheManager::SHM,
                null,
                null,
                null,
            ],
            "all remote without cookie (SHM)"     => [
                self::ALL,
                self::HOST,
                CacheManager::SHM,
                null,
                '',
                null,
            ],
            "all without hosts (SHM+DSHM)"        => [
                self::ALL,
                self::ALL,
                CacheManager::ALL,
                null,
                self::COOKIE,
                self::LOCAL,
            ],
            "all without cookie (SHM+DSHM)"       => [
                self::ALL,
                self::ALL,
                CacheManager::ALL,
                [self::HOST],
                '',
                self::LOCAL,
            ],
            "all without layer (SHM+DSHM)"       => [
                self::ALL,
                self::ALL,
                0,
                [self::HOST],
                '',
                self::LOCAL,
            ],
            "all without cache key (SHM+DSHM)"       => [
                '',
                self::ALL,
                CacheManager::ALL,
                [self::HOST],
                '',
                self::LOCAL,
            ],
        ];
    }

    private function createFactory(
        string $cache,
        string $target,
        int $layer,
        ?array $hosts,
        ?string $cookie,
        ?string $actual_host
    ): CacheCleanerStrategyInterface {
        $factory = new CacheCleanerStrategyFactory($cache, $target, $layer);

        if ($hosts !== null) {
            $factory->withHosts($hosts);
        }

        if ($cookie !== null) {
            $factory->withCookie($cookie);
        }

        if ($actual_host !== null) {
            $factory->withActualHost($actual_host);
        }

        return $factory->create();
    }
}
