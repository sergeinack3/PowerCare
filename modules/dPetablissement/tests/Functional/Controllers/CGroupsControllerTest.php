<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Etablissement\Tests\Functional\Controllers;

use Exception;
use Ox\Core\CMbModelNotFoundException;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Tests\JsonApi\Item;
use Ox\Tests\OxWebTestCase;
use Ox\Tests\TestsException;

/**
 * CGroups API controller tests
 */
class CGroupsControllerTest extends OxWebTestCase
{
    /**
     * @throws TestsException
     */
    public function testCreateGroups(): int
    {
        $item = new Item(CGroups::RESOURCE_TYPE, null);
        $item->setAttributes(
            [
                '_name' => 'Etablissement de test API',
                'code'  => 'etab_test_api',
            ]
        );

        $client = self::createClient();
        $client->request('POST', '/api/groups', [], [], [], json_encode($item));

        $this->assertResponseStatusCodeSame(201);
        $collection = $this->getJsonApiCollection($client);

        $this->assertEquals(1, $collection->getMeta('count'));
        $this->assertEquals(CGroups::RESOURCE_TYPE, $collection->getFirstItem()->getType());

        $group_id = $collection->getFirstItem()->getId();
        $this->assertNotNull($group_id);

        return $group_id;
    }

    /**
     * @throws TestsException
     */
    public function testListGroupsWithRoles(): void
    {
        $client = static::createClient();
        $client->request(
            'GET',
            "/api/groups",
            ['with_roles' => true]
        );

        $this->assertResponseIsSuccessful();
        $collection = $this->getJsonApiCollection($client);

        $this->assertGreaterThan(0, $collection->getMeta('count'));

        /** @var Item $item */
        foreach ($collection as $item) {
            $this->assertEquals(CGroups::RESOURCE_TYPE, $item->getType());
            $this->assertTrue($item->hasAttribute('is_main'));
            $this->assertTrue($item->hasAttribute('is_secondary'));
        }
    }

    /**
     * @param int $group_id
     *
     * @return void
     * @throws CMbModelNotFoundException
     * @throws TestsException
     *
     * @depends testCreateGroups
     */
    public function testShowGroup(int $group_id): void
    {
        CGroups::findOrFail($group_id);

        $client = static::createClient();
        $client->request(
            'GET',
            "/api/groups/{$group_id}"
        );

        $this->assertResponseIsSuccessful();
        $item = $this->getJsonApiItem($client);

        $this->assertEquals(CGroups::RESOURCE_TYPE, $item->getType());
        $this->assertEquals($group_id, $item->getId());
    }

    /**
     * @throws TestsException
     */
    public function testShowGroupNotExist(): void
    {
        $client = static::createClient();
        $client->request(
            'GET',
            "/api/groups/99999999"
        );

        $this->assertResponseStatusCodeSame(404);

        $error = $this->getJsonApiError($client);
        $this->assertStringStartsWith("Objet non trouvé", $error->getMessage());
    }

    /**
     * @param int $group_id
     *
     * @return void
     * @throws TestsException
     * @throws CMbModelNotFoundException
     * @throws Exception
     *
     * @depends testCreateGroups
     */
    public function testUpdateGroup(int $group_id): int
    {
        CGroups::findOrFail($group_id);
        $text = 'Test raison sociale';

        $item = new Item(CGroups::RESOURCE_TYPE, null);
        $item->setAttributes(
            [
                'raison_sociale' => $text,
            ]
        );

        $client = self::createClient();
        $client->request('PUT', "/api/groups/{$group_id}", [], [], [], json_encode($item));

        $this->assertResponseStatusCodeSame(200);

        $this->assertEquals(CGroups::RESOURCE_TYPE, $item->getType());
        $this->assertEquals($text, $item->getAttribute('raison_sociale'));

        return $group_id;
    }

    /**
     * @param int $group_id
     *
     * @return int
     * @throws CMbModelNotFoundException
     * @throws TestsException
     *
     * @depends testCreateGroups
     */
    public function testCreateFunctions(int $group_id): int
    {
        CGroups::findOrFail($group_id);

        $item = new Item(CFunctions::RESOURCE_TYPE, null);
        $item->setAttributes(
            [
                'text' => 'Fonction de test API',
                'type' => 'administratif',
                'color' => 'FFFFFF',
            ]
        );

        $client = self::createClient();
        $client->request('POST', "/api/groups/{$group_id}/functions", [], [], [], json_encode($item));

        $this->assertResponseStatusCodeSame(201);
        $collection = $this->getJsonApiCollection($client);

        $this->assertEquals(1, $collection->getMeta('count'));
        $this->assertEquals(CFunctions::RESOURCE_TYPE, $collection->getFirstItem()->getType());

        $function_id = $collection->getFirstItem()->getId();
        $this->assertNotNull($function_id);

        return $function_id;
    }

