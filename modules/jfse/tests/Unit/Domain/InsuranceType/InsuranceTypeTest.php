<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Domain\InsuranceType;

use Ox\Mediboard\Jfse\Domain\InsuranceType\InsuranceType;
use Ox\Mediboard\Jfse\Mappers\InsuranceTypeMapper;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;
use ReflectionClass;

/**
 * Class InsuranceTypeTest
 *
 * @package Ox\Mediboard\Jfse\Domain\InsuranceType
 */
class InsuranceTypeTest extends UnitTestJfse
{
    /**
     * From an array, return on object
     */
    public function testHydrateReturnsAnInsuranceType(): void
    {
        $insurance = new InsuranceType();

        $reflection = new ReflectionClass(InsuranceType::class);
        $prop       = $reflection->getProperty('code');
        $prop->setAccessible(true);
        $prop->setValue($insurance, 1);
        $prop = $reflection->getProperty('label');
        $prop->setAccessible(true);
        $prop->setValue($insurance, 'Libelle 1');

        $this->assertEquals($insurance, InsuranceType::hydrate(['code' => 1, 'label' => 'Libelle 1']));
    }

    public function testInsuranceTypeReturnsLibelle(): void
    {
        $insurance = InsuranceType::hydrate(['code' => 1, 'label' => 'Libelle 1']);

        $this->assertEquals('Libelle 1', $insurance->getLabel());
    }

    public function testInsuranceTypeReturnsCode(): void
    {
        $insurance = InsuranceType::hydrate(['code' => 1, 'label' => 'Libelle 1']);

        $this->assertEquals(1, $insurance->getCode());
    }
}
