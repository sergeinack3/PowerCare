<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\ViewModels;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Domain\InsuranceType\MaternityInsurance;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;
use Ox\Mediboard\Jfse\ViewModels\InsuranceType\CMaternityInsurance;

class CMaternityInsuranceTest extends UnitTestJfse
{
    public function testHydrate(): void
    {
        $insurance                    = new CMaternityInsurance();
        $insurance->date              = "2020-10-19";
        $insurance->force_exoneration = true;

        $entity = MaternityInsurance::hydrate(
            ["date" => new DateTimeImmutable("2020-10-19"), "force_exoneration" => true]
        );

        $this->assertEquals($insurance, CMaternityInsurance::getFromEntity($entity));
    }
}
