<?php
/**
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\CarePath\CarePath;
use Ox\Mediboard\Jfse\Domain\CarePath\CarePathEnum;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class CarePathClientTest extends UnitTestJfse {
  public function testSaveCarePath(): void {
    $returned_json = <<<JSON
{
    "method": {
        "name": "FDS-setParcoursSoins",
        "service": true,
        "parameters": {
            "idFacture": "1602840155494611941",
            "parcoursSoins": {
                "indicateur": "U"
            }
        },
        "output": {},
        "lstException": []
    }
}
JSON;

    $client = self::makeClientFromGuzzleResponses([self::makeJsonGuzzleResponse(200, $returned_json)]);
    $care_path_client = new CarePathClient($client);

    $care_path = CarePath::hydrate(['invoice_id' => 1602840155494611941, 'indicator' => CarePathEnum::EMERGENCY()]);
    $actual = $care_path_client->saveCarePath($care_path);

    $expected = Response::forge('FDS-setParcoursSoins', json_decode($returned_json, true));

    $this->assertEquals($expected, $actual);
  }
}
