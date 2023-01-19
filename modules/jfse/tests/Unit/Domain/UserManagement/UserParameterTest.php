<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Domain\UserManagement;

use Ox\Mediboard\Jfse\Domain\UserManagement\UserParameter;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class UserParameterTest extends UnitTestJfse
{
    /** @var UserParameter */
    private $parameter;

    public function setUp(): void
    {
        parent::setUp();

        $this->parameter = UserParameter::hydrate(['id' => 127, 'name' => 'Contrat tarifaire', 'value' => 1]);
    }

    public function testGetId(): void
    {
        $this->assertEquals(127, $this->parameter->getId());
    }

    public function testGetName(): void
    {
        $this->assertEquals('Contrat tarifaire', $this->parameter->getName());
    }

    public function testGetValue(): void
    {
        $this->assertEquals(1, $this->parameter->getValue());
    }
}
