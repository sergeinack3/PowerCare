<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Domain\InsuranceType;

use DateTimeImmutable;
use Exception;
use Ox\Core\Cache;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\ApiClients\InsuranceTypeClient;
use Ox\Mediboard\Jfse\ApiClients\InvoicingClient;
use Ox\Mediboard\Jfse\Domain\InsuranceType\FmfInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\InsuranceType;
use Ox\Mediboard\Jfse\Domain\InsuranceType\InsuranceTypeService;
use Ox\Mediboard\Jfse\Domain\InsuranceType\MaternityInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\MedicalInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\WorkAccidentInsurance;
use Ox\Mediboard\Jfse\Domain\Invoicing\InvoicingService;
use Ox\Mediboard\Jfse\Exceptions\Insurance\InsuranceException;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class InsuranceTypeServiceTest
 *
 * @package Ox\Mediboard\Jfse\Domain\InsuranceType
 */
class InsuranceTypeServiceTest extends UnitTestJfse
{
    /** @var MockObject */
    private $insurance_client;

    public function setUp(): void
    {
        parent::setUp();

        $this->insurance_client = $this->getMockBuilder(InsuranceTypeClient::class)
            ->setMethods(['getAllTypes', 'save'])
            ->getMock();
        $raw_types_return       = <<<JSON
{
  "method": {
    "output": {
      "lst": [
        {"code": 0, "libelle": "Maladie"},
        {"code": 1, "libelle": "Maternité"},
        {"code": 2, "libelle": "Accident du travail"},
        {"code": 3, "libelle": "Dispositif de prévention"},
        {"code": 4, "libelle": "Soins médicaux gratuits"}
      ]
    }
  }
}
JSON;

        $this->insurance_client->method('getAllTypes')->willReturn(
            Response::forge('', json_decode(utf8_encode($raw_types_return), true))
        );
    }

