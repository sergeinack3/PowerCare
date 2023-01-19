<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Urgences\Tests\Unit;


use Exception;
use Ox\Mediboard\Urgences\CExtractPassages;
use Ox\Tests\OxUnitTestCase;

class CExtractPassagesTest extends OxUnitTestCase
{
    public function providerNotifyErrorFailed(): array
    {
        return [
            ['data' => [], 'mock_store' => 'error'],
            ['data' => ['_id' => 1], 'mock_store' => 'error'],
            ['data' => ['_id' => 1], 'mock_store' => 'exception'],
            ['data' => ['_id' => 1, 'nb_tentatives' => 1], 'mock_store' => 'store'],
            ['data' => ['_id' => 1, 'nb_tentatives' => 3], 'mock_store' => 'store'],
        ];
    }

    /**
     * @param array  $data
     * @param string $mock_store
     * @dataProvider providerNotifyErrorFailed
     */
    public function testNotifyErrorFailed(array $data, string $mock_store): void
    {
        $passage = $this->getMockBuilder(CExtractPassages::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['store', 'notifyRPUError'])
            ->getMock();

        // prevent mail sending in Test
        $passage->method('notifyRPUError')->willReturn(false);

        if ($mock_store === "store") {
            $passage->method('store')->willReturn('');
        } elseif ($mock_store === 'error') {
            $passage->method('store')->willReturn('error');
        } elseif ($mock_store === "exception") {
            $passage->method('store')->willThrowException(new Exception());
        }

        foreach ($data as $prop => $value) {
            $passage->{$prop} = $value;
        }

        $this->assertFalse($passage->notifyError());
    }

    public function testNotifyError(): void
    {
        $passage = $this->getMockBuilder(CExtractPassages::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['store', 'notifyRPUError'])
            ->getMock();
        $passage->method('store')->willReturn('');
        $passage->method('notifyRPUError')->willReturn(true);

        $passage->_id = 1;
        $passage->nb_tentatives = 4;

        $this->assertTrue($passage->notifyError());
    }
}
