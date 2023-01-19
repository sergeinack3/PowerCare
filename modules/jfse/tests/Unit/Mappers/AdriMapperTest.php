<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use DateTime;
use Ox\Mediboard\Jfse\Domain\Vital\Beneficiary;
use Ox\Mediboard\Jfse\Domain\Vital\Insured;
use Ox\Mediboard\Jfse\Domain\Vital\Patient;
use Ox\Mediboard\Jfse\Domain\Vital\VitalCard;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class AdriMapperTest extends UnitTestJfse
{
    public function testVitalCardToArray(): void
    {
        $beneficiary    = Beneficiary::hydrate([
            "patient" => Patient::hydrate([
                "birth_date" => "19950203",
                "birth_rank" => 1,
            ]),
            "insured"       => Insured::hydrate([
                "regime_code" => "01",
                "nir"         => "1501962965225",
                "nir_key"     => "12",
            ]),
        ]);

        $expected = [
            "codeRegime"      => "01",
            "immatriculation" => "150196296522512",
            "dateNaissance"   => "19950203",
            "rangGemellaire"  => "1",
        ];

        $this->assertEquals($expected, (new AdriMapper())->beneficiaryToArray($beneficiary));
    }
}
