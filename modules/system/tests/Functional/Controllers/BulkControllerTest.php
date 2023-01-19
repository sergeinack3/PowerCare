<?php
/**
 * @author  SARL OpenXtrem <dev@openxtrem.com>
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

namespace Ox\Mediboard\System\Tests\Functional\Controllers;

use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Tests\JsonApi\Collection;
use Ox\Tests\JsonApi\Error;
use Ox\Tests\OxWebTestCase;

class BulkControllerTest extends OxWebTestCase
{
    public function testMissingContent(): void
    {
        $client = static::createClient();
        $client->request('POST', 'api/bulkOperations');
        $error = $this->getJsonApiError($client);
        $this->assertEquals($error->getMessage(), "Missing json content");
    }

    public function testUnauthorizedHeader(): void
    {
        $client       = static::createClient();
        $json_content = json_encode(["data" => []]);
        $client->request('POST', 'api/bulkOperations', [], [], [
            'HTTP_X-BULK-OPERATION' => 'true',
        ],               $json_content);
        $error = $this->getJsonApiError($client);
        $this->assertEquals($error->getMessage(), "Unauthorized bulk operations on sub request");
    }


    public function testBulk(): void
    {
        $client          = static::createClient();
        $request_to_bulk = $this->getBulkRequests();
        $client->request('POST', 'api/bulkOperations', [], [], [], json_encode($request_to_bulk));
        $content = $this->getResponseContent($client);

        $this->assertCount(3, $content);

        foreach ($content as $key => $response) {
            $this->assertEquals($response['id'], $request_to_bulk['data'][$key]['id']);
            $this->assertArrayHasKey('status', $response);
        }

        $collection = Collection::createFromArray($content[2]['body']);
        $this->assertGreaterThanOrEqual(1, $collection->count());
    }

    public function testBulkStopOnFailure(): void
    {
        $client          = static::createClient();
        $request_to_bulk = $this->getBulkRequests();
        $client->request('POST', 'api/bulkOperations', [
            'stopOnFailure' => true,
        ],               [], [], json_encode($request_to_bulk));
        $content = $this->getResponseContent($client);
        $this->assertCount(1, $content);
        $error = Error::createFromArray(reset($content)['body']);
        $this->assertStringStartsWith("No route found for", $error->getMessage());
    }

    private function getBulkRequests(): array
    {
        $data['data'] = [];

        $data['data'][] = [
            'id'     => uniqid(),
            'method' => 'GET',
            'path'   => '/api/lorem/ipsum/dolor/set',
        ];

        $group          = $this->getObjectFromFixturesReference(CGroups::class, UsersFixtures::REF_FIXTURES_GROUP);
        $data['data'][] = [
            'id'     => uniqid(),
            'method' => 'GET',
            'path'   => '/api/groups/' . $group->_id,
        ];

        $data['data'][] = [
            'id'         => uniqid(),
            'method'     => 'GET',
            'path'       => '/api/groups/' . $group->_id . '/functions',
            'parameters' => [
                'limit' => 2,
            ],
        ];

        return $data;
    }
}
