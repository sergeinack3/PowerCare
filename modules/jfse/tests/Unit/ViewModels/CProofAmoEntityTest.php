<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\ViewModels;

use Ox\Mediboard\Jfse\Domain\ProofAmo\ProofAmoType;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;
use Ox\Mediboard\Jfse\ViewModels\CProofAmoType;

/**
 * Class CProofAmoEntityTest
 * @package Ox\Mediboard\Jfse\Tests\Unit\ViewModels
 */
class CProofAmoEntityTest extends UnitTestJfse
{
    /**
     * Create a entity Type AMO and return a view model
     */
    public function testNewProofAmo(): void
    {
        $type = ProofAmoType::hydrate(['code' => 2, 'label' => 'Libelle 1']);

        $actual = CProofAmoType::getFromEntity($type);

        $expected          = new CProofAmoType();
        $expected->label = 'Libelle 1';
        $expected->code    = '2';

        $this->assertEquals($expected, $actual);
    }
}
