<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Domain\VitalCard;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\ApiClients\VitalCardClient;
use Ox\Mediboard\Jfse\Domain\Vital\AmoFamily;
use Ox\Mediboard\Jfse\Domain\Vital\AmoServicePeriod;
use Ox\Mediboard\Jfse\Domain\Vital\Beneficiary;
use Ox\Mediboard\Jfse\Domain\Vital\CoverageCodePeriod;
use Ox\Mediboard\Jfse\Domain\Vital\HealthInsurance;
use Ox\Mediboard\Jfse\Domain\Vital\Insured;
use Ox\Mediboard\Jfse\Domain\Vital\Patient;
use Ox\Mediboard\Jfse\Domain\Vital\Period;
use Ox\Mediboard\Jfse\Domain\Vital\VitalCard;
use Ox\Mediboard\Jfse\Domain\Vital\VitalCardService;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

/**
 * @todo: implement tests
 */
class VitalCardServiceTest extends UnitTestJfse
{
    public function testReadVitalCard(): void
    {
        $json = <<<JSON
{
    "method":{
        "output":{
            "donneescv":{
                "groupe":101,
                "mode131":0,
                "noBeneficiaireSelectionne":0,
                "type":"T",
                "numeroSerie":"468963684",
                "dateFinValidite":"",
                "donneesAdministrationRUF1":"",
                "donneesAdministrationRUF2":"",
                "donneesAdministration":"",
                "typeRUFPorteur":"A",
                "NIR":"1421962965167",
                "cleNIR":"94",
                "codeRegime":"01",
                "caisseGestionnaire":"349",
                "centreGestionnaire":"9881",
                "codeGestion":"13",
                "donneesRUFFamille":"",
                "serviceAMOFamille":{
                    "groupe":102,
                    "code":"0",
                    "dateDebut":"00000000",
                    "dateFin":"00000000"
                },
                "lstDonneesBeneficiaire":[
                    {
                        "groupe":104,
                        "numero":1,
                        "nomUsuel":"ESSAI",
                        "nomPatronymique":"",
                        "prenom":"ALAINBIS",
                        "adresse1":"",
                        "adresse2":"",
                        "adresse3":"",
                        "adresse4":"",
                        "adresse5":"",
                        "NIRCertifie":"",
                        "cleNIRCertifie":"00",
                        "dateCertificationNIR":"00000000",
                        "dateNaissance":"19421901",
                        "rangGemellaire":"1",
                        "qualite":"0",
                        "libelleQualite":"Assuré",
                        "codeServiceAMO":"0",
                        "dateDebutServiceAMO":"00000000",
                        "dateFinServiceAMO":"00000000",
                        "donneesRUFAMO":"",
                        "INSC":"13093304893911662206",
                        "cleINSC":"75",
                        "erreurINSC":0,
                        "ACS":0,
                        "ACSLibelle":"",
                        "id":"1605867527455721306",
                        "lstPeriodeDroitsAMO":[
                            {
                                "groupe":105,
                                "dateDebut":"20120101",
                                "dateFin":"20151231"
                            }
                        ],
                        "lstPeriodeCodeCouverture":[
                            {
                                "groupe":106,
                                "dateDebut":"",
                                "dateFin":"19991003",
                                "codeALD":"0",
                                "codeSituation":"0100",
                                "codeExoStandard":"0",
                                "tauxStandard":"",
                                "flagAlsaceMoselle":0
                            }
                        ],
                        "donneesMutuelle":{
                            "groupe":107,
                            "identificationMutuelle":"62013354",
                            "garantiesEffectives":"OOOOOOOO",
                            "indicateurTraitement":"",
                            "typeServicesAssocies":"",
                            "servicesAssociesContrat":"",
                            "codeAiguillageSTS":"",
                            "libelle":"Mutuelle n°62013354",
                            "lstPeriodeDroitsMutuelle":[
                                {
                                    "groupe":108,
                                    "dateDebut":"20120101",
                                    "dateFin":"20151231"
                                }
                            ]
                        }
                    }
                ],
                "libelleRegime":"Régime général",
                "libelleCaisse":"Caisse de test",
                "libelleGestion":"Invalides de guerre"
            }
        }
    }
}
JSON;

        $patient = Patient::hydrate(
            [
                "last_name"  => "ESSAI",
                "birth_name" => "ESSAI",
                "first_name" => "ALAINBIS",
                "birth_date" => "1942-19-01",
                "address"    => null,
                "birth_rank" => 1,
            ]
        );

        $amo_service = AmoServicePeriod::hydrate(
            [
                "code"       => "0",
                "start_date" => null,
                "end_date"   => null,
                "ruf_data"   => "",
                'group'      => '104',
                'begin_date' => null,
                'end_date'   => null,
            ]
        );

        $coverage_code_period = CoverageCodePeriod::hydrate(
            [
                "group"                     => '106',
                "begin_date"                => null,
                "end_date"                  => new DateTimeImmutable("1999-10-03"),
                "ald_code"                  => "0",
                "situation_code"            => "0100",
                "standard_exoneration_code" => "0",
                "standard_rate"             => "",
                "alsace_mozelle_flag"       => '0',
            ]
        );

        $health_insurance = HealthInsurance::hydrate(
            [
                "group"                           => '107',
                "id"                              => "62013354",
                "effective_guarantees"            => "OOOOOOOO",
                "treatment_indicator"             => "",
                "associated_services"             => "",
                "associated_services_contract"    => "",
                "referral_sts_code"               => "",
                "label"                           => "Mutuelle n°62013354",
                "health_insurance_periods_rights" => [
                    Period::hydrate(
                        [
                            "group"      => '108',
                            'begin_date' => new DateTimeImmutable("2012-01-01"),
                            'end_date'   => new DateTimeImmutable("2015-12-31"),
                        ]
                    ),
                ],
            ]
        );

        $insured = Insured::hydrate([
            'nir' => '1421962965167',
            'nir_key' => '94',
            'last_name' => 'ESSAI',
            'first_name' => 'ALAINBIS',
            'birth_name' => 'ESSAI',
            'regime_code' => '01',
            'regime_label' => null,
            'managing_fund' => '349',
            'managing_center' => '9881',
            'managing_code' => '13',
            'situation_code' => null,
        ]);

        $beneficiary = Beneficiary::hydrate(
            [
                "id"                     => "1605867527455721306",
                "group"                  => '104',
                "number"                 => '1',
                "type"                   => "T",
                "serial_number"          => "468963684",
                "patient"                => $patient,
                "certified_nir"          => "",
                "nir_certification_date" => null,
                "certified_nir_key"  => "00",
                "quality"                => "0",
                "quality_label"          => "Assuré",
                "amo_service"            => $amo_service,
                "insc_number"            => "13093304893911662206",
                "insc_key"               => "75",
                "insc_error"             => "0",
                "acs"                    => "0",
                "acs_label"              => "",
                "amo_period_rights"      => [
                    Period::hydrate(
                        [
                            "group"      => 105,
                            "begin_date" => new DateTimeImmutable("2012-01-01"),
                            "end_date"   => new DateTimeImmutable("2015-12-31"),
                        ]
                    ),
                ],
                "coverage_code_periods"  => [$coverage_code_period],
                "health_insurance"       => $health_insurance,
                'insured'                => $insured,
            ]
        );

        $vital_card = VitalCard::hydrate([
            'group' => 101,
            'mode131' => 0,
            'selected_beneficiary_number' => 0,
            'type' => 'T',
            'serial_number' => '468963684',
            'expiration_date' => null,
            'ruf1_administration_data' => '',
            'ruf2_administration_data' => '',
            'administration_data' => '',
            'ruf_bearer_type' => 'A',
            'insured' => $insured,
            'ruf_family_data' => '',
            'fund_label' => 'Caisse de test',
            'managing_label' => 'Invalides de guerre',
            'amo_family_service' => AmoFamily::hydrate([
                'code' => '0',
                'group' => '102',
                'begin_date' => null,
                'end_date' => null,
            ]),
            'work_accident_data' => [],
            'beneficiaries' => [
                $beneficiary,
            ],
            'cps_absent' => false,
        ]);

        $client = new VitalCardClient(
            self::makeClientFromGuzzleResponses([
                self::makeJsonGuzzleResponse(200, utf8_encode($json))
            ])
        );

        $service = new VitalCardService($client);

        $this->assertEquals($vital_card, $service->read(false));
    }
}
