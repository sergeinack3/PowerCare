<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Domain\Formula;

use Ox\Mediboard\Jfse\ApiClients\FormulaClient;
use Ox\Mediboard\Jfse\Domain\Formula\Formula;
use Ox\Mediboard\Jfse\Domain\Formula\FormulaService;
use Ox\Mediboard\Jfse\Domain\Formula\Parameter;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class FormulaServiceTest
 *
 * @package Ox\Mediboard\Jfse\Tests\Unit\Domain\Formula
 */
class FormulaServiceTest extends UnitTestJfse
{
    /** @var MockObject */
    private $client;

    public function setUp(): void
    {
        parent::setUp();

        $empty_data   = '{"method": {"output": {}}}';
        $this->client = $this->makeClientFromGuzzleResponses([$this->makeJsonGuzzleResponse(200, $empty_data)]);
    }

    public function testGetOperands(): void
    {
        $this->assertIsArray((new FormulaService(new FormulaClient($this->client)))->listFormulaOperands());
    }

    public function testGetFormulas(): void
    {
        $this->assertIsArray((new FormulaService(new FormulaClient($this->client)))->listFormulas());
    }

    /**
     * @dataProvider saveProvider
     */
    public function testSaveFormula(array $data): void
    {
        $service = new FormulaService(new FormulaClient($this->client));

        $this->assertTrue(
            $service->save(
                $data['nomFormule'],
                $data['multiplicateur'],
                $data['plafond'],
                $data['operande1'],
                $data['operande2'],
                $data['operateur']
            )
        );
    }

    public function saveProvider(): array
    {
        $entity_A = [
            "nomFormule"     => "Formule Test",
            "multiplicateur" => 0.5,
            "plafond"        => 100.0,
            "operande1"      => "1",
            "operande2"      => "2",
            "operateur"      => "2",
        ];
        $entity_B = [
            "nomFormule"     => "Formule Test 2",
            "multiplicateur" => 0.5,
            "plafond"        => 1000.0,
            "operande1"      => "1",
            "operande2"      => "-1",
            "operateur"      => "0",
        ];

        return [[$entity_A], [$entity_B]];
    }

    public function testListFormulasFromFSE(): void
    {
        $json = <<<JSON
{
    "method": {
        "output": {
            "lstFormules": [
                {
                    "idFormule": 1,
                    "pmss": 0.5,
                    "noPrestationAttachee": "01",
                    "noFormule": "02",
                    "libelle": "Formula 1",
                    "calculTheorique": "calculation",
                    "sts": false,
                    "lstParametres": [
                        {
                            "numero": "1",
                            "libelle": "Parameter label",
                            "type": "P",
                            "valeur": 10.5
                        }
                    ]
                }
            ]
        }
    }
}
JSON;

        $client  = $this->makeClientFromGuzzleResponses([$this->makeJsonGuzzleResponse(200, $json)]);
        $service = new FormulaService(new FormulaClient($client));

        $formulas = [
            Formula::hydrate(
                [
                    "formula_id"              => 1,
                    "pmss"                    => 0.5,
                    "prestation_number"       => "01",
                    "formula_number"          => "02",
                    "label"                   => "Formula 1",
                    "theoretical_calculation" => "calculation",
                    "sts"                     => false,
                    "parameters"              => [
                        Parameter::hydrate(
                            [
                                "number" => "1",
                                "label"  => "Parameter label",
                                "type"   => Parameter::TYPE_PERCENTAGE,
                                "value"  => 10.5,
                            ]
                        ),
                    ],
                ]
            ),
        ];

        $this->assertEquals($formulas, $service->listFormulasFromFSE(111245623));
    }
}
