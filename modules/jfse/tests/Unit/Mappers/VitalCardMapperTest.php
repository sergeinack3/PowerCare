<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Domain\Vital\AdditionalHealthInsurance;
use Ox\Mediboard\Jfse\Domain\Vital\AdditionalHealthInsuranceRuf;
use Ox\Mediboard\Jfse\Domain\Vital\AmoServicePeriod;
use Ox\Mediboard\Jfse\Domain\Vital\Beneficiary;
use Ox\Mediboard\Jfse\Domain\Vital\CoverageCodePeriod;
use Ox\Mediboard\Jfse\Domain\Vital\HealthInsurance;
use Ox\Mediboard\Jfse\Domain\Vital\Insured;
use Ox\Mediboard\Jfse\Domain\Vital\InvoicingTla;
use Ox\Mediboard\Jfse\Domain\Vital\Patient;
use Ox\Mediboard\Jfse\Domain\Vital\Period;
use Ox\Mediboard\Jfse\Domain\Vital\VitalCard;
use Ox\Mediboard\Jfse\Domain\Vital\WorkAccident;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;
use ReflectionClass;

class VitalCardMapperTest extends UnitTestJfse
{
    /** @var ReflectionClass */
    private $mapper;

    public function setUp(): void
    {
        parent::setUp();

        $this->mapper = new VitalCardMapper();
    }

    public function testArrayToHealthInsurance(): void
    {
        $raw_data = [
            "donneesMutuelle" =>
                [
                    "groupe"                   => 107,
                    "identificationMutuelle"   => "62013354",
                    "garantiesEffectives"      => "OOOOOOOO",
                    "indicateurTraitement"     => "",
                    "typeServicesAssocies"     => "",
                    "servicesAssociesContrat"  => "",
                    "codeAiguillageSTS"        => "",
                    "libelle"                  => "Mutuelle n°62013354",
                    "lstPeriodeDroitsMutuelle" => [],
                ],
        ];

        $expected = HealthInsurance::hydrate(
            [
                "group"                           => 107,
                "id"                              => "62013354",
                "effective_guarantees"            => "OOOOOOOO",
                "treatment_indicator"             => "",
                "associated_services"             => "",
                "associated_services_contract"    => "",
                "referral_sts_code"               => "",
                "label"                           => "Mutuelle n°62013354",
                "health_insurance_periods_rights" => [],
            ]
        );

        $this->assertEquals($expected, $this->invokePrivateMethod($this->mapper, "arrayToHealthInsurance", $raw_data));
    }

    public function testArrayToAdditionalHealthInsurance(): void
    {
        $raw_data = [
            "groupe"                      => 1,
            "numeroComplementaireB2"      => "12",
            "numeroComplementaireEDI"     => "123",
            "numeroAdherent"              => "1234",
            "indicateurTraitement"        => "11",
            "dateDebut"                   => "20200101",
            "dateFin"                     => "20201201",
            "codeRoutage"                 => "AA87",
            "identifiantHote"             => "98765",
            "nomDomaine"                  => "domaineNom",
            "codeAiguillageSTS"           => "68C",
            "typeServices"                => "HH65",
            "servicesAssociesContrat"     => "PO9",
            "libelle"                     => "Libelle",
            "lstDonneesRUFComplementaire" => [
                [
                    "groupe"     => 7,
                    "donneesRUF" => "",
                ],
            ],
            "contexteFacturationTLA"      => null,
        ];

        $expected = AdditionalHealthInsurance::hydrate(
            [
                "group"                        => 1,
                "number_b2"                    => "12",
                "number_edi"                   => "123",
                "subscriber_number"            => "1234",
                "treatment_indicator"          => "11",
                "begin_date"                   => new DateTimeImmutable("2020-01-01"),
                "end_date"                     => new DateTimeImmutable("2020-12-01"),
                "routing_code"                 => "AA87",
                "host_id"                      => "98765",
                "domain_name"                  => "domaineNom",
                "referral_sts_code"            => "68C",
                "services_type"                => "HH65",
                "associated_services_contract" => "PO9",
                "label"                        => "Libelle",
                "rufs"                         => [
                    AdditionalHealthInsuranceRuf::hydrate(["group" => 7, "data" => ""]),
                ],
                "invoicing_tla"                => null,
            ]
        );

        $this->assertEquals(
            $expected,
            $this->invokePrivateMethod($this->mapper, "arrayToAdditionalHealthInsurance", $raw_data)
        );
    }

