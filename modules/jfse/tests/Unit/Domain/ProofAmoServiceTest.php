<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Domain;

use DateTimeImmutable;
use Ox\Core\Cache;
use Ox\Mediboard\Jfse\ApiClients\ProofAmoClient;
use Ox\Mediboard\Jfse\Domain\ProofAmo\ProofAmoService;
use Ox\Mediboard\Jfse\Domain\ProofAmo\ProofAmoType;
use Ox\Mediboard\Jfse\Exceptions\Invoice\InvoiceException;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class ProofAmoServiceTest extends UnitTestJfse
{
    /**
     * List of proof types must return an array of types using empty cache
     */
    public function testListProofTypesMustReturnAnArrayOfTypesUsingEmptyCache(): void
    {
        $returned_data = <<<JSON
{
  "method": {
    "output": {
      "lstNaturePieceJustificativeAMO": [
        {"code": 0, "libelle": "Proof example 1"},
        {"code": 1, "libelle": "Proof example 2"}
      ]
    },
    "lstException": []
  }
}
JSON;

        $responses       = [
            self::makeJsonGuzzleResponse(200, $returned_data),
        ];
        $client          = $this->makeClientFromGuzzleResponses($responses);
        $proofAMO_client = new ProofAmoClient($client);

        $cache = new Cache('', '', Cache::NONE);

        $proofAMO_service = new ProofAmoService($proofAMO_client, $cache);
        $actual_types     = $proofAMO_service->listProofTypes();

        $expected_types = [
            ProofAmoType::hydrate(["code" => 0, "label" => "Proof example 1"]),
            ProofAmoType::hydrate(["code" => 1, "label" => "Proof example 2"]),
        ];

        $this->assertEquals($expected_types, $actual_types);
    }

    /**
     * List of proof types must return an array of types using loaded cache
     */
    public function testListProofTypesMustReturnAnArrayOfTypesUsingLoadedCache(): void
    {
        $proofAMO_client = new ProofAmoClient($this->makeClientFromGuzzleResponses([]));

        $data  = [
                ["code" => 0, "label" => "Proof example 1"],
                ["code" => 1, "label" => "Proof example 2"],
        ];
        $cache = $this->getMockBuilder(Cache::class)->disableOriginalConstructor()->setMethods(['get'])->getMock();
        $cache->method('get')->willReturn($data);

        $proofAMO_service = new ProofAmoService($proofAMO_client, $cache);
        $actual_types     = $proofAMO_service->listProofTypes();

        $expected_types = [
            ProofAmoType::hydrate(["code" => 0, "label" => "Proof example 1"]),
            ProofAmoType::hydrate(["code" => 1, "label" => "Proof example 2"]),
        ];

        $this->assertEquals($expected_types, $actual_types);
    }

    public function testSavingProofMustReturnAnArray(): void
    {
        $data = <<<JSON
{
    "method": {
        "name": "FDS-setPieceJustificativeAMO",
        "service": true,
        "parameters": {
            "idFacture": "1602840155494611941",
            "pieceJustificativeAMO": {
                "nature": 0
            }
        },
        "output": {
            "facture": {
                "keyFacture": "1602840155494611941",
                "securisation": 3,
                "alsaceMoselle": 0,
                "dateElaboration": "20201016",
                "idIntegrateur": "0",
                "differerEnvoi": 0,
                "anonymiser": 0,
                "modePapier": 0,
                "modeFSP": -1,
                "totalMontants": 0.0,
                "totalAMO": 0.0,
                "totalAssure": 0.0,
                "totalAMC": 0.0,
                "numeroFacture": "-1",
                "duAMO": 0.0,
                "duAMC": 0.0,
                "acteIsoleSerie": 0,
                "forcageAMO": false,
                "forcageAMC": false,
                "valeurPlafondCMU": 0.0,
                "etatDroitsAMO": "Les droits AMO sont ouverts",
                "checkVitaleCard": false,
                "tauxGlobal": -1,
                "corrigerOuRecycler": 0,
                "parcoursSoins": {
                    "indicateur": "",
                    "declaration": 0,
                    "statut": 0,
                    "medecin": {
                        "nom": "",
                        "prenom": "",
                        "noIdentification": "",
                        "dateInstallation": "",
                        "dateInstallationZoneSousMedicalisee": ""
                    }
                },
                "accidentDC": {
                    "accidentDC": -1,
                    "dateAccident": ""
                },
                "pieceJustificativeAMO": {
                    "nature": -1,
                    "date": "",
                    "origine": 0
                }
            }
        },
        "lstException": []
    }
}
JSON;

        $responses       = [
            self::makeJsonGuzzleResponse(200, $data),
        ];
        $client          = $this->makeClientFromGuzzleResponses($responses);
        $proofAMO_client = new ProofAmoClient($client);

        $cache = new Cache("JfseProofAmo", "proofTypes", Cache::NONE);

        $proofAMO_service = new ProofAmoService($proofAMO_client, $cache);
        $actual_types     = $proofAMO_service->saveProofAmo(1, 1, new DateTimeImmutable('2020-10-06'), 1);

        $this->assertTrue($actual_types);
    }

    public function testSavingProofMustThrowAnExceptionWhenInvalidInvoiceId(): void
    {
        $data = <<<JSON
{
    "method": {
        "lstException": [
            {
                "code": 2001,
                "source": "FSE-SERVER",
                "description": "Objet FSEDATA non intialisé !"
            }
        ]
    }
}
JSON;

        $responses       = [
            self::makeJsonGuzzleResponse(200, utf8_encode($data)),
        ];
        $client          = $this->makeClientFromGuzzleResponses($responses);
        $proofAMO_client = new ProofAmoClient($client);

        $cache = new Cache("JfseProofAmo", "proofTypes", Cache::NONE);

        $proofAMO_service = new ProofAmoService($proofAMO_client, $cache);

        $this->expectException(InvoiceException::class);
        $proofAMO_service->saveProofAmo(1, 1, new DateTimeImmutable('2020-10-06'), 1);
    }

    public function testSaveProofAmoWhenWrongInvoiceId(): void
    {
        $json     = '{"method": {"lstException": [{"code": 2001, "message": "Error"}]}}';
        $response = $this->makeJsonGuzzleResponse(200, utf8_encode(utf8_encode($json)));

        $client     = $this->makeClientFromGuzzleResponses([$response]);
        $amo_client = new ProofAmoClient($client);

        $service = new ProofAmoService($amo_client);

        $this->expectException(InvoiceException::class);
        $service->saveProofAmo(1, 2, null, null);
    }
}
