<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Mappers;

use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\HealthInsurance\HealthInsurance;
use Ox\Mediboard\Jfse\Mappers\HealthInsuranceMapper;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class HealthInsuranceMapperTest extends UnitTestJfse
{
    /**
     * @dataProvider saveRequestProvider
     */
    public function testGetArrayFromData(HealthInsurance $health_insurance, array $expected): void
    {
        $mapper = new HealthInsuranceMapper();

        $actual = $mapper->getArrayFromData($health_insurance->getCode(), $health_insurance->getName());
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider searchRequestProvider
     */
    public function testGetArrayFromResponse(Response $response, array $expected): void
    {
        $mapper = new HealthInsuranceMapper();

        $actual = $mapper::getArrayFromResponse($response);
        $this->assertEquals($expected, $actual);
    }

    public function saveRequestProvider(): array
    {
        $health_insurance = HealthInsurance::hydrate(['code' => '123456789', 'name' => 'Mutuelle Test']);
        $expected         = [
            "updateMutuelle" => [
                "nom"             => "Mutuelle Test",
                "code"            => "123456789",
                'idEtablissement' => 0,
            ],
        ];

        return [[$health_insurance, $expected]];
    }

    public function searchRequestProvider(): array
    {
        $json_response = <<<JSON
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
        $response      = Response::forge(
            'MUT-getListeMutuelles',
            json_decode(utf8_encode($json_response), true)
        );
        $expected      = [
            [
                "code"                 => "302976568",
                "name"                 => "Mutuelle Centrale des Finances",
                "type_of_organization" => 1,
            ],
            [
                "code"                 => "341230380",
                "name"                 => "Mutuelle des Agents des Impots",
                "type_of_organization" => 1,
            ],
        ];

        return [[$response, $expected]];
    }
}
