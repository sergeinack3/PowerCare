<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Controllers;

use Ox\Mediboard\Jfse\Controllers\PrescribingPhysicianController;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class PrescribingPhysicianControllerTest extends UnitTestJfse
{

    public function testGetRoutePrefix(): void
    {
        $this->assertNotEmpty(PrescribingPhysicianController::getRoutePrefix());
    }
}
