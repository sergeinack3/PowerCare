<?php
/**
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\CarePath;

use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\ApiClients\CarePathClient;
use Ox\Mediboard\Jfse\Domain\CarePath\CarePathDoctor;
use Ox\Mediboard\Jfse\Domain\CarePath\CarePathService;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class CarePathServiceTest extends UnitTestJfse {

  public function testSaveCarePath() {
    $care_path_client = $this->getMockBuilder(CarePathClient::class)
      ->disableOriginalConstructor()
      ->setMethods(['saveCarePath'])
      ->getMock();
    $care_path_client->method('saveCarePath')->willReturn(Response::forge('FDS-setParcoursSoins', ['method' => ['output' => []]]));

    $content = [
      'invoice_id' => 1,
      "indicator" => "O",
      CarePathDoctor::hydrate(["first_name" => "John", "last_name" => "Doe", "invoicing_id" => 123456789])
    ];

    $this->assertTrue((new CarePathService($care_path_client))->saveCarePath($content));
  }
}
