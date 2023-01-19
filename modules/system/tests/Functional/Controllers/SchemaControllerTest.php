<?php
/**
 * @author  SARL OpenXtrem <dev@openxtrem.com>
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

namespace Ox\Mediboard\System\Tests\Functional\Controllers;

use Ox\Tests\OxWebTestCase;

class SchemaControllerTest extends OxWebTestCase
{
    public function testModelSchema(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/schemas/module');

        $this->assertResponseIsSuccessful();
        $collection = $this->getJsonApiCollection($client);
        $this->assertEquals($collection->getFirstItem()->getType(), 'schema');
        $this->assertEquals($collection->getFirstItem()->getAttribute('owner'), 'module');
    }

    public function testRouteSchema(): void
    {
        $client = static::createClient();
        $path   = "L2FwaS9tb2R1bGVz"; // /api/modules

        $client->request('GET', '/api/routes/' . $path . '/get');
        $this->assertResponseIsSuccessful();
        $item = $this->getJsonApiItem($client);

        $this->assertEquals($item->getType(), 'route_schema');
    }

    public function testRouteSchemaKo(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/routes/L2FwadeS978b2R1bG/get');
        $this->assertResponseStatusCodeSame('500');
        $error = $this->getJsonApiError($client);
        $this->assertStringStartsWith("Undefined path in OAS :", $error->getMessage());
    }
}
