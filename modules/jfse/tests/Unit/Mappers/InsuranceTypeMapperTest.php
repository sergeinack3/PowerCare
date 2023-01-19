<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Mappers;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Domain\InsuranceType\AbstractInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\MaternityInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\MedicalInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\FmfInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\WorkAccidentInsurance;
use Ox\Mediboard\Jfse\Mappers\InsuranceTypeMapper;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

/**
 * Class InsuranceTypeMapperTest
 *
 * @package Ox\Mediboard\Jfse\Tests\Unit\Mappers
 */
class InsuranceTypeMapperTest extends UnitTestJfse
{
    /**
     * @dataProvider insuranceTypeProvider
     *
     * @param AbstractInsurance $actual_type
     * @param string            $invoice_id
     * @param array             $expected
     */
    public function testGetArrayFromSicknessInsuranceType(
        AbstractInsurance $actual_type,
        string $invoice_id,
        array $expected
    ): void {
        $returned_data = (new InsuranceTypeMapper())->getArrayFromInsuranceType($actual_type, $invoice_id);

        $this->assertEquals($expected, $returned_data);
    }

    /**
     * Provider for the different insurance types
     *
     * @return array
     */
    public function insuranceTypeProvider(): array
    {
        $medical_insurance          = MedicalInsurance::hydrate(["code_exoneration_disease" => 5]);
        $expected_medical_insurance = [
            "idFacture"       => '1',
            "natureAssurance" => [
                'maladie' => [
                    "codeExoneration" => 5,
                ],
            ],
        ];

        $data                             = [
            "date"                          => new DateTimeImmutable('2020-10-14'),
            "has_physical_document"         => true,
            "number"                        => 2,
            "organisation_support"          => 3,
            "is_organisation_identical_amo" => false,
            "organisation_vital"            => 67,
            "shipowner_support"             => 1,
            "amount_apias"                  => 87.9,
        ];
        $work_accident_insurance          = WorkAccidentInsurance::hydrate($data);
        $expected_work_accident_insurance = [
            "idFacture"       => '1',
            "natureAssurance" => [
                'AT' => [
                    "date"                  => '20201014',
                    "presenceFeuillet"      => true,
                    "numero"                => 2,
                    "refCaisseSupport"      => 3,
                    "caisseIdentiqueAMO"    => false,
                    "refCaisseCV"           => 67,
                    "priseEnChargeArmateur" => 1,
                    "montantPECApias"       => 87.9,
                ],
            ],
        ];

        $data                         = [
            "date"              => new DateTimeImmutable('2020-10-14'),
            "force_exoneration" => true,
        ];
        $maternity_insurance          = MaternityInsurance::hydrate($data);
        $expected_maternity_insurance = [
            "idFacture"       => '1',
            "natureAssurance" => [
                'maternite' => [
                    "date"    => '20201014',
                    "forcage" => true,
                ],
            ],
        ];

        $data                   = [
            "supported_fmf_existence" => true,
            "supported_fmf_expense"   => 76.8,
        ];
        $fmf_type               = FmfInsurance::hydrate($data);
        $expected_fmf_insurance = [
            "idFacture"       => '1',
            "natureAssurance" => [
                'SMG' => [
                    "existencePEC" => true,
                    "montantPEC"   => 76.8,
                ],
            ],
        ];

        return [
            [$medical_insurance, '1', $expected_medical_insurance],
            [$work_accident_insurance, '1', $expected_work_accident_insurance],
            [$maternity_insurance, '1', $expected_maternity_insurance],
            [$fmf_type, '1', $expected_fmf_insurance],
        ];
    }
}