    /**
     * @param int $function_id
     *
     * @return void
     * @throws CMbModelNotFoundException
     * @throws TestsException
     *
     * @depends testCreateFunctions
     */
    public function testListFunctions(int $function_id): void
    {
        $function = CFunctions::findOrFail($function_id);

        $client = static::createClient();
        $client->request(
            'GET',
            "/api/groups/{$function->group_id}/functions"
        );

        $this->assertResponseIsSuccessful();
        $collection = $this->getJsonApiCollection($client);

        $this->assertGreaterThan(0, $collection->getMeta('count'));

        /** @var Item $item */
        foreach ($collection as $item) {
            $this->assertEquals(CFunctions::RESOURCE_TYPE, $item->getType());
            $this->assertTrue($item->hasAttribute('text'));
        }
    }

    /**
     * @param int $function_id
     *
     * @return void
     * @throws CMbModelNotFoundException
     * @throws TestsException
     *
     * @depends testCreateFunctions
     */
    public function testShowFunction(int $function_id): void
    {
        CFunctions::findOrFail($function_id);

        $client = static::createClient();
        $client->request(
            'GET',
            "/api/groups/functions/{$function_id}"
        );

        $this->assertResponseIsSuccessful();
        $item = $this->getJsonApiItem($client);

        $this->assertEquals(CFunctions::RESOURCE_TYPE, $item->getType());
        $this->assertEquals($function_id, $item->getId());
    }

    /**
     * @throws TestsException
     */
    public function testShowFunctionNotExist(): void
    {
        $client = static::createClient();
        $client->request(
            'GET',
            "/api/groups/functions/99999999"
        );

        $this->assertResponseStatusCodeSame(404);

        $error = $this->getJsonApiError($client);
        $this->assertStringStartsWith("Objet non trouvé", $error->getMessage());
    }

    /**
     * @param int $function_id
     *
     * @return void
     * @throws TestsException
     * @throws CMbModelNotFoundException
     * @throws Exception
     *
     * @depends testCreateFunctions
     */
    public function testUpdateFunction(int $function_id): int
    {
        CFunctions::findOrFail($function_id);
        $text = 'Fonction de test API edited';

        $item = new Item(CFunctions::RESOURCE_TYPE, null);
        $item->setAttributes(
            [
                'text' => $text,
            ]
        );

        $client = self::createClient();
        $client->request('PUT', "/api/groups/functions/{$function_id}", [], [], [], json_encode($item));

        $this->assertResponseStatusCodeSame(200);

        $this->assertEquals(CFunctions::RESOURCE_TYPE, $item->getType());
        $this->assertEquals($text, $item->getAttribute('text'));

        return $function_id;
    }

    /**
     * @param int $function_id
     *
     * @return void
     * @throws TestsException
     * @throws CMbModelNotFoundException
     * @throws Exception
     *
     * @depends testUpdateFunction
     */
    public function testDeleteFunction(int $function_id): void
    {
        CFunctions::findOrFail($function_id);

        $client = self::createClient();
        $client->request('DELETE', "/api/groups/functions/{$function_id}");

        $this->assertResponseStatusCodeSame(204);

        $this->assertEmpty($client->getResponse()->getContent());

        $this->assertFalse(CFunctions::find($function_id));
    }

    /**
     * @param int $group_id
     *
     * @return void
     * @throws TestsException
     * @throws CMbModelNotFoundException
     * @throws Exception
     *
     * @depends testUpdateGroup
     */
    public function testDeleteGroup(int $group_id): void
    {
        CGroups::findOrFail($group_id);

        $client = self::createClient();
        $client->request('DELETE', '/api/groups/' . $group_id);

        $this->assertResponseStatusCodeSame(204);

        $this->assertEmpty($client->getResponse()->getContent());

        $this->assertFalse(CGroups::find($group_id));
    }
}
