<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\ViewModels;

use Ox\Mediboard\Jfse\Domain\InsuranceType\FmfInsurance;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;
use Ox\Mediboard\Jfse\ViewModels\InsuranceType\CFmfInsurance;

class CFmfInsuranceTest extends UnitTestJfse
{
    public function testHydrate(): void
    {
        $insurance                          = new CFmfInsurance();
        $insurance->supported_fmf_existence = true;
        $insurance->supported_fmf_expense   = 20.4;

        $entity = FmfInsurance::hydrate(["supported_fmf_existence" => true, "supported_fmf_expense" => 20.4]);

        $this->assertEquals($insurance, CFmfInsurance::getFromEntity($entity));
    }
}
