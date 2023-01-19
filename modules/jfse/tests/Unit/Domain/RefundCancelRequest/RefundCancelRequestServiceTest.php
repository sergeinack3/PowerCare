<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Domain\RefundCancelRequest;

use Ox\Mediboard\Jfse\ApiClients\RefundCancelRequestClient;
use Ox\Mediboard\Jfse\Domain\RefundCancelRequest\RefundCancelRequest;
use Ox\Mediboard\Jfse\Domain\RefundCancelRequest\RefundCancelRequestDetails;
use Ox\Mediboard\Jfse\Domain\RefundCancelRequest\RefundCancelRequestService;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class RefundCancelRequestServiceTest
 *
 * @package Ox\Mediboard\Jfse\Tests\Unit\Domain\RefundCancelRequest
 */
class RefundCancelRequestServiceTest extends UnitTestJfse
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
                    ]
                }
            }
        }
JSON;
        $this->client = $this->makeClientFromGuzzleResponses(
            [$this->makeJsonGuzzleResponse(200, $response)]
        );
    }

    public function testGetListe(): void
    {
        $response = <<<JSON
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
        $expected = [
            RefundCancelRequest::hydrate(
                [
                    "type"           => "type_a",
                    "jfse_id"        => 1,
                    "dre_lot_number" => "333333",
                    "fse_lot_number" => "444444",
                    "invoice_number" => "222222",
                    "invoice_id"     => 123456,
                ]
            ),
            RefundCancelRequest::hydrate(
                [
                    "type"           => "type_b",
                    "jfse_id"        => 1,
                    "dre_lot_number" => "555555",
                    "fse_lot_number" => "666666",
                    "invoice_number" => "777777",
                    "invoice_id"     => 789123,
                ]
            ),
        ];
        $client   = $this->makeClientFromGuzzleResponses(
            [$this->makeJsonGuzzleResponse(200, $response)]
        );
        $service  = new RefundCancelRequestService(new RefundCancelRequestClient($client));
        $this->assertEquals(
            $expected,
            $service->getListe(
                1,
                null,
                null,
                null,
                null
            )
        );
    }

    public function testSave(): void
    {
        $response = <<<JSON
        {
            "method": {
                "name": "DAR-saveDREAnnulation",
                "service": true,
                "parameters": {
                    "saveDREAnnulation": {
                        "lstIdFactures": {
                            "idFacture": 123456,
                            "dateElaboration" : 20201111,
                            "securisation": 1
                        }
                    }
                },
                "lstException": [],
                "cancel": false,
                "asynchronous": false
            }
        }
JSON;
        $client   = $this->makeClientFromGuzzleResponses(
            [$this->makeJsonGuzzleResponse(200, $response)]
        );

        $refund_cancel_request = RefundCancelRequestDetails::hydrate(
            [
                "invoice_id"       => 123456,
                "date_elaboration" => 20201111,
                "securisation"     => 1,
            ]
        );

        $actual = (new RefundCancelRequestService(new RefundCancelRequestClient($client)))->save(
            $refund_cancel_request->getInvoiceId(),
            $refund_cancel_request->getDateElaboration(),
            $refund_cancel_request->getSecurisation()
        );

        $this->assertTrue($actual);
    }

    public function testGetDetails(): void
    {
        $response = '
        {
            "method": {
                "output": {
                    "lstDetailsFactures": {
                        "numeroDre": "111111",
                        "idFacture": 123456,
                        "noFacture": "222222",
                        "nomBeneficiaire": "Doe",
                        "prenomBeneficiaire": "John",
                        "securisation": 1,
                        "nomPs": "Max LIBRE",
                        "dateElaboration": 20201111
                    }
                }
            }
        }';
        $client   = $this->makeClientFromGuzzleResponses(
            [$this->makeJsonGuzzleResponse(200, utf8_encode($response))]
        );
        $service  = new RefundCancelRequestService(new RefundCancelRequestClient($client));
        $expected = RefundCancelRequestDetails::hydrate([
            "dre_number"             => "111111",
            "invoice_id"             => 123456,
            "invoice_number"         => "222222",
            "beneficiary_last_name"  => "Doe",
            "beneficiary_first_name" => "John",
            "securisation"           => 1,
            "ps_name"                => "Max LIBRE",
            "date_elaboration"       => 20201111,
        ]);

        $this->assertEquals(
            $expected,
            $service->getDetails(123456)
        );
    }
}
