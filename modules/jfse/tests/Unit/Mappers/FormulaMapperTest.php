<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Mappers;

use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\Formula\Formula;
use Ox\Mediboard\Jfse\Domain\Formula\Parameter;
use Ox\Mediboard\Jfse\Mappers\FormulaMapper;
use Ox\Mediboard\Jfse\Mappers\FormulaOperandMapper;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class FormulaMapperTest extends UnitTestJfse
{
    /**
     * @dataProvider getFormulasFromResponseProvider
     */
    public function testGetFormulasFromResponse(array $response, array $expected): void
    {
        $mapper = new FormulaMapper();

        $actual = $mapper->getFormulasFromResponse($response);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider getListeOperandesProvider
     */
    public function testGetList(Response $response, array $expected): void
    {
        $mapper = new FormulaOperandMapper();

        $actual = $mapper->getOperandsFromResponse($response);
        $this->assertEquals($expected, $actual);
    }

    public function getFormulasFromResponseProvider(): array
    {
        $json_response = <<<JSON
{
    "lstFormules": [
        {
            "idFormule": "1294310104447274326",
            "pmss": 0.0,
            "noPrestationAttachee": "",
            "noFormule": "1294310104447274326",
            "libelle": "Ticket Modérateur",
            "calculTheorique": "",
            "sts": false,
            "lstParametres": [
                {
                    "numero": "0",
                    "libelle": "Multiplicateur",
                    "type": "M",
                    "valeur": 1.0
                },
                {
                    "numero": "1",
                    "libelle": "Operande1",
                    "type": "M",
                    "valeur": 0.0
                },
                {
                    "numero": "2",
                    "libelle": "Operateur",
                    "type": "M",
                    "valeur": 0.0
                },
                {
                    "numero": "3",
                    "libelle": "Operande2",
                    "type": "M",
                    "valeur": 0.0
                },
                {
                    "numero": "4",
                    "libelle": "Plafond",
                    "type": "M",
                    "valeur": 0.0
                }
            ]
        },
        {
            "idFormule": "1346164646571859407",
            "pmss": 0.0,
            "noPrestationAttachee": "",
            "noFormule": "1346164646571859407",
            "libelle": "Frais Reels",
            "calculTheorique": "",
            "sts": false,
            "lstParametres": [
                {
                    "numero": "0",
                    "libelle": "Multiplicateur",
                    "type": "M",
                    "valeur": 1.0
                },
                {
                    "numero": "1",
                    "libelle": "Operande1",
                    "type": "M",
                    "valeur": 2.0
                },
                {
                    "numero": "2",
                    "libelle": "Operateur",
                    "type": "M",
                    "valeur": 0.0
                },
                {
                    "numero": "3",
                    "libelle": "Operande2",
                    "type": "M",
                    "valeur": 2.0
                },
                {
                    "numero": "4",
                    "libelle": "Plafond",
                    "type": "M",
                    "valeur": 0.0
                }
            ]
        }
    ]
}
JSON;
        $response      = array_map_recursive('utf8_decode', json_decode(utf8_encode($json_response), true));

        $expected      = [
            1294310104447274326 => Formula::hydrate([
                "formula_id"            => "1294310104447274326",
                "pmss"                  => 0.0,
                "prestation_number"     => "",
                "formula_number"        => "1294310104447274326",
                "label"                 => "Ticket Modérateur",
                "theorical_calculation" => "",
                "sts"                   => false,
                "parameters"            => [
                    Parameter::hydrate([
                        "number" => "0",
                        "label"  => "Multiplicateur",
                        "type"   => "M",
                        "value"  => 1.0,
                    ]),
                    Parameter::hydrate([
                        "number" => "1",
                        "label"  => "Operande1",
                        "type"   => "M",
                        "value"  => 0.0,
                    ]),
                    Parameter::hydrate([
                        "number" => "2",
                        "label"  => "Operateur",
                        "type"   => "M",
                        "value"  => 0.0,
                    ]),
                    Parameter::hydrate([
                        "number" => "3",
                        "label"  => "Operande2",
                        "type"   => "M",
                        "value"  => 0.0,
                    ]),
                    Parameter::hydrate([
                        "number" => "4",
                        "label"  => "Plafond",
                        "type"   => "M",
                        "value"  => 0.0,
                    ]),
                ],
            ]),
            1346164646571859407 => Formula::hydrate([
                "formula_id"            => "1346164646571859407",
                "pmss"                  => 0.0,
                "prestation_number"     => "",
                "formula_number"        => "1346164646571859407",
                "label"                 => "Frais Reels",
                "theorical_calculation" => null,
                "sts"                   => false,
                "parameters"            => [
                    Parameter::hydrate([
                        "number" => "0",
                        "label"  => "Multiplicateur",
                        "type"   => "M",
                        "value"  => 1.0,
                    ]),
                    Parameter::hydrate([
                        "number" => "1",
                        "label"  => "Operande1",
                        "type"   => "M",
                        "value"  => 2.0,
                    ]),
                    Parameter::hydrate([
                        "number" => "2",
                        "label"  => "Operateur",
                        "type"   => "M",
                        "value"  => 0.0,
                    ]),
                    Parameter::hydrate([
                        "number" => "3",
                        "label"  => "Operande2",
                        "type"   => "M",
                        "value"  => 2.0,
                    ]),
                    Parameter::hydrate([
                        "number" => "4",
                        "label"  => "Plafond",
                        "type"   => "M",
                        "value"  => 0.0,
                    ]),
                ],
            ]),
        ];

        return [[$response, $expected]];
    }

    public function getListeOperandesProvider(): array
    {
        $json_response = <<<JSON
{
    "method": {
        "output": {
            "lst": [
                {
                    "code": -1,
                    "libelle": "Pas d'opérande"
                },
                {
                    "code": 0,
                    "libelle": "Ticket Modérateur (TM)"
                },
                {
                    "code": 1,
                    "libelle": "Tarif de responsabilité (TR)"
                },
                {
                    "code": 2,
                    "libelle": "Dépense réelle (DR)"
                },
                {
                    "code": 3,
                    "libelle": "Part assurance obligatoire (MRO)"
                },
                {
                    "code": 4,
                    "libelle": "Plafond Mensuel Sécurité sociale (PMSS)"
                }
            ]
        }
    }
}
JSON;
        $response      = Response::forge(
            'FORM-getListeOperandes',
            json_decode(utf8_encode($json_response), true)
        );
        $expected      = [
            [
                "code"    => -1,
                "label" => "Pas d'opérande",
            ],
            [
                "code"    => 0,
                "label" => "Ticket Modérateur (TM)",
            ],
            [
                "code"    => 1,
                "label" => "Tarif de responsabilité (TR)",
            ],
            [
                "code"    => 2,
                "label" => "Dépense réelle (DR)",
            ],
            [
                "code"    => 3,
                "label" => "Part assurance obligatoire (MRO)",
            ],
            [
                "code"    => 4,
                "label" => "Plafond Mensuel Sécurité sociale (PMSS)",
            ],
        ];

        return [[$response, $expected]];
    }
}
