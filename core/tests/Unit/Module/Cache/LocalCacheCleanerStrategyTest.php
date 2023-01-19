<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Module;

use InvalidArgumentException;
use Ox\Core\CacheManager;
use Ox\Core\Module\Cache\CacheCleanerStrategyInterface;
use Ox\Core\Module\Cache\LocalCacheCleanerStrategy;
use Ox\Tests\OxUnitTestCase;

class LocalCacheCleanerStrategyTest extends OxUnitTestCase
{
    private const ALL   = 'all';
    private const LOCAL = 'local';

    /**
     * @dataProvider validStrategyProvider
     */
    public function testCreateValidStrategy(
        string $cache,
        int $layer,
        ?string $actual_host,
        string $expected_class
    ): void {
        $strategy = $this->createStrategy($cache, $layer, $actual_host);

        $this->assertInstanceOf($expected_class, $strategy);
    }

    /**
     * @dataProvider invalidStrategyProvider
     */
    public function testCreateInvalidStrategyAndThrowException(
        string $cache,
        int $layer,
        ?string $actual_host
    ): void {
        $this->expectException(InvalidArgumentException::class);

        $this->createStrategy($cache, $layer, $actual_host);
    }

    public function validStrategyProvider(): array
    {
        return [
            "all local (SHM)" => [
                self::ALL,
                CacheManager::SHM,
                self::LOCAL,
                LocalCacheCleanerStrategy::class,
            ],
        ];
    }

    public function invalidStrategyProvider(): array
    {
        return [
            "all local without actual host (SHM)" => [
                self::ALL,
                CacheManager::SHM,
                null,
            ],
        ];
    }

    private function createStrategy(
        string $cache,
        int $layer,
        ?string $actual_host
    ): CacheCleanerStrategyInterface {
        return new LocalCacheCleanerStrategy($cache, $layer, $actual_host);
    }
}
