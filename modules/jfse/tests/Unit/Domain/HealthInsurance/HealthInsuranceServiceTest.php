<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Domain\HealthInsurance;

use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\ApiClients\HealthInsuranceClient;
use Ox\Mediboard\Jfse\Domain\HealthInsurance\HealthInsurance;
use Ox\Mediboard\Jfse\Domain\HealthInsurance\HealthInsuranceService;
use Ox\Mediboard\Jfse\Exceptions\HealthInsurance\HealthInsuranceException;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class HealthInsuranceServiceTest
 *
 * @package Ox\Mediboard\Jfse\Tests\Unit\Domain\HealthInsurance
 */
class HealthInsuranceServiceTest extends UnitTestJfse
{
    /** @var MockObject */
    private $client;

    public function setUp(): void
    {
        parent::setUp();

        $response     = <<<JSON
        {
            "method": {
                "output": {
                    "lst": [
                        {
                          "code": "302976568",
                          "nom": "Mutuelle Centrale des Finances",
                          "typeOrganisme": 1
                        },
                        {
                          "code": "341230380",
                          "nom": "Mutuelle des Agents des Impots",
                          "typeOrganisme": 1
                        }
                    ]
                }
            }
        }
JSON;
        $this->client = $this->makeClientFromGuzzleResponses(
            [$this->makeJsonGuzzleResponse(200, $response)]
        );
    }

    public function testSearchHealthInsurance(): void
    {
        $expected = [
            HealthInsurance::hydrate(
                ["code" => "302976568", "name" => "Mutuelle Centrale des Finances", "type_of_organization" => 1]
            ),
            HealthInsurance::hydrate(
                ["code" => "341230380", "name" => "Mutuelle des Agents des Impots", "type_of_organization" => 1]
            ),

        ];
        $this->assertEquals(
            $expected,
            (new HealthInsuranceService(new HealthInsuranceClient($this->client)))->search(
                1,
                HealthInsurance::SEARCH_MODE_CONTAINS
            )
        );
    }

    public function testSearchInsuranceWithUnknownMode(): void
    {
        $save_json = '
{
    "method": {
        "lstMessages": [
            {
                "id": "1605866821174061254",
                "level": 0,
                "description": "La valeur du champ \'mode\' est invalide ! (noeud \'getListeMutuelles\')",
                "prestationsConcernees": "",
                "source": 3200,
                "libSource": "LISTE DE MUTUELLES",
                "idGenre": "M017",
                "messageValidation": false,
                "regle": 0,
                "regleId": "",
                "regleForcable": false,
                "regleSerialId": "0",
                "codeDiagn": "",
                "moduleDiagn": "",
                "niveauDiagn": 0
            }
        ]
    }
}';
        $client    = $this->makeClientFromGuzzleResponses(
            [$this->makeJsonGuzzleResponse(200, utf8_encode($save_json))]
        );
        $service   = new HealthInsuranceService(new HealthInsuranceClient($client));

        $this->expectException(HealthInsuranceException::class);
        $service->search(1, 3);
    }

    public function testSaveHealthInsurance(): void
    {
        $returned_data = <<<JSON
        {
         "method": {
                "name": "MUT-updateMutuelle",
                "service": true,
                "parameters": {
                    "updateMutuelle": {
                        "code": "123456789",
                        "nom": "Mutuelle Test",
                        "lstIdJfse": {
                            "idJfse": 1
                        }
                    }
                },
                "lstException": [],
                "cancel": false,
                "asynchronous": false
            }
        }
JSON;
        $client        = $this->makeClientFromGuzzleResponses(
            [$this->makeJsonGuzzleResponse(200, $returned_data)]
        );

        $health_insurance = HealthInsurance::hydrate(['code' => '123456789', 'name' => 'Mutuelle Test']);

        $actual = (new HealthInsuranceService(new HealthInsuranceClient($client)))->save(
            $health_insurance->getCode(),
            $health_insurance->getName()
        );

        $this->assertTrue($actual);
    }

    public function testSaveHealthInsuranceWithInvalidFormData(): void
    {
        $save_json = '
{
    "method": {
        "lstMessages": [
            {
                "id": "1605867525145931305",
                "level": 0,
                "description": "Champ vide non autorisé sur le champ code (noeud \'updateMutuelle\')",
                "prestationsConcernees": "",
                "source": 3201,
                "libSource": "MISE A JOUR DE MUTUELLE",
                "idGenre": "M007",
                "messageValidation": false,
                "regle": 0,
                "regleId": "",
                "regleForcable": false,
                "regleSerialId": "0",
                "codeDiagn": "",
                "moduleDiagn": "",
                "niveauDiagn": 0
            }
        ]
    }
}';
        $client    = $this->makeClientFromGuzzleResponses(
            [$this->makeJsonGuzzleResponse(200, utf8_encode($save_json))]
        );
        $service   = new HealthInsuranceService(new HealthInsuranceClient($client));

        $this->expectException(HealthInsuranceException::class);

        $service->save("", "");
    }

    public function testdelete(): void
    {
        $returned_data = <<<JSON
        {
            "method": {
                "name": "MUT-deleteMutuelle",
                "service": true,
                "parameters": {
                    "deleteMutuelle": {
                        "code": "123456789"
                    }
                },
                "lstException": [],
                "cancel": false,
                "asynchronous": false
                }
        }
JSON;
        $client        = $this->makeClientFromGuzzleResponses(
            [$this->makeJsonGuzzleResponse(200, $returned_data)]
        );
        $data          =
            [
                "deleteMutuelle" =>
                    [
                        "code" => "123456789",
                    ],
            ];
        $expected      = Response::forge('MUT-deleteMutuelle', json_decode($returned_data, true));
        $actual        = (new HealthInsuranceService(new HealthInsuranceClient($client)))->delete(
            "123456789"
        );
        $this->assertEquals($expected, $actual);
    }

    public function testDeleteHealthInsuranceWithInvalidCode(): void
    {
        $save_json = '
{
    "method": {
        "lstMessages": [
            {
                "id": "1605802367298741807",
                "level": 0,
                "description": "Champ vide non autorisé sur le champ code (noeud \'deleteMutuelle\')",
                "prestationsConcernees": "",
                "source": 3202,
                "libSource": "SUPPRESSION DE MUTUELLE",
                "idGenre": "M007",
                "messageValidation": false,
                "regle": 0,
                "regleId": "",
                "regleForcable": false,
                "regleSerialId": "0",
                "codeDiagn": "",
                "moduleDiagn": "",
                "niveauDiagn": 0
            }
        ]
    }
}';
        $client    = $this->makeClientFromGuzzleResponses(
            [$this->makeJsonGuzzleResponse(200, utf8_encode($save_json))]
        );
        $service   = new HealthInsuranceService(new HealthInsuranceClient($client));

        $this->expectException(HealthInsuranceException::class);

        $service->delete("");
    }
}
