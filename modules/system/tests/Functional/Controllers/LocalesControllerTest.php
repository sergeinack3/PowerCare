<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Functional\Controllers;

use Ox\Tests\OxWebTestCase;
use Ox\Tests\TestsException;

class LocalesControllerTest extends OxWebTestCase
{
    /**
     * @throws TestsException
     */
    public function testModuleDoesNotExists(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/locales/fr/toto');

        $this->assertResponseStatusCodeSame(404);

        $error = $this->getJsonApiError($client);
        $this->assertStringStartsWith("Module 'dPtoto' does not exists or is not active", $error->getMessage());
    }

    /**
     * @throws TestsException
     */
    public function testResponseIsOk(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/locales/fr/system');

        $this->assertResponseStatusCodeSame(200);

        $item = $this->getJsonApiItem($client);

        $this->assertEquals('locales', $item->getType());
        $this->assertNotEmpty($item->getAttributes());
    }

    /**
     * @throws TestsException
     */
    public function testResponseIsOkWithDp(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/locales/fr/bloc');

        $this->assertResponseStatusCodeSame(200);

        $item = $this->getJsonApiItem($client);

        $this->assertEquals('locales', $item->getType());
        $this->assertNotEmpty($item->getAttributes());
    }

    /**
     * @throws TestsException
     */
    public function testFilterEmptyResult(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/locales/fr/system', ['filter' => 'key.contains.' . uniqid()]);

        $this->assertResponseStatusCodeSame(200);

        $item = $this->getJsonApiItem($client);
        $this->assertEmpty($item->getAttributes());
    }

    /**
     * @throws TestsException
     */
    public function testFilterOk(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/locales/fr/system', ['filter' => 'key.contains.CAbon']);

        $this->assertResponseStatusCodeSame(200);

        $item = $this->getJsonApiItem($client);
        foreach ($item->getAttributes() as $name => $value) {
            $this->assertStringContainsString('CAbon', $name);
        }
    }
}