    /**
     * Get all insurances and return a list of objects
     */
    public function testGetAllInsuranceTypes(): void
    {
        $expected = [
            InsuranceType::hydrate(['code' => 0, 'label' => 'Maladie']),
            InsuranceType::hydrate(['code' => 1, 'label' => 'Maternité']),
            InsuranceType::hydrate(['code' => 2, 'label' => 'Accident du travail']),
            InsuranceType::hydrate(['code' => 3, 'label' => 'Dispositif de prévention']),
            InsuranceType::hydrate(['code' => 4, 'label' => 'Soins médicaux gratuits']),
        ];

        $cache = new Cache('', '', Cache::NONE);

        $service = new InsuranceTypeService($this->insurance_client, $cache);
        $actual  = $service->getAllInsuranceTypes();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider saveProvider
     *
     * @param array $content
     *
     * @throws Exception
     */
    public function testSave(array $content, string $json_response): void
    {
        $response = Response::forge('', json_decode(utf8_encode($json_response), true));
        $this->insurance_client->method('save')->willReturn($response);

        $cache = new Cache('', '', Cache::NONE);

        $invoicing_client = $this->getMockBuilder(InvoicingClient::class)
            ->setMethods(['setAccidentDC'])
            ->getMock();
        $invoicing_client->method('setAccidentDC')->willReturn($response);
        $invoicing_service = new InvoicingService($invoicing_client);

        $service = new InsuranceTypeService($this->insurance_client, $cache, $invoicing_service);

        $this->assertTrue($service->save($content));
    }

    /**
     * @return array
     */
    public function saveProvider(): array
    {
        $fse_response = <<<JSON
{
    "method": {
        "name": "FDS-validerFacture",
        "service": true,
        "parameters": {
            "idFacture": "1615891189775351955"
        },
        "output": {
            "facture": {
                "keyFacture": "1615891189775351955",
                "securisation": 3,
                "alsaceMoselle": 0,
                "dateElaboration": "20210316",
                "idIntegrateur": "112",
                "differerEnvoi": 0,
                "anonymiser": 0,
                "modePapier": 0,
                "modeFSP": -1,
                "totalMontants": 23,
                "totalAMO": 16.1,
                "totalAssure": 23,
                "totalAMC": 0,
                "numeroFacture": "1",
                "duAMO": 0,
                "duAMC": 0,
                "acteIsoleSerie": 0,
                "forcageAMO": false,
                "forcageAMC": false,
                "valeurPlafondCMU": 0,
                "etatDroitsAMO": "Les droits AMO sont ouverts",
                "checkVitaleCard": true,
                "tauxGlobal": -1,
                "corrigerOuRecycler": 0,
                "idScorCerfa": "0",
                "idTicketVitale": "0",
                "parcoursSoins": {
                    "indicateur": "T",
                    "declaration": 1,
                    "statut": 1,
                    "medecin": {
                        "nom": "",
                        "prenom": "",
                        "noIdentification": "",
                        "dateInstallation": "",
                        "dateInstallationZoneSousMedicalisee": ""
                    }
                },
                "accidentDC": {
                    "accidentDC": 0,
                    "dateAccident": ""
                },
                "natureAssurance": {
                    "natureAssurance": 0,
                    "AT": {
                        "presenceFeuillet": 0,
                        "date": "",
                        "numero": "",
                        "refCaisseSupport": "",
                        "caisseIdentiqueAMO": 0,
                        "refCaisseCV": -1,
                        "priseEnChargeArmateur": 1,
                        "montantPECApias": 0
                    },
                    "maternite": {
                        "date": "",
                        "forcageExoMaternite": false
                    },
                    "SMG": {
                        "existencePEC": 0,
                        "montantPEC": 0
                    },
                    "maladie": {
                        "codeExoneration": "0"
                    }
                },
                "lstCotations": [
                    {
                        "type": 0,
                        "id": "1615891189714371918",
                        "idSeance": "",
                        "externalId": "CActeNGAP 843828",
                        "date": "20210316",
                        "dateAchevement": "",
                        "codeActe": "C",
                        "codeActeRemplacementAssocie": "",
                        "lettreCle": "C",
                        "quantite": 1,
                        "coefficient": 1,
                        "qualificatifDepense": "",
                        "montantDepassement": 0,
                        "montantTotal": 23,
                        "lieuExecution": 0,
                        "complement": "",
                        "codeActivite": "",
                        "codePhase": "",
                        "lstModificateurs": [],
                        "codeAssociation": "",
                        "supplementCharge": 0,
                        "remboursementExceptionnel": 0,
                        "dents": "",
                        "prixUnitaire": 23,
                        "baseRemboursement": 23,
                        "utilisationReferentiel": 1,
                        "codeRegroupement": "",
                        "taux": 70,
                        "montantFacture": 23,
                        "exonerationTMParticuliere": "-1",
                        "prixReferentiel": 23,
                        "depassementUniquement": false,
                        "codeJustifExoneration": "0",
                        "locked": false,
                        "lockedMessage": "",
                        "isHonoraire": false,
                        "isLpp": false,
                        "libelle": "Consultation",
                        "totalAMO": 16.1,
                        "totalAssure": 23,
                        "totalAMC": 0,
                        "duAMO": 0,
                        "duAMC": 0,
                        "forcageAMOAutorise": true,
                        "forcageAMCAutorise": true,
                        "protheseDentaire": false,
                        "coefficientStr": "1.0",
                        "ententePrealable": {
                            "valeur": 0,
                            "dateEnvoi": ""
                        },
                        "preventionCommune": {
                            "topPrevention": 0,
                            "qualifiant": ""
                        },
                        "forcageMontantAMO": {
                            "choix": 0,
                            "partAMO": 16.1,
                            "partAMOSaisie": 0
                        },
                        "forcageMontantAMC": {
                            "choix": 0,
                            "partAMC": 0,
                            "partAMCSaisie": 0
                        },
                        "lstPrestationLPP": []
                    }
                ],
                "pieceJustificativeAMO": {
                    "nature": -1,
                    "date": "",
                    "origine": 0
                },
                "prescripteur": {
                    "datePrescription": "20210316",
                    "originePrescription": ""
                },
                "organismeComplementaire": {
                    "tiersPayantAMO": 0,
                    "tiersPayantAMC": 0,
                    "victimeAttentat": 0,
                    "tiersPayantSNCF": 0,
                    "serviceAMO": {
                        "code": "00",
                        "libelle": "Le beneficiaire n'a pas de service AMO specifique",
                        "dateDebut": "",
                        "dateFin": ""
                    },
                    "assistant": {
                        "lstConventionsApplicables": [],
                        "lstFormulesApplicables": {
                            "lstFormules": [],
                            "lstMessages": []
                        },
                        "actionAttendue": 0,
                        "choix": 0,
                        "transformation": false,
                        "libelleTransformation": "",
                        "messageConventionsTeleservice": "",
                        "messageFormulesTeleservice": "",
                        "lstUrlsIdb": [],
                        "lstUrlsClc": []
                    }
                },
                "pav": {
                    "lstActesPav": []
                }
            },
            "patient": {
                "idExterne": "CPatient-279748",
                "immatriculation": "172192B99900224",
                "dateNaissance": "19721901",
                "rangGemellaire": 1,
                "qualite": 0,
                "qualiteLib": "Assuré",
                "nom": "TEST",
                "prenom": "ALAIN",
                "nircertifie": "",
                "dateNirCertifie": "",
                "declarationMTCV": -1,
                "droitsAMO": {
                    "codeRegime": "01",
                    "codeCaisse": "349",
                    "codeCentre": "9881",
                    "codeGestion": "18",
                    "codeSituation": "",
                    "codeCouverture": "00100",
                    "codeService": "00",
                    "dateDebut": "",
                    "dateFin": ""
                }
            },
            "assure": {
                "nom": "TEST",
                "prenom": "ALAIN",
                "immatriculation": "172192B99900224",
                "nomPatronymique": "TEST"
            },
            "ps": {
                "idJfse": 2,
                "idEtablissement": 0,
                "numImmatriculation": "99900064140",
                "typeImmatriculation": 8,
                "nom": "BISTOURI",
                "prenom": "NANA",
                "libelleCivilite": "Madame",
                "adresse": "",
                "famillePS": 0,
                "famille": "PR",
                "numeroSituation": 1,
                "typeIdStruct": 4,
                "numIdStruct": "999000641400000",
                "raisonSociale": "CABINET BISTOURI",
                "numFact": "991130634",
                "codeConventionnel": 3,
                "codeSpecialite": "04",
                "libelleSpecialite": "Chirurgie generale",
                "zoneTarif": "30",
                "zoneIK": "1",
                "lstCodeAgrement": [
                    "1",
                    "0",
                    "0"
                ],
                "habilitationFSE": 1,
                "habilitationLOT": 1,
                "modeExercice": 0,
                "secteurActivite": 31,
                "dateInstallation": "",
                "dateInstallationZoneSousMedicalisee": "",
                "caisseExecutant": "",
                "activeParcoursSoins": 1,
                "remplacant": {
                    "session": 0,
                    "nom": "",
                    "prenom": "",
                    "numFact": "",
                    "numRpps": "",
                    "desactivation": false
                }
            },
            "lstMessages": [
                {
                    "id": "1615891355877451021",
                    "level": 0,
                    "description": "",
                    "prestationsConcernees": "",
                    "source": 107,
                    "libSource": "CREER FACTURE",
                    "idGenre": "M223",
                    "messageValidation": true,
                    "regle": 0,
                    "regleId": "",
                    "regleForcable": false,
                    "regleSerialId": "0",
                    "codeDiagn": "",
                    "moduleDiagn": "",
                    "niveauDiagn": 0
                }
            ],
            "lstQuestions": [],
            "facturer": false,
            "ihm": {
                "pieceJustificative": false,
                "alsaceMoselle": false,
                "bandeauBenef": false,
                "medecinPrescripteur": false,
                "ameBase": false,
                "forcageExoMaternite": false,
                "tpSNCF": false,
                "horsTPAMC": true,
                "pharmacie": false,
                "parcoursDeSoins": true,
                "lstActesCCAM": false,
                "lstActesCotations": true,
                "modeCNDA": true,
                "blocageActes": false,
                "blocagePrescripteur": false,
                "av27_aideConsultation": false,
                "av27_medTraitant": false,
                "av27_tarifsOpposables": false,
                "relancerCalculCLC": false,
                "activationADRI": true,
                "activationIMTI": true,
                "activationAnnuaireAMC": true,
                "affichageEcranPav": false,
                "anonymisation": true
            }
        },
        "lstException": [],
        "cancel": false,
        "asynchronous": false
    }
}
JSON;

        $medical = [
            "invoice_id"               => 1,
            "nature_type"              => MedicalInsurance::CODE,
            "code_exoneration_disease" => 1,
            'common_law_accident'      => false,
        ];

        $work_accident = [
            "invoice_id"                    => 1,
            "nature_type"                   => WorkAccidentInsurance::CODE,
            "date"                          => new DateTimeImmutable("2020-10-14"),
            "has_work_stoppage_paper"       => true,
            "number"                        => 1765,
            "organisation_support"          => 9381,
            "is_organisation_identical_amo" => true,
            "organisation_vital"            => 3,
            "pec_armateur"                  => 1,
            "amount_apias"                  => 10.6,
        ];

        $maternity = [
            "invoice_id"        => 1,
            "nature_type"       => MaternityInsurance::CODE,
            "date"              => new DateTimeImmutable("2020-10-14"),
            "force_exoneration" => true,
        ];

        $smg = [
            "invoice_id"              => 1,
            "nature_type"             => FmfInsurance::CODE,
            "supported_smg_existence" => true,
            "supported_smg_expense"   => 76.9,
        ];

        return [
            [$medical, $fse_response],
            [$work_accident, $fse_response],
            [$maternity, $fse_response],
            [$smg, $fse_response]
        ];
    }

    public function testSaveWithWrongTypeAndExpectAnException(): void
    {
        $save_json = '{"method": {"output":{}}}';
        $rep       = Response::forge('', json_decode($save_json, true));
        $this->insurance_client->method('save')->willReturn($rep);

        $cache = new Cache('', '', Cache::NONE);

        $service = new InsuranceTypeService($this->insurance_client, $cache);

        $content = [
            "invoice_id"  => 1,
            "nature_type" => 987,
        ];

        $this->expectException(Exception::class);

        $service->save($content);
    }

    public function testSaveWithoutNatureTypeAndExpectException(): void
    {
        $save_json = '{"method": {"output":{}}}';
        $rep       = Response::forge('', json_decode($save_json, true));
        $this->insurance_client->method('save')->willReturn($rep);

        $cache = new Cache('', '', Cache::NONE);

        $service = new InsuranceTypeService($this->insurance_client, $cache);

        $content = [
            "invoice_id" => 1,
        ];

        $this->expectException(Exception::class);

        $service->save($content);
    }

    public function testSaveWithWrongInvoice(): void
    {
        $save_json = '{"method": {"lstException":[{"code": 2001, "message": "Invoice error"}]}}';
        $client    = $this->makeClientFromGuzzleResponses([$this->makeJsonGuzzleResponse(200, $save_json)]);
        $cache     = new Cache('', '', Cache::NONE);

        $service = new InsuranceTypeService(new InsuranceTypeClient($client), $cache);

        $content = [
            "invoice_id"        => 0,
            "nature_type"       => MaternityInsurance::CODE,
            "date"              => new DateTimeImmutable("2020-10-14"),
            "force_exoneration" => true,
        ];

        $this->expectException(InsuranceException::class);
        $service->save($content);
    }
}
