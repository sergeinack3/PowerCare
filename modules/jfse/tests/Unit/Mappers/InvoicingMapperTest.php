<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Mappers;

use DateTime;
use DateTimeImmutable;
use Ox\Mediboard\Jfse\Api\Question;
use Ox\Mediboard\Jfse\Domain\InsuranceType\FmfInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\Insurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\InsuranceType;
use Ox\Mediboard\Jfse\Domain\InsuranceType\MaternityInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\MedicalInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\WorkAccidentInsurance;
use Ox\Mediboard\Jfse\Domain\Invoicing\Acs;
use Ox\Mediboard\Jfse\Domain\Invoicing\AcsContractTypeEnum;
use Ox\Mediboard\Jfse\Domain\Invoicing\AcsManagementModeEnum;
use Ox\Mediboard\Jfse\Domain\Invoicing\CommonLawAccident;
use Ox\Mediboard\Jfse\Domain\Invoicing\ComplementaryHealthInsurance;
use Ox\Mediboard\Jfse\Domain\Invoicing\Invoice;
use Ox\Mediboard\Jfse\Domain\Invoicing\InvoiceUserInterface;
use Ox\Mediboard\Jfse\Domain\Invoicing\Prescription;
use Ox\Mediboard\Jfse\Domain\Invoicing\RuleForcing;
use Ox\Mediboard\Jfse\Domain\Invoicing\SecuringModeEnum;
use Ox\Mediboard\Jfse\Domain\MedicalAct\MedicalAct;
use Ox\Mediboard\Jfse\Domain\PrescribingPhysician\Physician;
use Ox\Mediboard\Jfse\Domain\Vital\Beneficiary;
use Ox\Mediboard\Jfse\Domain\Vital\Insured;
use Ox\Mediboard\Jfse\Domain\Vital\Patient;
use Ox\Mediboard\Jfse\Mappers\InvoicingMapper;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class InvoicingMapperTest extends UnitTestJfse
{
    /**
     * @dataProvider makeInitFactureArrayFromInvoiceProvider
     *
     * @param Invoice $invoice
     * @param array   $expected
     */
    public function testMakeInitFactureArrayFromInvoice(Invoice $invoice, array $expected): void
    {
        $mapper = new InvoicingMapper();
        $this->assertEquals(
            $expected,
            $mapper->makeInitFactureArrayFromInvoice($invoice)
        );
    }

    public function makeInitFactureArrayFromInvoiceProvider(): array
    {
        // Invoice 1 - Basic Invoice
        $invoice_1 = Invoice::hydrate([
            "securing"           => SecuringModeEnum::SECURED(),
            'automatic_deletion' => true
        ]);

        $expected_1       = [
            "FSE" => [
                "facture" => [
                    "securisation"               => SecuringModeEnum::SECURED()->getValue(),
                    "suppressionFactureAutorise" => 1,
                    'modePapier'                 => 0,
                    'alsaceMoselle'              => 0,
                    'differerEnvoi'              => 0,
                    'checkVitaleCard'            => 0,
                    'desactivationSTS'           => 0,
                    'affichageBandeauBenef'      => 0,
                ],
            ],
        ];
        $insurance        = Insurance::hydrate([
            'selected_insurance_type' => MedicalInsurance::CODE,
            'medical_insurance'       => MedicalInsurance::hydrate([
                "code_exoneration_disease" => 0,
            ]),
        ]);
        $invoice_2 = Invoice::hydrate([
            "securing"        => SecuringModeEnum::UNSECURED(),
            "dateElaboration" => "20200101",
            "insurance"       => $insurance,
            'automatic_deletion' => false
        ]);
        $expected_2       = [
            "FSE" => [
                "facture" => [
                    "securisation"               => SecuringModeEnum::UNSECURED()->getValue(),
                    "suppressionFactureAutorise" => 0,
                    "natureAssurance"            => [
                        "maladie" => [
                            "codeExoneration" => 0,
                        ],
                    ],
                    'modePapier'                 => 0,
                    'alsaceMoselle'              => 0,
                    'differerEnvoi'              => 0,
                    'checkVitaleCard'            => 0,
                    'desactivationSTS'           => 0,
                    'affichageBandeauBenef'      => 0,
                ],
            ],
        ];

        // Invoice 2 Bis - with InsuranceType node in Invoice (WorkAccidentInsurance)
        $insurance_2         = Insurance::hydrate([
            'selected_insurance_type' => WorkAccidentInsurance::CODE,
            'work_accident_insurance' => WorkAccidentInsurance::hydrate([
                "date"                  => new DateTimeImmutable('2020-01-01'),
                "has_physical_document" => true,
                "number"                => '123456789',
            ])
        ]);
        $invoice_2bis = Invoice::hydrate([
            "securing"        => SecuringModeEnum::UNSECURED(),
            "dateElaboration" => new DateTimeImmutable('2020-01-01'),
            "insurance"       => $insurance_2,
            'automatic_deletion' => false
        ]);
        $expected_2bis       = [
            "FSE" => [
                "facture" => [
                    "securisation"               => SecuringModeEnum::UNSECURED()->getValue(),
                    "suppressionFactureAutorise" => 0,
                    "natureAssurance"            => [
                        "AT" => [
                            "date"             => '20200101',
                            "presenceFeuillet" => 1,
                            "numero"           => '123456789',
                            'caisseIdentiqueAMO'    => 0,
                            'priseEnChargeArmateur' => 0,
                        ],
                    ],
                    'modePapier'                 => 0,
                    'alsaceMoselle'              => 0,
                    'differerEnvoi'              => 0,
                    'checkVitaleCard'            => 0,
                    'desactivationSTS'           => 0,
                    'affichageBandeauBenef'      => 0,
                ],
            ],
        ];

        // Invoice 2 Ter - with InsuranceType node in Invoice (FmfInsurance)
        $insurance_3         = Insurance::hydrate([
            'selected_insurance_type' => FmfInsurance::CODE,
            'fmf_insurance'           => FmfInsurance::hydrate([
                "supported_fmf_existence" => 1,
                "supported_fmf_expense"   => 100.0,
            ]),
        ]);
        $invoice_2ter = Invoice::hydrate([
            "securing"        => SecuringModeEnum::UNSECURED(),
            "dateElaboration" => "20200101",
            "insurance"       => $insurance_3,
            'automatic_deletion' => false
        ]);
        $expected_2ter       = [
            "FSE" => [
                "facture" => [
                    "securisation"               => SecuringModeEnum::UNSECURED()->getValue(),
                    "suppressionFactureAutorise" => 0,
                    "natureAssurance"            => [
                        "SMG" => [
                            "existencePEC" => 1,
                            "montantPEC"   => 100.0,
                        ],
                    ],
                    'modePapier'                 => 0,
                    'alsaceMoselle'              => 0,
                    'differerEnvoi'              => 0,
                    'checkVitaleCard'            => 0,
                    'desactivationSTS'           => 0,
                    'affichageBandeauBenef'      => 0,
                ],
            ],
        ];

        // Invoice 2 Quater - with InsuranceType node in Invoice (MaternityInsurance)

        $insurance_4            = Insurance::hydrate([
            'selected_insurance_type' => MaternityInsurance::CODE,
            'maternity_insurance'     => MaternityInsurance::hydrate([
                "date"              => new DateTimeImmutable('2020-01-01'),
                "force_exoneration" => 1,
            ]),
        ]);
        $invoice_2quater = Invoice::hydrate([
            "securing"        => SecuringModeEnum::UNSECURED(),
            "dateElaboration" => new DateTimeImmutable('2020-01-01'),
            "insurance"       => $insurance_4,
            'automatic_deletion' => false
        ]);
        $expected_2quater       = [
            "FSE" => [
                "facture" => [
                    "securisation"               => SecuringModeEnum::UNSECURED()->getValue(),
                    "suppressionFactureAutorise" => 0,
                    "natureAssurance"            => [
                        "maternite" => [
                            "date"    => '20200101',
                            "forcage" => 1,
                        ],
                    ],
                    'modePapier'                 => 0,
                    'alsaceMoselle'              => 0,
                    'differerEnvoi'              => 0,
                    'checkVitaleCard'            => 0,
                    'desactivationSTS'           => 0,
                    'affichageBandeauBenef'      => 0,
                ],
            ],
        ];

        // Invoice 3 - with Beneficiary node
        $patient_3     = Patient::hydrate([
            "birth_rank" => 1,
            "birth_date" => "19700101",
        ]);
        $beneficiary_3 = Beneficiary::hydrate([
            "certified_nir"     => "1461935084001",
            "certified_nir_key" => "63",
            "quality"           => "0",
            "patient"           => $patient_3,
            'insured'           => Insured::hydrate([
                'nir'     => "1461935084001",
                'nir_key' => "63",
            ]),
            "integrator_id"     => "1234",
        ]);

        $invoice_3 = Invoice::hydrate([
            "securing"    => SecuringModeEnum::SECURED(),
            "beneficiary" => $beneficiary_3,
            'automatic_deletion' => false
        ]);
        $expected_3       = [
            "FSE" => [
                "facture"      => [
                    "securisation"               => SecuringModeEnum::SECURED()->getValue(),
                    "suppressionFactureAutorise" => 0,
                    'modePapier'                 => 0,
                    'alsaceMoselle'              => 0,
                    'differerEnvoi'              => 0,
                    'checkVitaleCard'            => 0,
                    'desactivationSTS'           => 0,
                    'affichageBandeauBenef'      => 0,
                ],
                "beneficiaire" => [
                    "immatriculation" => '146193508400163',
                    "qualite"         => '0',
                    "idExterne"       => "1234",
                    "dateNaissance"   => "19700101",
                    "rangGemellaire"  => '1',
                    "nirCertifie"     => '146193508400163',
                ],
            ],
        ];

        // Invoice 4 - with Insured node
        $insured_4 = Insured::hydrate([
            "nir"        => "1461935084001",
            "nir_key"    => "63",
            "last_name"  => "Doe",
            "first_name" => "John",
            "birth_name" => "",
        ]);

        $invoice_4 = Invoice::hydrate([
            "securing" => SecuringModeEnum::SECURED(),
            "insured"  => $insured_4,
            'automatic_deletion' => false
        ]);
        $expected_4       = [
            "FSE" => [
                "facture" => [
                    "securisation"               => SecuringModeEnum::SECURED()->getValue(),
                    "suppressionFactureAutorise" => 0,
                    'modePapier'                 => 0,
                    'alsaceMoselle'              => 0,
                    'differerEnvoi'              => 0,
                    'checkVitaleCard'            => 0,
                    'desactivationSTS'           => 0,
                    'affichageBandeauBenef'      => 0,
                ],
                "assure"  => [
                    "nom"             => "Doe",
                    "prenom"          => "John",
                    "immatriculation" => "146193508400163",
                    "nomPatronymique" => "",
                ],
            ],
        ];

        // Invoice 5 ComplementaryHealthInsurance
        $complementary    = ComplementaryHealthInsurance::hydrate([
            "third_party_amo" => 0,
            "third_party_amc" => 1,
        ]);
        $invoice_5 = Invoice::hydrate([
            "securing"                       => SecuringModeEnum::SECURED(),
            "complementary_health_insurance" => $complementary,
            'automatic_deletion' => false
        ]);
        $expected_5       = [
            "FSE" => [
                "facture"                 => [
                    "securisation"               => SecuringModeEnum::SECURED()->getValue(),
                    "suppressionFactureAutorise" => 0,
                    'modePapier'                 => 0,
                    'alsaceMoselle'              => 0,
                    'differerEnvoi'              => 0,
                    'checkVitaleCard'            => 0,
                    'desactivationSTS'           => 0,
                    'affichageBandeauBenef'      => 0,
                ],
                "organismeComplementaire" => [
                    "tiersPayantAMC" => 1,
                    "tiersPayantAMO" => 0,
                    'victimeAttentat' => 0,
                    'tiersPayantSNCF' => 0,
                ],
            ],
        ];

        // Invoice 6 - Prescriber
        $physician        = Physician::hydrate([
            "id"               => 1603448711301941782,
            "first_name"       => "JOSEPH",
            "last_name"        => "MARTELL",
            "invoicing_number" => "123456789",
            "speciality"       => 0,
            "type"             => 0,
            "national_id"      => "123456789",
            "structure_id"     => "987654",
        ]);
        $prescription     = Prescription::hydrate([
            "prescriber" => $physician,
            "date"       => new DateTimeImmutable('2020-01-01'),
        ]);
        $invoice_6 = Invoice::hydrate([
            "securing"     => SecuringModeEnum::SECURED(),
            "prescription" => $prescription,
            'automatic_deletion' => false
        ]);
        $expected_6       = [
            "FSE" => [
                "facture"      => [
                    "securisation"               => SecuringModeEnum::SECURED()->getValue(),
                    "suppressionFactureAutorise" => 0,
                    'modePapier'                 => 0,
                    'alsaceMoselle'              => 0,
                    'differerEnvoi'              => 0,
                    'checkVitaleCard'            => 0,
                    'desactivationSTS'           => 0,
                    'affichageBandeauBenef'      => 0,
                ],
                "prescripteur" => [
                    "datePrescription" => (new DateTimeImmutable('2020-01-01'))->format('Ymd'),
                    "medecin"          => [
                        "nom"           => "MARTELL",
                        "prenom"        => "JOSEPH",
                        "noFacturation" => "123456789",
                        "specialite"    => 0,
                        "type"          => 0,
                        "rpps"          => 123456789,
                        "noStructure"   => 987654,
                    ],
                ],
            ],
        ];

        // Invoice 7 - InvoiceUserInterface
        $invoice_interface = InvoiceUserInterface::hydrate([
            "acts_lock"                        => true,
            "proof_amo"                        => false,
            "alsace_moselle"                   => false,
            "beneficiary"                      => false,
            "prescriber"                       => false,
            "ame"                              => false,
            "maternity_exoneration"            => false,
            "sncf"                             => false,
            "amc_third_party_payment"          => false,
            "pharmacy"                         => false,
            "care_path"                        => false,
            "ccam_acts"                        => false,
            "medical_acts"                     => false,
            "cnda_mode"                        => false,
            "amendment_27_consultation_help"   => false,
            "amendment_27_referring_physician" => false,
            "amendment_27_enforceable_tariff"  => false,
        ]);

        $invoice_7 = Invoice::hydrate([
                "securing"       => SecuringModeEnum::SECURED(),
                "user_interface" => $invoice_interface,
            'automatic_deletion' => false
        ]);

        $expected_7 = [
            "FSE" => [
                "facture" => [
                    "securisation"               => SecuringModeEnum::SECURED()->getValue(),
                    "suppressionFactureAutorise" => 0,
                    "blocageActes"               => 1,
                    'modePapier'                 => 0,
                    'alsaceMoselle'              => 0,
                    'differerEnvoi'              => 0,
                    'checkVitaleCard'            => 0,
                    'desactivationSTS'           => 0,
                    'affichageBandeauBenef'      => 0,
                ],
            ],
        ];

        return [
            [$invoice_1, $expected_1],
            [$invoice_2, $expected_2],
            [$invoice_2bis, $expected_2bis],
            [$invoice_2ter, $expected_2ter],
            [$invoice_2quater, $expected_2quater],
            [$invoice_3, $expected_3],
            [$invoice_4, $expected_4],
            [$invoice_5, $expected_5],
            [$invoice_6, $expected_6],
            [$invoice_7, $expected_7],
        ];
    }

    /**
     * @dataProvider makeGetConventionsArrayFromConventionProvider
     *
     * @param array $data
     * @param array $expected
     */
    public function testMakeGetConventionsArrayFromConvention(array $data, array $expected): void
    {
        $mapper = new InvoicingMapper();
        $this->assertEquals(
            $expected,
            $mapper->makeGetConventionsArrayFromConvention(
                $data["invoice_id"],
                $data["convention_data"]
            )
        );
    }

    public function makeGetConventionsArrayFromConventionProvider(): array
    {
        $data_1 = [
            "invoice_id"      => "11111111",
            "convention_data" => [],
        ];

        $data_2 = [
            "invoice_id"      => "22222222",
            "convention_data" => [
                "AMO" => [
                    "codeRegime" => 123456789,
                    "codeCaisse" => 987654321,
                    "codeCentre" => 147258369,
                ],
            ],
        ];
        $data_3 = [
            "invoice_id"      => "33333333",
            "convention_data" => [
                "AMO" => [
                    "codeRegime" => 123456789,
                    "codeCaisse" => 987654321,
                    "codeCentre" => 147258369,
                ],
                "AMC" => [
                    "numeroComplementaireB2" => "14",
                    "critereSecondaire"      => "14????????",
                    "typeConvention"         => "RO",
                ],
            ],
        ];
        $data_4 = [
            "invoice_id"      => "44444444",
            "convention_data" => [
                "AMO"      => [
                    "codeRegime" => 123456789,
                    "codeCaisse" => 987654321,
                    "codeCentre" => 147258369,
                ],
                "mutuelle" => [
                    "identification"   => 123456789,
                    "servicesAssocies" => "",
                ],
            ],
        ];

        $expected_1 = ["idFacture" => "11111111"];
        $expected_2 = [
            "idFacture"      => "22222222",
            "getConventions" => [
                "AMO" => [
                    "codeRegime" => "123456789",
                    "codeCaisse" => "987654321",
                    "codeCentre" => "147258369",
                ],
            ],
        ];
        $expected_3 = [
            "idFacture"      => "33333333",
            "getConventions" => [
                "AMO" => [
                    "codeRegime" => "123456789",
                    "codeCaisse" => "987654321",
                    "codeCentre" => "147258369",
                ],
                "AMC" => [
                    "numeroComplementaireB2" => "14",
                    "critereSecondaire"      => "14????????",
                    "typeConvention"         => "RO",
                ],
            ],
        ];
        $expected_4 = [
            "idFacture"      => "44444444",
            "getConventions" => [
                "AMO"      => [
                    "codeRegime" => "123456789",
                    "codeCaisse" => "987654321",
                    "codeCentre" => "147258369",
                ],
                "mutuelle" => [
                    "identification"   => 123456789,
                    "servicesAssocies" => "",
                ],
            ],
        ];

        return [
            [$data_1, $expected_1],
            [$data_2, $expected_2],
            [$data_3, $expected_3],
            [$data_4, $expected_4],
        ];
    }

    /**
     * @dataProvider makeGetListeActesArrayFromDataProvider
     *
     * @param array $data
     * @param array $expected
     */
    public function testMakeGetListeActesArrayFromData(array $data, array $expected): void
    {
        $mapper = new InvoicingMapper();
        $this->assertEquals(
            $expected,
            $mapper->makeGetListeActesArrayFromData(
                $data["invoice_id"],
                $data["data"]
            )
        );
    }

    public function makeGetListeActesArrayFromDataProvider(): array
    {
        $data_1 = [
            "invoice_id" => "11111111",
            "data"       => [
                "filtre"           => "TOTO",
                "dateExecution"    => "2020-01-01",
                "typeActe"         => 0,
                "filtreSpecialite" => 0,
            ],
        ];

        $expected_1 = [
            "idFacture"     => "11111111",
            "getListeActes" => [
                "filtre"           => "TOTO",
                "dateExecution"    => "2020-01-01",
                "typeActe"         => 0,
                "filtreSpecialite" => 0,
            ],
        ];

        return [
            [$data_1, $expected_1],
        ];
    }

    /**
     * @dataProvider makeRemoveCotationArrayFromLstCotationsProvider
     *
     * @param array $data
     * @param array $expected
     */
    public function testMakeRemoveCotationArrayFromLstCotations(array $data, array $expected): void
    {
        $mapper = new InvoicingMapper();
        $this->assertEquals(
            $expected,
            $mapper->makeRemoveCotationArrayFromLstCotations(
                $data["lst_cotations"],
                $data["invoice_id"]
            )
        );
    }

    public function makeRemoveCotationArrayFromLstCotationsProvider(): array
    {
        $data_1 = [
            "lst_cotations" => [
                MedicalAct::hydrate(["id" => "123456"]),
                MedicalAct::hydrate(["id" => "456789"]),
            ],
            "invoice_id"    => "11111111",
        ];
        $data_2 = [
            "lst_cotations" => [
                MedicalAct::hydrate(["id" => "987654"]),
                MedicalAct::hydrate(["id" => "654321"]),
            ],
            "invoice_id"    => "22222222",
        ];

        $expected_1 = [
            "idFacture"    => "11111111",
            "lstCotations" => [
                "0" => ["id" => "123456"],
                "1" => ["id" => "456789"],
            ],
        ];
        $expected_2 = [
            "idFacture"    => "22222222",
            "lstCotations" => [
                "0" => ["id" => "987654"],
                "1" => ["id" => "654321"],
            ],
        ];

        return [
            [$data_1, $expected_1],
            [$data_2, $expected_2],
        ];
    }

    /**
     * @dataProvider makeSetAccidentDcArrayFromCommonLawAccidentProvider
     *
     * @param array $data
     * @param array $expected
     */
    public function testMakeSetAccidentDcArrayFromCommonLawAccident(array $data, array $expected): void
    {
        $mapper = new InvoicingMapper();
        $this->assertEquals(
            $expected,
            $mapper->makeSetAccidentDcArrayFromCommonLawAccident(
                $data["common_law_accident"],
                $data["invoice_id"]
            )
        );
    }

    public function makeSetAccidentDcArrayFromCommonLawAccidentProvider(): array
    {
        $data_1 = [
            "invoice_id"          => "11111111",
            "common_law_accident" => CommonLawAccident::hydrate(["common_law_accident" => 0]),
        ];
        $data_2 = [
            "invoice_id"          => "22222222",
            "common_law_accident" => CommonLawAccident::hydrate(
                ["common_law_accident" => 1, "date" => DateTime::createFromFormat('Y-m-d', '2020-01-01')]
            ),
        ];

        $expected_1 = [
            "idFacture"  => "11111111",
            "accidentDC" => [
                "accidentDC" => 0,
            ],
        ];
        $expected_2 = [
            "idFacture"  => "22222222",
            "accidentDC" => [
                "accidentDC"   => 1,
                "dateAccident" => '20200101',
            ],
        ];

        return [
            [$data_1, $expected_1],
            [$data_2, $expected_2],
        ];
    }

    /**
     * @dataProvider makeSetReponseQuestionsArrayFromDataProvider
     *
     * @param $data
     * @param $expected
     */
    public function testMakeSetReponseQuestionsArrayFromData(array $data, array $expected): void
    {
        $mapper = new InvoicingMapper();
        $this->assertEquals(
            $expected,
            $mapper->makeSetReponseQuestionsArrayFromData(
                $data["invoice_id"],
                $data["data"]
            )
        );
    }

    public function makeSetReponseQuestionsArrayFromDataProvider(): array
    {
        $question_1 = Question::map(
            [
                "id"           => "123",
                "genre"        => 0,
                "libelle"      => "",
                "type"         => 0,
                "lstResponses" => [
                    "0" => "OUI",
                    "1" => "NON",
                ],
                "reponse"      => 0,
            ]
        );

        $question_2 = Question::map(
            [
                "id"           => "456",
                "genre"        => 0,
                "libelle"      => "",
                "type"         => 0,
                "lstResponses" => [
                    "0" => "OUI",
                    "1" => "NON",
                ],
                "reponse"      => 1,
            ]
        );

        $question_3 = Question::map(
            [
                "id"           => "789",
                "genre"        => 0,
                "libelle"      => "",
                "type"         => 0,
                "lstResponses" => [
                    "0" => "OUI",
                    "1" => "NON",
                ],
                "reponse"      => 1,
            ]
        );

        $data_1 = ["invoice_id" => "11111111", "data" => [$question_1, $question_2]];
        $data_2 = ["invoice_id" => "22222222", "data" => [$question_1, $question_3]];
        $data_3 = ["invoice_id" => "33333333", "data" => [$question_2]];

        $expected_1 = [
            "idFacture"           => "11111111",
            "lstReponseQuestions" => [
                ["id" => "123", "reponse" => 0],
                ["id" => "456", "reponse" => 1],
            ],
        ];
        $expected_2 = [
            "idFacture"           => "22222222",
            "lstReponseQuestions" => [
                ["id" => "123", "reponse" => 0],
                ["id" => "789", "reponse" => 1],
            ],
        ];
        $expected_3 = [
            "idFacture"           => "33333333",
            "lstReponseQuestions" => [
                ["id" => "456", "reponse" => 1],
            ],
        ];

        return [
            [$data_1, $expected_1],
            [$data_2, $expected_2],
            [$data_3, $expected_3],
        ];
    }

    /**
     * @dataProvider makeSetOrganismeComplementaireFromEntityProvider
     *
     * @param array $data
     * @param array $expected
     */
    public function testMakeSetOrganismeComplementaireFromEntity(array $data, array $expected): void
    {
        $mapper = new InvoicingMapper();
        $this->assertEquals(
            $expected,
            $mapper->makeSetOrganismeComplementaireFromEntity(
                $data["invoice_id"],
                $data["complementary_health_insurance"]
            )
        );
    }

    public function makeSetOrganismeComplementaireFromEntityProvider(): array
    {
        $data_1 = [
            "invoice_id"                     => "11111111",
            "complementary_health_insurance" => ComplementaryHealthInsurance::hydrate(["third_party_amc" => 1]),
        ];

        $expected_1 = [
            "idFacture"               => "11111111",
            "organismeComplementaire" => [
                "tiersPayantAMC" => 1,
                'tiersPayantAMO' => 0,
                'victimeAttentat' => 0,
                'tiersPayantSNCF' => 0
            ],
        ];

        $data_2 = [
            "invoice_id"                     => "22222222",
            "complementary_health_insurance" => ComplementaryHealthInsurance::hydrate(
                [
                    "third_party_amc"  => 0,
                    "third_party_amo"  => 1,
                    "attack_victim"    => 0,
                    "third_party_sncf" => 0,
                ]
            ),
        ];

        $expected_2 = [
            "idFacture"               => "22222222",
            "organismeComplementaire" => [
                "tiersPayantAMC"  => 0,
                "tiersPayantAMO"  => 1,
                "victimeAttentat" => 0,
                "tiersPayantSNCF" => 0,
            ],
        ];

        return [
            [$data_1, $expected_1],
            [$data_2, $expected_2],
        ];
    }

    /**
     * @dataProvider makeAssistantAcsArrayFromEntityProvider
     *
     * @param Acs   $acs
     * @param array $expected
     */
    public function testMakeAssistantAcsArrayFromEntity(Acs $acs, array $expected): void
    {
        $mapper = new InvoicingMapper();
        $this->assertEquals(
            $expected,
            $mapper->makeAssistantAcsArrayFromEntity($acs)
        );
    }

    public function makeAssistantAcsArrayFromEntityProvider(): array
    {
        $acs_1 = Acs::hydrate(
            [
                "management_mode" => AcsManagementModeEnum::NO_THIRD_PARTY_AMC(),
                "contract_type"   => AcsContractTypeEnum::THIRD_PARTY_AMO(),
            ]
        );
        $acs_2 = Acs::hydrate(
            [
                "management_mode" => AcsManagementModeEnum::COORDINATED_THIRD_PARTY(),
                "contract_type"   => AcsContractTypeEnum::ACS_A_CONTRACT(),
            ]
        );
        $acs_3 = Acs::hydrate(
            [
                "management_mode" => AcsManagementModeEnum::UNIQUE_MANAGEMENT(),
                "contract_type"   => AcsContractTypeEnum::ACS_B_CONTRACT(),
            ]
        );
        $acs_4 = Acs::hydrate(
            [
                "management_mode" => AcsManagementModeEnum::SEPARATED_MANAGEMENT(),
                "contract_type"   => AcsContractTypeEnum::ACS_C_CONTRACT(),
            ]
        );

        $expected_1 = [
            "assistantACS" => [
                "modeGestion" => AcsManagementModeEnum::NO_THIRD_PARTY_AMC(),
                "typeContrat" => AcsContractTypeEnum::THIRD_PARTY_AMO(),
            ],
        ];
        $expected_2 = [
            "assistantACS" => [
                "modeGestion" => AcsManagementModeEnum::COORDINATED_THIRD_PARTY(),
                "typeContrat" => AcsContractTypeEnum::ACS_A_CONTRACT(),
            ],
        ];
        $expected_3 = [
            "assistantACS" => [
                "modeGestion" => AcsManagementModeEnum::UNIQUE_MANAGEMENT(),
                "typeContrat" => AcsContractTypeEnum::ACS_B_CONTRACT(),
            ],
        ];
        $expected_4 = [
            "assistantACS" => [
                "modeGestion" => AcsManagementModeEnum::SEPARATED_MANAGEMENT(),
                "typeContrat" => AcsContractTypeEnum::ACS_C_CONTRACT(),
            ],
        ];

        return [
            [$acs_1, $expected_1],
            [$acs_2, $expected_2],
            [$acs_3, $expected_3],
            [$acs_4, $expected_4],
        ];
    }

    /**
     * @dataProvider makeSetForceReglesFromEntityProvider
     *
     * @param array $data
     * @param array $expected
     */
    public function testMakeSetForceReglesFromEntity(array $data, array $expected): void
    {
        $mapper = new InvoicingMapper();
        $this->assertEquals(
            $expected,
            $mapper->makeSetForceReglesFromEntity($data["invoice_id"], $data["rule_forcing"])
        );
    }

    public function makeSetForceReglesFromEntityProvider(): array
    {
        $data_1 = [
            "invoice_id"   => "11111111",
            "rule_forcing" => RuleForcing::hydrate(
                ["serial_id" => "123456789", "forcing_type" => RuleForcing::STANDARD_FORCING]
            ),
        ];
        $data_2 = [
            "invoice_id"   => "22222222",
            "rule_forcing" => RuleForcing::hydrate(
                ["serial_id" => "987654321", "forcing_type" => RuleForcing::COMPLETE_CONTROL_FORCING]
            ),
        ];

        $expected_1 = [
            "idFacture"    => "11111111",
            "forcageRegle" => [
                "regleSerialId" => "123456789",
            ],
        ];
        $expected_2 = [
            "idFacture"    => "22222222",
            "forcageRegle" => [
                "regleSerialId" => "987654321",
            ],
        ];

        return [
            [$data_1, $expected_1],
            [$data_2, $expected_2],
        ];
    }
}
