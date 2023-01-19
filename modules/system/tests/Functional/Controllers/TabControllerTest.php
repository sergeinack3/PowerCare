<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Functional\Controllers;

use Ox\Tests\JsonApi\Collection;
use Ox\Tests\JsonApi\Item;
use Ox\Tests\OxWebTestCase;

class TabControllerTest extends OxWebTestCase
{
    public function testListModuleTabs(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/modules/system/tabs');

        $this->assertResponseStatusCodeSame(200);

        $collection = $this->getJsonApiCollection($client);
        /** @var Item $item */
        foreach ($collection as $item) {
            $this->assertEquals('tab', $item->getType());
            $this->assertEquals('system', $item->getAttribute('mod_name'));
            $this->assertStringStartsWith('?m=system&tab=', $item->getLink('tab_url'));
        }
    }

    public function testListModuleTabsModuleNotInstalled(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/modules/lorem/tabs');

        $this->assertResponseStatusCodeSame(404);

        $error = $this->getJsonApiError($client);
        $this->assertEquals('The module lorem is not installed.', $error->getMessage());
    }

    public function testSetPinnedTabModuleNotActive(): void
    {
        $client = self::createClient();
        $client->request(
            'POST',
            '/api/modules/lorem/tabs',
            [],
            [],
            [],
            json_encode(new Collection([new Item('pinned_tab')]))
        );

        $this->assertResponseStatusCodeSame(404);

        $error = $this->getJsonApiError($client);
        $this->assertEquals('The module lorem is not active.', $error->getMessage());
    }

    public function testSetPinnedTab(): string
    {
        $tabs = new Collection(
            [
                (new Item('pinned_tab'))->setAttributes(['_tab_name' => 'about']),
                (new Item('pinned_tab'))->setAttributes(['_tab_name' => 'view_history']),
            ]
        );

        $client = self::createClient();
        $client->request('POST', '/api/modules/system/tabs', [], [], [], json_encode($tabs));

        $this->assertResponseStatusCodeSame(201);

        $collection = $this->getJsonApiCollection($client);

        $this->assertEquals(2, $collection->getMeta('count'));

        /** @var Item $item */
        foreach ($collection as $item) {
            $this->assertEquals('pinned_tab', $item->getType());
            $this->assertNotNull($item->getId());
            $this->assertEquals('system', $item->getAttribute('_mod_name'));
            $this->assertTrue(in_array($item->getAttribute('_tab_name'), ['about', 'view_history']));
        }

        return 'system';
    }

    /**
     * @depends testSetPinnedTab
     */
    public function testShowPinnedTab(string $mod_name): void
    {
        $client = self::createClient();
        $client->request('GET', "/api/modules/$mod_name/pin");

        $this->assertResponseStatusCodeSame(200);

        $collection = $this->getJsonApiCollection($client);

        $this->assertEquals(2, $collection->getMeta('count'));

        /** @var Item $item */
        foreach ($collection as $item) {
            $this->assertEquals('pinned_tab', $item->getType());
            $this->assertNotNull($item->getId());
            $this->assertEquals('system', $item->getAttribute('_mod_name'));
            $this->assertTrue(in_array($item->getAttribute('_tab_name'), ['about', 'view_history']));
        }
    }

    public function testGetPinnedTabModuleNotActive(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/modules/lorem/pin');

        $this->assertResponseStatusCodeSame(404);

        $error = $this->getJsonApiError($client);
        $this->assertEquals('The module lorem is not active.', $error->getMessage());
    }
}
