<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Pmsi\Tests\Unit;

use Ox\Mediboard\Pmsi\CRelancePMSI;
use Ox\Mediboard\Pmsi\Services\PMSIRelanceService;
use Ox\Tests\OxUnitTestCase;

class PMSIRelanceServiceTest extends OxUnitTestCase
{
    /**
     * @return void
     */
    public function testSortRelancesByPrat(): void
    {
        $pmsi_relance_service = new PMSIRelanceService();

        $relance1 = new CRelancePMSI();
        $relance2 = new CRelancePMSI();
        $relance3 = new CRelancePMSI();

        $relance1->chir_id = $relance2->chir_id = 2;
        $relance3->chir_id = 3;

        $expected = [
            2 => [
                $relance1,
                $relance2,
            ],
            3 => [$relance3],
        ];

        $actual = $pmsi_relance_service->getRelancesByPrat([$relance1, $relance2, $relance3]);

        $this->assertEquals($expected, $actual);
    }
}
