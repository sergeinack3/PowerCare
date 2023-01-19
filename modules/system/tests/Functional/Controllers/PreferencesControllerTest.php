<?php

/**
 * @author  SARL OpenXtrem <dev@openxtrem.com>
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

namespace Ox\Mediboard\System\Tests\Functional\Controllers;

use Exception;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CPreferences;
use Ox\Tests\JsonApi\Item;
use Ox\Tests\OxWebTestCase;
use Ox\Tests\TestsException;

class PreferencesControllerTest extends OxWebTestCase
{
    private const PREF = 'TESTPREFDEFAULT';

    /**
     * @throws TestsException
     */
    public function testListAllModulesPreferences(): void
    {
        $client = static::createClient();
        $client->request(
            'GET',
            "/api/preferences/all",
        );

        $this->assertResponseIsSuccessful();
        $item = $this->getJsonApiItem($client);

        $this->assertEquals(CPreferences::RESOURCE_TYPE . 's', $item->getType());
    }

    /**
     * @throws TestsException
     */
    public function testListPreferencesModuleNotExist(): void
    {
        $mod_name = 'loremIpsum';

        $client = static::createClient();
        $client->request(
            'GET',
            "/api/preferences/{$mod_name}",
        );

        $this->assertResponseStatusCodeSame(404);

        $error = $this->getJsonApiError($client);
        $this->assertStringStartsWith("Module 'dP{$mod_name}' does not exists or is not active", $error->getMessage());
    }

    /**
     * @throws TestsException
     * @throws Exception
     */
    public function testListAllModulesUserPreferences(): void
    {
        $client = static::createClient();
        $client->request(
            'GET',
            "/api/preferences/all/" . CMediusers::get()->_id,
        );

        $this->assertResponseIsSuccessful();
        $item = $this->getJsonApiItem($client);

        $this->assertEquals(CPreferences::RESOURCE_TYPE . 's', $item->getType());
    }

    /**
     * @throws TestsException
     * @throws Exception
     */
    public function testSetPreferencesWithoutDefault(): void
    {
        $item = new Item(CPreferences::RESOURCE_TYPE, null);
        $item->setAttributes(
            [
                'key'   => self::PREF . uniqid(),
                'value' => 1,
            ]
        );

        $client = self::createClient();
        $client->request(
            'POST',
            '/api/preferences/' . CMediusers::get()->_id,
            [],
            [],
            [],
            json_encode($item)
        );

        $this->assertResponseStatusCodeSame(404);

        $error = $this->getJsonApiError($client);
        $this->assertStringStartsWith(
            "Les préférences suivantes ne possèdent pas de valeur par défaut",
            $error->getMessage()
        );
    }

    /**
     * @throws TestsException
     */
    public function testSetDefaultPreferences(): void
    {
        $item = new Item(CPreferences::RESOURCE_TYPE, null);
        $item->setAttributes(
            [
                'key'   => self::PREF,
                'value' => 1,
            ]
        );

        $client = self::createClient();
        $client->request(
            'POST',
            '/api/preferences/',
            [],
            [],
            [],
            json_encode($item)
        );

        $this->assertResponseStatusCodeSame(201);
        $item = $this->getJsonApiCollection($client)->getFirstItem();

        $this->assertEquals(CPreferences::RESOURCE_TYPE, $item->getType());
        $this->assertEquals(self::PREF, $item->getAttribute('key'));
        $this->assertEquals(1, $item->getAttribute('value'));
    }

    /**
     * @throws TestsException
     * @throws Exception
     */
    public function testSetUserPreferences(): void
    {
        $item = new Item(CPreferences::RESOURCE_TYPE, null);
        $item->setAttributes(
            [
                'key'   => self::PREF,
                'value' => 1,
            ]
        );

        $client = self::createClient();
        $client->request(
            'POST',
            '/api/preferences/' . CMediusers::get()->_id,
            [],
            [],
            [],
            json_encode($item)
        );

        $this->assertResponseStatusCodeSame(201);
        $item = $this->getJsonApiCollection($client)->getFirstItem();

        $this->assertEquals(CPreferences::RESOURCE_TYPE, $item->getType());
        $this->assertEquals(self::PREF, $item->getAttribute('key'));
        $this->assertEquals(1, $item->getAttribute('value'));
    }

    /**
     * @throws TestsException
     * @throws Exception
     */
    public function testDeleteDefaultPreferenceNotExist(): void
    {
        $pref = self::PREF . uniqid();

        $item = new Item(CPreferences::RESOURCE_TYPE, null);
        $item->setAttributes(
            [
                'key' => $pref,
            ]
        );

        $client = self::createClient();
        $client->request(
            'DELETE',
            '/api/preferences/' . CMediusers::get()->_id,
            [],
            [],
            [],
            json_encode($item)
        );

        $this->assertResponseStatusCodeSame(404);

        $error = $this->getJsonApiError($client);
        $this->assertStringStartsWith("Les préférences suivantes n'existe pas : '{$pref}'", $error->getMessage());
    }

    /**
     * @throws TestsException
     * @throws Exception
     */
    public function testDeleteDefaultPreferenceWithoutUserId(): void
    {
        $pref = self::PREF;

        $item = new Item(CPreferences::RESOURCE_TYPE, null);
        $item->setAttributes(
            [
                'key' => $pref,
            ]
        );

        $client = self::createClient();
        $client->request(
            'DELETE',
            '/api/preferences/',
            [],
            [],
            [],
            json_encode($item)
        );

        $this->assertResponseStatusCodeSame(500);

        $error = $this->getJsonApiError($client);
        $this->assertStringStartsWith(
            "Vous ne pouvez pas supprimer de préférences par défaut",
            $error->getMessage()
        );
    }

    /**
     * @throws TestsException
     * @throws Exception
     */
    public function testDeletePreference(): void
    {
        $pref = self::PREF;

        $item = new Item(CPreferences::RESOURCE_TYPE, null);
        $item->setAttributes(
            [
                'key' => $pref,
            ]
        );

        $client = self::createClient();
        $client->request(
            'DELETE',
            '/api/preferences/' . CMediusers::get()->_id,
            [],
            [],
            [],
            json_encode($item)
        );

        $this->assertResponseStatusCodeSame(204);
    }
}
