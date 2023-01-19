<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Urgences\Tests\Unit;

use Ox\Mediboard\Urgences\Services\RPUService;
use Ox\Tests\OxUnitTestCase;

class RPUServiceTest extends OxUnitTestCase
{
    /**  @dataProvider orderProvider */
    public function testComputePaginationReturnString(string $order_col, string $order_way, string $expected): void
    {
        $rpu_service = new RPUService();

        $rpu_service->computePagination($order_col, $order_way);
        $this->assertEquals($expected, $rpu_service->getOrder());
    }

    public function orderProvider(): array
    {
        return [
            'extract_passage'    => [
                '_first_extract_passages',
                'ASC',
                'extract_passages.date_extract ASC',
            ],
            'wrong_value'    => [
                'wrong value',
                'DESC',
                'sejour.entree DESC',
            ],
        ];
    }
}
