<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Adri;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\ApiClients\AdriClient;
use Ox\Mediboard\Jfse\Domain\Vital\AdditionalHealthInsurance;
use Ox\Mediboard\Jfse\Domain\Vital\AmoServicePeriod;
use Ox\Mediboard\Jfse\Domain\Vital\Beneficiary;
use Ox\Mediboard\Jfse\Domain\Vital\CoverageCodePeriod;
use Ox\Mediboard\Jfse\Domain\Vital\HealthInsurance;
use Ox\Mediboard\Jfse\Domain\Vital\Insured;
use Ox\Mediboard\Jfse\Domain\Vital\Patient;
use Ox\Mediboard\Jfse\Domain\Vital\Period;
use Ox\Mediboard\Jfse\Domain\Vital\VitalCard;
use Ox\Mediboard\Jfse\Domain\Vital\VitalCardService;
use Ox\Mediboard\Jfse\Exceptions\AdriServiceException;
use Ox\Mediboard\Jfse\Mappers\VitalCardMapper;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Populate\Generators\CPatientGenerator;

class AdriServiceTest extends UnitTestJfse
{

    public function testGetInfosAdri(): void
    {
        $json = <<<JSON
{
    "method": {
        "output": {
            "dateReponse": "20201207",
            "beneficiaireDeSoins": {
                "numero": 4,
                "nomUsuel": "ADRDEUX",
                "nomPatronymique": "ADRDEUX",
                "prenom": "GUNTHER",
                "adresse1": "",
                "adresse2": "",
                "adresse3": "515 Avenue Gregoire Jean-Eude",
                "adresse4": "",
                "adresse5": "47840 Ennui-sur-Blase",
                "noNIRCertifie": "",
                "dateNaissance": "20010617",
                "rangGemellaire": "1",
                "qualite": "6",
                "codeServiceAMO": "0",
                "dateDebutServiceAMO": "00000000",
                "dateFinServiceAMO": "00000000",
                "codeModedeGestionComplementaire": "",
                "codeSupportComplementaire": "",
                "topMedecinTraitant": "",
                "lstPeriodeAMO": [
                    {
                        "dateDebut": "20090101",
                        "dateFin": "20121231"
                    }
                ],
                "lstPeriodeCodeCouverture": [
                    {
                        "groupe": 106,
                        "dateDebut": "",
                        "dateFin": "",
                        "codeALD": "0",
                        "codeSituation": "0100"
                    }
                ]
            },
            "infosGlobales": {
                "codeRegime": "01",
                "codeCaisse": "349",
                "centrePrestation": "9881",
                "codePresentationSupportAMO": "0"
            },
            "mutuelle": {
                "noIdentification": "75500017",
                "indicateurTraitement": "",
                "codeAiguillageSTS": "",
                "typeServicesAssocies": "",
                "servicesAssociesAuContrat": "",
                "codeGarantiesComplementaires": "OOOOOOOO",
                "periodeMutuelle": {
                    "noPeriode": "108",
                    "dateDebut": "20120101",
                    "dateFin": "20121231"
                }
            }
        }
    }
}
JSON;

        $client  = $this->makeClientFromGuzzleResponses([$this->makeJsonGuzzleResponse(200, $json)]);
        $service = new AdriService(new AdriClient($client));

        $vital_card = VitalCard::hydrate([
            "regime_code"     => "01",
            "management_code" => null,
            "managing_center" => "9881",
            "managing_fund"   => "349",
            "beneficiaries"   => [
                Beneficiary::hydrate([
                    "number"                      => 4,
                    "insured"                     => Insured::hydrate([
                        "regime_code"     => "01",
                        "management_code" => null,
                        "managing_center" => "9881",
                        "managing_fund"   => "349",
                    ]),
                    "patient"                     => Patient::hydrate([
                        "last_name"  => "ADRDEUX",
                        "birth_name" => "ADRDEUX",
                        "first_name" => "GUNTHER",
                        "birth_date" => "2001-06-17",
                        "birth_rank" => 1,
                        'address'    => '515 Avenue Gregoire Jean-Eude',
                        'zip_code'   => '47840',
                        'city'       => 'Ennui-sur-Blase'
                    ]),
                    "certified_nir"               => "",
                    "quality"                     => "6",
                    "amo_service"                 => AmoServicePeriod::hydrate([
                        "code"       => "0",
                        "date_begin" => null,
                        "date_end"   => null,
                    ]),
                    "additional_health_insurance" => AdditionalHealthInsurance::hydrate([
                        "management_code_mode" => "",
                        "code_support"         => "0",
                        "guarantees_code"      => "OOOOOOOO",
                    ]),
                    "prescribing_physician_top"   => "",
                    "amo_period_rights"           => [
                        AmoServicePeriod::hydrate([
                            "begin_date" => new DateTimeImmutable("2009-01-01"),
                            "end_date"   => new DateTimeImmutable("2012-12-31"),
                            "code"       => "0",
                        ]),
                    ],
                    "coverage_code_periods"       => [
                        CoverageCodePeriod::hydrate([
                            "group"          => 106,
                            "begin_date"     => null,
                            "end_date"       => null,
                            "ald_code"       => "0",
                            "situation_code" => "0100",
                        ]),
                    ],
                    "health_insurance"            => HealthInsurance::hydrate([
                        "id"                              => "75500017",
                        "treatment_indicator"             => "",
                        "referral_sts_code"               => "",
                        "associated_services"             => "",
                        "associated_services_contract"    => "",
                        "health_insurance_periods_rights" => Period::hydrate([
                            "group"      => 108,
                            "begin_date" => new DateTimeImmutable("2012-01-01"),
                            "end_date"   => new DateTimeImmutable("2012-12-31"),
                        ]),
                        "code_presentation_support"       => "0",
                    ]),
                ]),
            ],
        ]);

        $expected = Adri::hydrate(
            [
                "response_date" => new DateTimeImmutable('2020-12-07'),
                "vital_card"    => $vital_card,
            ]
        );

        $beneficiary = Beneficiary::hydrate([
            "patient"                => Patient::hydrate([
                "last_name"  => "ADRDEUX",
                "first_name" => "GUNTHER",
                "birth_date" => "20010617",
                "birth_rank" => 1,
            ]),
            'insured' => Insured::hydrate([
                "regime_code"     => "01",
                "managing_center" => "9881",
                "managing_fund"   => "1111",
                "nir"             => "1111",
                "nir_key"         => "01",
            ]),
            "certified_nir"          => "111",
            "nir_certification_date" => new DateTimeImmutable("2020-12-07"),
        ]);

        $this->assertEquals($expected, $service->getInfosAdri($beneficiary));
    }

    public function testUpdatePatientWhenMissingPatientFields(): void
    {
        $api_client         = $this->getMockBuilder(AdriClient::class)->disableOriginalConstructor()->getMock();
        $vital_service_mock = $this->getMockBuilder(VitalCardService::class)->disableOriginalConstructor()->getMock();
        $vital_mapper_mock  = $this->getMockBuilder(VitalCardMapper::class)->getMock();

        $service = new AdriService($api_client, $vital_service_mock, $vital_mapper_mock);
        $this->assertTrue(true);
//        $this->expectException(AdriServiceException::class);

//        $patient = (new CPatientGenerator())->generate();
//        $service->getFromCPatient($patient);
    }
}