    public function testArrayToTla(): void
    {
        $raw_data = [
            "groupe"                  => 3,
            "dateLectureCarteVitale"  => "20201120",
            "typeContexteFacturation" => "type",
            "accidentDC"              => 2,
            "dateAccidentDC"          => "20201101",
            "dateMaternite"           => "20200316",
            "dateAT"                  => "20200912",
            "numeroAT"                => "1234567890",
            "zoneAMC"                 => "zone",
        ];

        $expected = InvoicingTla::hydrate(
            [
                "group"                     => 3,
                "vital_card_reading_date"   => new DateTimeImmutable("2020-11-20"),
                "type"                      => "type",
                "third_party_accident"      => 2,
                "third_party_accident_date" => new DateTimeImmutable("2020-11-01"),
                "maternity_date"            => new DateTimeImmutable("2020-03-16"),
                "work_accident_date"        => new DateTimeImmutable("2020-09-12"),
                "work_accident_number"      => "1234567890",
                "amc_zone"                  => "zone",
            ]
        );

        $this->assertEquals($expected, $this->invokePrivateMethod($this->mapper, "arrayToInvoicingTla", $raw_data));
    }

    public function testArrayToPeriod(): void
    {
        $raw_data = [
            "groupe"    => 1,
            "dateDebut" => "20200101",
            "dateFin"   => "20201201",
        ];

        $expected = Period::hydrate(
            [
                "group"      => 1,
                "begin_date" => new DateTimeImmutable("2020-01-01"),
                "end_date"   => new DateTimeImmutable("2020-12-01"),
            ]
        );

        $this->assertEquals($expected, $this->invokePrivateMethod($this->mapper, "arrayToPeriod", $raw_data));
    }

    public function testArrayToAmoServicePeriod(): void
    {
        $raw_data = [
            "groupe"              => 1,
            "codeServiceAMO"      => "11",
            "dateDebutServiceAMO" => "20200101",
            "dateFinServiceAMO"   => "20201201",
            "donneesRUFAMO"       => "dataRuf",
        ];

        $expected = AmoServicePeriod::hydrate(
            [
                "group"      => 1,
                "code"       => "11",
                "begin_date" => new DateTimeImmutable("2020-01-01"),
                "end_date"   => new DateTimeImmutable("2020-12-01"),
                "ruf_data"   => "dataRuf",
            ]
        );

        $this->assertEquals($expected, $this->invokePrivateMethod($this->mapper, "arrayToAmoServicePeriod", $raw_data));
    }

    public function testArrayToWorkAccident(): void
    {
        $raw_data = [
            "groupe"                   => 8,
            "organismeDestinataireAT1" => "12",
            "codeAT1"                  => "43",
            "identifiantAT1"           => "IU8",
            "organismeDestinataireAT2" => "34",
            "codeAT2"                  => "21",
            "identifiantAT2"           => "OKLM",
            "organismeDestinataireAT3" => "56",
            "codeAT3"                  => "98",
            "identifiantAT3"           => "CCBB",
        ];

        $expected = [
            WorkAccident::hydrate(
                ["number" => 1, "group" => 8, "recipient_organisation" => "12", "code" => "43", "id" => "IU8"]
            ),
            WorkAccident::hydrate(
                ["number" => 2, "group" => 8, "recipient_organisation" => "34", "code" => "21", "id" => "OKLM"]
            ),
            WorkAccident::hydrate(
                ["number" => 3, "group" => 8, "recipient_organisation" => "56", "code" => "98", "id" => "CCBB"]
            ),
        ];

        $this->assertEquals($expected, $this->invokePrivateMethod($this->mapper, "arrayToWorkAccident", $raw_data));
    }

