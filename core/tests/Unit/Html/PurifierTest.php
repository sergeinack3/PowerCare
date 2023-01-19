<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Html;

use Ox\Core\Cache;
use Ox\Core\Html\Purifier;
use Ox\Core\Html\PurifierInterface;
use Ox\Tests\OxUnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Description
 */
class PurifierTest extends OxUnitTestCase
{
    public function testCacheIsUsedWhenPurifying(): void
    {
        $adapter = $this->getAdapter();

        $adapter->method('removeHtml')->willReturn('html text');

        $purifier = $this->getPurifier($adapter);
        $cache    = $this->getCache();

        $purifier->method('getCache')
            ->willReturn($cache);

        $cache->expects($this->exactly(2))->method('get')->willReturnOnConsecutiveCalls(null, 'html text');
        $cache->expects($this->once())->method('put');

        $adapter->expects($this->once())->method('removeHtml');

        // Asserting that adapter is used here
        $this->assertEquals('html text', $purifier->removeHtml('html text'));

        // Asserting that cache is used here
        $this->assertEquals('html text', $purifier->removeHtml('html text'));
    }

    public function testCacheIsNotUsedWhenStringShorterThanSixAndPurifying(): void
    {
        $adapter = $this->getAdapter();

        $adapter->method('removeHtml')->willReturn('html');

        $purifier = $this->getPurifier($adapter);
        $cache    = $this->getCache();

        $purifier->method('getCache')
            ->willReturn($cache);

        $cache->method('get')->willReturnOnConsecutiveCalls(null, 'html');
        $cache->expects($this->never())->method('put');

        // Asserting that adapter is used here
        $this->assertEquals('html', $purifier->removeHtml('html text'));

        // Asserting that cache is used here
        $this->assertEquals('html', $purifier->removeHtml('html text'));
    }

    public function testCacheIsUsedWhenRemoving(): void
    {
        $adapter = $this->getAdapter();

        $adapter->method('removeHtml')->willReturn('html text');

        $purifier = $this->getPurifier($adapter);
        $cache    = $this->getCache();

        $purifier->method('getCache')
            ->willReturn($cache);

        $cache->expects($this->exactly(2))->method('get')->willReturnOnConsecutiveCalls(null, 'html text');
        $cache->expects($this->once())->method('put');

        $adapter->expects($this->once())->method('removeHtml');

        // Asserting that adapter is used here
        $this->assertEquals('html text', $purifier->removeHtml('html text'));

        // Asserting that cache is used here
        $this->assertEquals('html text', $purifier->removeHtml('html text'));
    }

    public function testCacheIsNotUsedWhenStringShorterThanSixAndRemoving(): void
    {
        $adapter = $this->getAdapter();

        $adapter->method('removeHtml')->willReturn('html');

        $purifier = $this->getPurifier($adapter);
        $cache    = $this->getCache();

        $purifier->method('getCache')
            ->willReturn($cache);

        $cache->method('get')->willReturnOnConsecutiveCalls(null, 'html');
        $cache->expects($this->never())->method('put');

        // Asserting that adapter is used here
        $this->assertEquals('html', $purifier->removeHtml('html text'));

        // Asserting that cache is used here
        $this->assertEquals('html', $purifier->removeHtml('html text'));
    }

    /**
     * @return PurifierInterface|MockObject
     */
    private function getAdapter()
    {
        return $this->getMockBuilder(PurifierInterface::class)->getMock();
    }

    /**
     * @param PurifierInterface $adapter
     *
     * @return Purifier|MockObject
     */
    private function getPurifier(PurifierInterface $adapter)
    {
        return $this->getMockBuilder(Purifier::class)
            ->setConstructorArgs([$adapter])
            ->setMethods(['getCache'])
            ->getMock();
    }

    /**
     * @return Cache|MockObject
     */
    private function getCache()
    {
        return $this->getMockBuilder(Cache::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
