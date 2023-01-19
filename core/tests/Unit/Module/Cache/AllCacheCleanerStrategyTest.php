<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Module;

use InvalidArgumentException;
use Ox\Core\Module\Cache\AllCacheCleanerStrategy;
use Ox\Core\Module\Cache\CacheCleanerStrategyInterface;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpClient\HttpClient;

class AllCacheCleanerStrategyTest extends OxUnitTestCase
{
    private const ALL    = 'all';
    private const LOCAL  = 'local';
    private const HOST   = '127.0.0.1';
    private const COOKIE = 'lorem=ipsum';

    /**
     * @dataProvider validStrategyProvider
     */
    public function testCreateValidStrategy(
        string $cache,
        ?array $hosts,
        ?string $cookie,
        ?string $actual_host,
        string $expected_class
    ): void {
        $strategy = $this->createStrategy($cache, $hosts, $cookie, $actual_host);

        $this->assertInstanceOf($expected_class, $strategy);
    }

    /**
     * @dataProvider invalidStrategyProvider
     */
    public function testCreateInvalidStrategyAndThrowException(
        string $cache,
        ?array $hosts,
        ?string $cookie,
        ?string $actual_host
    ): void {
        $this->expectException(InvalidArgumentException::class);

        $this->createStrategy($cache, $hosts, $cookie, $actual_host);
    }

    public function validStrategyProvider(): array
    {
        return [
            "all local" => [
                self::ALL,
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
            "all local without cookie (SHM)" => [
                self::ALL,
                [self::HOST],
                '',
                self::LOCAL,
                AllCacheCleanerStrategy::class,
            ],
            "all local without hosts (SHM)" => [
                self::ALL,
                [],
                self::COOKIE,
                self::HOST,
                AllCacheCleanerStrategy::class,
            ],
        ];
    }

    private function createStrategy(
        string $cache,
        ?array $hosts,
        ?string $cookie,
        ?string $actual_host
    ): CacheCleanerStrategyInterface {
        return new AllCacheCleanerStrategy(
            $cache,
            HttpClient::create(),
            $hosts,
            $cookie,
            $actual_host
        );
    }
}