    public function testArrayBeneficiary(): void
    {
        $raw_data = [
            "id"                   => 1234,
            "groupe"               => 1,
            "numero"               => 3,
            "nomUsuel"             => "Doe",
            "nomPatronymique"      => "Doe",
            "prenom"               => "John",
            "adresse1"             => "4 Rue Paul Vatine",
            "adresse2"             => "17000",
            "adresse3"             => "La Rochelle",
            "adresse4"             => "",
            "adresse5"             => "",
            "rangGemellaire"       => 1,
            "dateNaissance"        => "20201123",
            "NIRCertifie"          => "123456789",
            "cleNIRCertifie"       => "12",
            "dateCertificationNIR" => "20201120",
            "qualite"              => "1",
            "libelleQualite"       => "assuré",
            "codeServiceAMO"       => "11",
            "dateDebutServiceAMO"  => "20200101",
            "dateFinServiceAMO"    => "20201123",
            "donneesRUFAMO"        => "RUF AMO",
            "INSC"                 => "123456",
            "cleINSC"              => "98",
            "erreurINSC"           => "",
            "ACS"                  => "acs",
            "ACSLibelle"           => "acs label",
        ];

        $expected = Beneficiary::hydrate(
            [
                "id"                     => 1234,
                "group"                  => 1,
                "number"                 => 3,
                "patient"                => Patient::hydrate(
                    [
                        "last_name"  => "Doe",
                        "birth_name" => "Doe",
                        "birth_date" => "2020-11-23",
                        "first_name" => "John",
                        "address"    => "4 Rue Paul Vatine 17000 La Rochelle",
                        "birth_rank" => 1,
                    ]
                ),
                "certified_nir"          => "123456789",
                "certified_nir_key"      => "12",
                "nir_certification_date" => new DateTimeImmutable("2020-11-20"),
                "quality"                => "1",
                "quality_label"          => "assuré",
                "amo_service"            => AmoServicePeriod::hydrate(
                    [
                        "group"      => 1,
                        "code"       => "11",
                        "begin_date" => new DateTimeImmutable("2020-01-01"),
                        "end_date"   => new DateTimeImmutable("2020-11-23"),
                        "ruf_data"   => "RUF AMO",
                    ]
                ),
                "insc_number"            => "123456",
                "insc_key"               => "98",
                "insc_error"             => "",
                "acs"                    => "acs",
                "acs_label"              => "acs label",
                "amo_period_rights"      => [],
                "coverage_code_periods"  => [],
                "health_insurance"       => null,
            ]
        );

        $this->assertEquals($expected, $this->mapper->arrayToBeneficiary($raw_data));
    }

    public function testArrayToPatient(): void
    {
        $raw_data = [
            "nomUsuel"        => "Doe",
            "nomPatronymique" => "Doe",
            "prenom"          => "John",
            "adresse1"        => "4 Rue Paul Vatine",
            "adresse2"        => "17000",
            "adresse3"        => "La Rochelle",
            "adresse4"        => "",
            "adresse5"        => "",
            "rangGemellaire"  => 1,
            "dateNaissance"   => "20201123",
        ];

        $expected = Patient::hydrate(
            [
                "last_name"  => "Doe",
                "birth_name" => "Doe",
                "birth_date" => "2020-11-23",
                "first_name" => "John",
                "address"    => "4 Rue Paul Vatine 17000 La Rochelle",
                "birth_rank" => 1,
            ]
        );

        $this->assertEquals($expected, $this->mapper->arrayToPatient($raw_data));
    }

    public function testArrayToCoverageCodePeriod(): void
    {
        $raw_data = [
            "groupe"            => 3,
            "dateDebut"         => "20201101",
            "dateFin"           => "20201130",
            "codeALD"           => "111",
            "codeSituation"     => "567",
            "codeExoStandard"   => "exo code",
            "tauxStandard"      => "1",
            "flagAlsaceMoselle" => "1",
        ];

        $expected = CoverageCodePeriod::hydrate(
            [
                "group"                     => 3,
                "begin_date"                => new DateTimeImmutable("2020-11-01"),
                "end_date"                  => new DateTimeImmutable("2020-11-30"),
                "ald_code"                  => "111",
                "situation_code"            => "567",
                "standard_exoneration_code" => "exo code",
                "standard_rate"             => "1",
                "alsace_mozelle_flag"       => "1",
            ]
        );

        $this->assertEquals(
            [$expected],
            $this->invokePrivateMethod($this->mapper, "arrayToCoverageCodePeriods", [$raw_data])
        );
    }

