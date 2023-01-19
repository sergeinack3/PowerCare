<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\InsuranceType;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Domain\InsuranceType\WorkAccidentInsurance;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class CWorkAccidentInsuranceTest extends UnitTestJfse
{
    public function testGetFromEntity(): void
    {
        $model                                = new CWorkAccidentInsurance();
        $model->date                          = '2020-10-19';
        $model->has_physical_document         = '1';
        $model->number                        = '345';
        $model->organisation_support          = 2;
        $model->is_organisation_identical_amo = '0';
        $model->organisation_vital            = 2;
        $model->shipowner_support             = '1';
        $model->amount_apias                  = 20.0;

        $entity = WorkAccidentInsurance::hydrate(
            [
                "date"                          => new DateTimeImmutable('2020-10-19'),
                "has_physical_document"         => true,
                "number"                        => '345',
                "organisation_support"          => 2,
                "is_organisation_identical_amo" => false,
                "organisation_vital"            => 2,
                "shipowner_support"             => 1,
                "amount_apias"                  => 20.0,
            ]
        );

        $this->assertEquals($model, CWorkAccidentInsurance::getFromEntity($entity));
    }
}
