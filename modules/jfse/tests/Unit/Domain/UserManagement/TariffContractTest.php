<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Domain\UserManagement;

use Ox\Mediboard\Jfse\Domain\UserManagement\TariffContract;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class TariffContractTest extends UnitTestJfse
{
    public function testJsonSerialize(): void
    {
        $this->assertEquals(
            ['code' => 127, 'label' => 'OPTAM'],
            (TariffContract::hydrate(['code' => 127, 'label' => 'OPTAM']))->jsonSerialize()
        );
    }
}
