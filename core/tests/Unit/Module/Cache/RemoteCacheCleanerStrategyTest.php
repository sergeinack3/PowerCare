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
use Ox\Core\Module\Cache\RemoteCacheCleanerStrategy;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpClient\HttpClient;

class RemoteCacheCleanerStrategyTest extends OxUnitTestCase
{
    private const ALL    = 'all';
    private const HOST   = '127.0.0.1';
    private const COOKIE = 'lorem=ipsum';

    /**
     * @dataProvider validStrategyProvider
     */
    public function testCreateValidStrategy(
        string $cache,
        ?string $actual_host,
        ?string $cookie,
        string $expected_class
    ): void {
        $strategy = $this->createStrategy($cache, $actual_host, $cookie);

        $this->assertInstanceOf($expected_class, $strategy);
    }

    /**
     * @dataProvider invalidStrategyProvider
     */
    public function testCreateInvalidStrategyAndThrowException(
        string $cache,
        ?string $actual_host,
        ?string $cookie
    ): void {
        $this->expectException(InvalidArgumentException::class);

        $this->createStrategy($cache, $actual_host, $cookie);
    }

    public function validStrategyProvider(): array
    {
        return [
            "all remote (SHM)" => [
                self::ALL,
                self::HOST,
                self::COOKIE,
                RemoteCacheCleanerStrategy::class,
            ],
        ];
    }

    public function invalidStrategyProvider(): array
    {
        return [
            "all remote without cookie (SHM)" => [
                self::ALL,
                self::HOST,
                '',
            ],
            "all remote without target (SHM)" => [
                self::ALL,
                '',
                self::COOKIE,
            ],
        ];
    }

    private function createStrategy(
        string $cache,
        ?string $actual_host,
        ?string $cookie
    ): CacheCleanerStrategyInterface {
        return new RemoteCacheCleanerStrategy(
            $cache,
            CacheManager::SHM_SPECIAL,
            HttpClient::create(),
            $actual_host,
            $cookie
        );
    }
}
