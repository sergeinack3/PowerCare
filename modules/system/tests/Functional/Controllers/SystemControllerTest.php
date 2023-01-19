<?php
/**
 * @author  SARL OpenXtrem <dev@openxtrem.com>
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

namespace Ox\Mediboard\System\Tests\Functional\Controllers;

use Ox\Mediboard\System\Controllers\SystemController;
use Ox\Tests\OxWebTestCase;

class SystemControllerTest extends OxWebTestCase
{
    public function testStatus(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/status');

        $this->assertResponseIsSuccessful();
        $item = $this->getJsonApiItem($client);
        $this->assertTrue($item->hasAttribute('status'));
        $this->assertEquals($item->getAttribute('status'), 'online');
    }


    public function testOAS()
    {
        $client = static::createClient();
        $client->request('GET', '/api/doc');

        $this->assertResponseIsSuccessful();
    }

    public function testOffline(): void
    {
        $container  = static::getContainer();
        $controller = $container->get(SystemController::class);
        $response = $controller->offline('Test offline');
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertStringContainsString('<body class="offline">', $response->getContent());
    }
}
