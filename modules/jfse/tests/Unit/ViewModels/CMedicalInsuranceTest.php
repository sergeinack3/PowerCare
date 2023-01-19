<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\ViewModels;

use Ox\Mediboard\Jfse\Domain\InsuranceType\MedicalInsurance;
use Ox\Mediboard\Jfse\ViewModels\InsuranceType\CMedicalInsurance;
use PHPUnit\Framework\TestCase;

class CMedicalInsuranceTest extends TestCase
{
    public function testGetFromEntity(): void
    {
        $model                           = new CMedicalInsurance();
        $model->code_exoneration_disease = 31;

        $entity = MedicalInsurance::hydrate(['code_exoneration_disease' => 31]);

        $this->assertEquals($model, CMedicalInsurance::getFromEntity($entity));
    }
}