    public function testArrayToVitalCard(): void
    {
        $raw_json = <<<JSON
{
    "method": {
        "output": {
            "donneescv": {
                "groupe": 101,
                "mode131": 0,
                "noBeneficiaireSelectionne": 0,
                "type": "T",
                "numeroSerie": "468963684",
                "dateFinValidite": "20201123",
                "donneesAdministrationRUF1": "ruf1 data",
                "donneesAdministrationRUF2": "ruf2 data",
                "donneesAdministration": "admin data",
                "typeRUFPorteur": "A",
                "NIR": "1421962965167",
                "cleNIR": "94",
                "codeRegime": "01",
                "caisseGestionnaire": "349",
                "centreGestionnaire": "9881",
                "codeGestion": "13",
                "donneesRUFFamille": "ruf family",
                "serviceAMOFamille": {},
                "lstDonneesBeneficiaire": [
                    {
                        "id": 1234,
                        "groupe": 1,
                        "numero": 3,
                        "nomUsuel": "Doe",
                        "nomPatronymique": "Doe",
                        "prenom": "John",
                        "adresse1": "4 Rue Paul Vatine",
                        "adresse2": "",
                        "adresse3": "",
                        "adresse4": "",
                        "adresse5": "17000 La Rochelle",
                        "rangGemellaire": 1,
                        "dateNaissance": "20201123",
                        "NIRCertifie": "123456789",
                        "cleNIRCertifie": "12",
                        "dateCertificationNIR": "20201120",
                        "qualite": "0",
                        "libelleQualite": "assure",
                        "codeServiceAMO": "11",
                        "dateDebutServiceAMO": "20200101",
                        "dateFinServiceAMO": "20201123",
                        "donneesRUFAMO": "RUF AMO",
                        "INSC": "123456",
                        "cleINSC": "98",
                        "erreurINSC": "",
                        "ACS": "acs",
                        "ACSLibelle": "acs label"
                    }
                ],
                "libelleRegime": "Régime général",
                "libelleCaisse": "Caisse de test",
                "libelleGestion": "Invalides de guerre"
            }
        },
        "lstException": [],
        "cancel": false,
        "asynchronous": false
    }
}
JSON;
        $json     = json_decode(utf8_encode($raw_json), true);
        $raw_data = $json["method"]["output"];

        $insured = Insured::hydrate(
            [
                "nir"             => "1421962965167",
                "nir_key"         => "94",
                "last_name"       => "Doe",
                "birth_name"      => "Doe",
                "first_name"      => "John",
                "regime_code"     => "01",
                "managing_fund"   => "349",
                "managing_center" => "9881",
                "managing_code"   => "13",
                "address"         => "4 Rue Paul Vatine",
                "zip_code"        => "17000",
                "city"            => "La Rochelle",
            ]
        );

        $beneficiary = Beneficiary::hydrate(
            [
                "id"                     => 1234,
                "group"                  => 1,
                "number"                 => 3,
                "patient"                => Patient::hydrate(
                    [
                        "last_name"  => "Doe",
                        "birth_name" => "Doe",
                        "birth_date" => "2020-11-23",
                        "first_name" => "John",
                        "address"    => "4 Rue Paul Vatine",
                        "zip_code"   => "17000",
                        "city"       => "La Rochelle",
                        "birth_rank" => 1,
                    ]
                ),
                "certified_nir"          => "123456789",
                "certified_nir_key"      => "12",
                "nir_certification_date" => new DateTimeImmutable("2020-11-20"),
                "quality"                => "0",
                "quality_label"          => "assure",
                "amo_service"            => AmoServicePeriod::hydrate(
                    [
                        "group"      => 1,
                        "code"       => "11",
                        "begin_date" => new DateTimeImmutable("2020-01-01"),
                        "end_date"   => new DateTimeImmutable("2020-11-23"),
                        "ruf_data"   => "RUF AMO",
                    ]
                ),
                "insc_number"            => "123456",
                "insc_key"               => "98",
                "insc_error"             => "",
                "acs"                    => "acs",
                "acs_label"              => "acs label",
                "amo_period_rights"      => [],
                "coverage_code_periods"  => [],
                "health_insurance"       => null,
                'insured'                => $insured,
            ]
        );

        $expected = VitalCard::hydrate(
            [
                "group"                       => 101,
                "mode131"                     => 0,
                "selected_beneficiary_number" => 0,
                "type"                        => "T",
                "serial_number"               => "468963684",
                "expiration_date"             => new DateTimeImmutable("2020-11-23"),
                "ruf1_administration_data"    => "ruf1 data",
                "ruf2_administration_data"    => "ruf2 data",
                "administration_data"         => "admin data",
                "insured"                     => $insured,
                "ruf_bearer_type"             => "A",
                "ruf_family_data"             => "ruf family",
                "regime_label"                => utf8_encode("Régime général"),
                "fund_label"                  => "Caisse de test",
                "managing_label"              => "Invalides de guerre",
                "amo_family_service"          => [],
                "work_accident_data"          => [],
                "beneficiaries"               => [$beneficiary],
            ]
        );

        $this->assertEquals($expected, $this->mapper->arrayToVitalCard($raw_data));
    }
}
