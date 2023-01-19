<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Mappers;

use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\RefundCancelRequest\RefundCancelRequestDetails;
use Ox\Mediboard\Jfse\Mappers\RefundCancelRequestMapper;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

/**
 * Class RefundRequestCancelMapperTest
 *
 * @package Ox\Mediboard\Jfse\Tests\Unit\Mappers
 */
class RefundCancelRequestMapperTest extends UnitTestJfse
{
    /**
     * @dataProvider getDataFromResponseProvider
     *
     * @param Response $response
     * @param array    $expected
     */
    public function testGetDataFromResponse(
        Response $response,
        array $expected
    ): void {
        $mapper = new RefundCancelRequestMapper();

        $actual = $mapper->getDataFromResponse($response);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider getInvoiceDetailsProvider
     *
     * @param Response                   $response
     * @param RefundCancelRequestDetails $expected
     */
    public function testGetInvoiceDetailsFromResponse(
        Response $response,
        RefundCancelRequestDetails $expected
    ): void {
        $mapper = new RefundCancelRequestMapper();

        $actual = $mapper->getInvoiceDetailsFromResponse($response);
        $this->assertEquals($expected, $actual);
    }

    public function getInvoiceDetailsProvider(): array
    {
        $json_response = <<<JSON
        {
            "method": {
                "output": {
                    "lstDetailsFactures": 
                    {
                        "numeroDre": "111111",
                        "idFacture": "123456",
                        "noFacture": "222222",
                        "nomBeneficiaire": "Doe",
                        "prenomBeneficiaire": "John",
                        "securisation": "1",
                        "nomPs": "Max LIBRE",
                        "dateElaboration": "20201111"
                    }
                }
            }
        }
JSON;
        $response      = Response::forge(
            'DAR-getDetailsFactures',
            json_decode(utf8_encode($json_response), true)
        );
        $expected      = RefundCancelRequestDetails::hydrate([
            "dre_number"             => "111111",
            "invoice_id"             => "123456",
            "invoice_number"         => "222222",
            "beneficiary_last_name"  => "Doe",
            "beneficiary_first_name" => "John",
            "securisation"           => "1",
            "ps_name"                => "Max LIBRE",
            "date_elaboration"       => "20201111",
        ]);

        return [[$response, $expected]];
    }

    public function getDataFromResponseProvider(): array
    {
        $json_response = <<<JSON
        {
            "method": {
                "output": {
                    "lstDREAnnulations": [
                        {
                            "type": "type_a",
                            "idJfse": 1,
                            "noLotDRE": "333333",
                            "noLotFSE": "444444",
                            "noFacture": "222222",
                            "idFacture": 123456
                        },
                        {
                            "type": "type_b",
                            "idJfse": 1,
                            "noLotDRE": "555555",
                            "noLotFSE": "666666",
                            "noFacture": "777777",
                            "idFacture": 789123
                        }
                    ]
                }
            }
        }
JSON;
        $response      = Response::forge(
            'DAR-getListeDREAnnulation',
            json_decode(utf8_encode($json_response), true)
        );
        $expected      = [
            [
                "type"           => "type_a",
                "jfse_id"        => 1,
                "dre_lot_number" => "333333",
                "fse_lot_number" => "444444",
                "invoice_number" => "222222",
                "invoice_id"     => 123456,
            ],
            [
                "type"           => "type_b",
                "jfse_id"        => 1,
                "dre_lot_number" => "555555",
                "fse_lot_number" => "666666",
                "invoice_number" => "777777",
                "invoice_id"     => 789123,
            ],
        ];

        return [[$response, $expected]];
    }
}
