<?php

/**
 * @author  SARL OpenXtrem <dev@openxtrem.com>
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

namespace Ox\Mediboard\System\Tests\Functional\Controllers;

use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\CConfiguration;
use Ox\Tests\JsonApi\Item;
use Ox\Tests\OxWebTestCase;
use Ox\Tests\TestsException;

class ConfigurationControllerTest extends OxWebTestCase
{
    private const CONFIG = 'dPfiles General upload_max_filesize';

    /**
     * @throws TestsException
     */
    public function testListModuleConfigurations(): void
    {
        $client = static::createClient();
        $client->request(
            'GET',
            "/api/configurations/system",
        );

        $this->assertResponseIsSuccessful();
        $item = $this->getJsonApiItem($client);

        $this->assertEquals(CConfiguration::RESOURCE_TYPE . 's', $item->getType());
        $this->assertTrue($item->hasAttribute('instance'));
        $this->assertTrue($item->hasAttribute('static'));
        $this->assertTrue($item->hasAttribute('context'));
    }

    /**
     * @throws TestsException
     */
    public function testListConfigurationModuleNotExist(): void
    {
        $mod_name = 'loremIpsum';

        $client = static::createClient();
        $client->request(
            'GET',
            "/api/configurations/{$mod_name}",
        );

        $this->assertResponseStatusCodeSame(404);

        $error = $this->getJsonApiError($client);
        $this->assertStringStartsWith("Module 'dP{$mod_name}' does not exists or is not active", $error->getMessage());
    }

    /**
     * @throws TestsException
     */
    public function testGetConfigurations(): string
    {
        $item = new Item(CConfiguration::RESOURCE_TYPE, null);
        $item->setAttributes(
            [
                'feature' => self::CONFIG,
            ]
        );

        $client = self::createClient();
        $client->request(
            'GET',
            '/api/configurations',
            ['context' => CGroups::loadCurrent()->_guid],
            [],
            [],
            json_encode($item)
        );

        $this->assertResponseIsSuccessful();
        $item = $this->getJsonApiCollection($client)->getFirstItem();

        $this->assertEquals(CConfiguration::RESOURCE_TYPE, $item->getType());
        $this->assertTrue($item->hasAttribute(self::CONFIG));

        // Return old value to prevent future errors and restore config as his original state
        return $item->getAttribute(self::CONFIG);
    }

    /**
     * @param string $old_value Old config value
     *
     * @return void
     * @throws TestsException
     *
     * @depends testGetConfigurations
     */
    public function testPutConfiguration(string $old_value): void
    {
        $item = new Item(CConfiguration::RESOURCE_TYPE, null);
        $item->setAttributes(
            [
                'feature' => self::CONFIG,
                'value'   => $old_value,
            ]
        );

        $client = self::createClient();
        $client->request(
            'PUT',
            '/api/configurations?context=' . CGroups::loadCurrent()->_guid,
            [],
            [],
            [],
            json_encode($item)
        );

        $this->assertResponseStatusCodeSame(200);
        $item = $this->getJsonApiCollection($client)->getFirstItem();

        $this->assertEquals(CConfiguration::RESOURCE_TYPE, $item->getType());
        $this->assertEquals(self::CONFIG, $item->getAttribute('feature'));
        $this->assertEquals($old_value, $item->getAttribute('value'));
    }
}
