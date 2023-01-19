<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\ApiClients;

use DateTimeImmutable;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\ApiClients\ProofAmoClient;
use Ox\Mediboard\Jfse\Exceptions\Invoice\InvoiceException;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class ProofAmoClientTest extends UnitTestJfse
{
    public function testListProofTypes(): void
    {
        $json = <<<JSON
{
   "method":{
      "name":"PJAMO-getListeNaturePieceJustificativeAMO",
      "output": {
         "lstNaturePieceJustificativeAMO": [
            {
               "code": 0,
               "libelle": "Aucune pièce justificative"
            },
            {
               "code": 1,
               "libelle": "Bulletin de salaire, attestation, prise en charge"
            },
            {
               "code": 2,
               "libelle": "Carte d'assuré social ou consultation télématique"
            },
            {
               "code": 4,
               "libelle": "Carte vitale"
            }
         ]
      },
      "lstException": []
   }
}
JSON;

        $amo_client = new ProofAmoClient(
            $this->makeClientFromGuzzleResponses([$this->makeJsonGuzzleResponse(200, utf8_encode($json))])
        );
        $actual     = $amo_client->listProofTypes();

        $expected = Response::forge('PJAMO-getListeNaturePieceJustificativeAMO', json_decode(utf8_encode($json), true));

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider saveProofProvider
     */
    public function testSaveProofAmo(array $data): void
    {
        $json = '{"method": {"output": {"message": "success"}}}';

        $amo_client = new ProofAmoClient(
            $this->makeClientFromGuzzleResponses([$this->makeJsonGuzzleResponse(200, utf8_encode($json))])
        );
        $actual     = $amo_client->saveProofAmo($data['invoice_id'], $data['nature'], $data['date'], $data['origin']);

        $expected = Response::forge(
            'FDS-setPieceJustificativeAMO',
            json_decode('{"method": {"output": {"message": "success"}}}', true)
        );

        $this->assertEquals($expected, $actual);
    }

    public function saveProofProvider(): array
    {
        return [
            [['invoice_id' => 1, 'nature' => 2, 'date' => new DateTimeImmutable(), 'origin' => 2]],
            [['invoice_id' => 2, 'nature' => 3, 'date' => new DateTimeImmutable(), 'origin' => null]],
            [['invoice_id' => 2, 'nature' => 3, 'date' => null, 'origin' => null]],
        ];
    }
}
