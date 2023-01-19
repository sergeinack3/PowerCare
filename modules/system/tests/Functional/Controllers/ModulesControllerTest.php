<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Functional\Controllers;

use Ox\Tests\JsonApi\Item;
use Ox\Tests\OxWebTestCase;

class ModulesControllerTest extends OxWebTestCase
{
    public function testShowModuleLegacyNotInstalled(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/legacy/modules/lorem');

        $this->assertResponseStatusCodeSame(404);

        $error = $this->getJsonApiError($client);
        $this->assertEquals('The module lorem is not installed.', $error->getMessage());
    }

    public function testShowModuleLegacy(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/legacy/modules/system');

        $this->assertResponseStatusCodeSame(200);

        $item = $this->getJsonApiItem($client);
        $this->assertEquals('module', $item->getType());
        $this->assertEquals('core', $item->getAttribute('mod_type'));
        $this->assertEquals('system', $item->getAttribute('mod_name'));
        $this->assertTrue($item->hasAttribute('tabs'));
    }

    public function testShowModuleNotInstalled(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/modules/lorem');

        $this->assertResponseStatusCodeSame(404);

        $error = $this->getJsonApiError($client);
        $this->assertEquals('The module lorem is not active.', $error->getMessage());
    }

    public function testShowModule(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/modules/system');

        $this->assertResponseStatusCodeSame(200);

        $item = $this->getJsonApiItem($client);
        $this->assertEquals('module', $item->getType());
        $this->assertEquals('system', $item->getAttribute('mod_name'));
        $this->assertEquals('?m=system', $item->getLink('module_url'));
        $this->assertStringEndsWith('/api/modules/system/tabs', $item->getLink('tabs'));
    }

    public function testListModules(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/modules', ['limit' => 3]);

        $this->assertResponseStatusCodeSame(200);

        $collection = $this->getJsonApiCollection($client);
        $this->assertEquals(3, $collection->getMeta('count'));
        $this->assertStringEndsWith('/api/modules?state=active', $collection->getLink('modules'));

        /** @var Item $item */
        foreach ($collection as $item) {
            $this->assertEquals('module', $item->getType());
            $this->assertTrue($item->hasLink('module_url'));
            $this->assertTrue($item->hasLink('tabs'));
        }
    }

    public function testListModuleRoutesNotInstalled(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/modules/lorem/routes');

        $this->assertResponseStatusCodeSame(404);

        $error = $this->getJsonApiError($client);
        $this->assertEquals('Invalid module name lorem', $error->getMessage());
    }


    public function testListModulesRoutes(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/modules/system/routes');

        $this->assertResponseStatusCodeSame(200);

        $collection = $this->getJsonApiCollection($client);
        $item = $collection->getFirstItem();
        $this->assertEquals('path_map', $item->getType());
        $this->assertTrue($item->hasLink('schema'));
    }
}
