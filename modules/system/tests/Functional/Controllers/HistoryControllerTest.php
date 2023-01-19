<?php
/**
 * @author  SARL OpenXtrem <dev@openxtrem.com>
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

namespace Ox\Mediboard\System\Tests\Functional\Controllers;

use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Tests\JsonApi\Item;
use Ox\Tests\OxWebTestCase;
use Ox\Tests\TestsException;

class HistoryControllerTest extends OxWebTestCase
{
    public function testListHistory(): Item
    {
        /** @var CUser $user */
        $user = $this->getObjectFromFixturesReference(CUser::class, UsersFixtures::REF_USER_LOREM_IPSUM);
        $uri  = "/api/history/user/{$user->_id}";

        $client = static::createClient();
        $client->request('GET', $uri);
        $this->assertResponseIsSuccessful();
        $collection    = $this->getJsonApiCollection($client);
        $count_history = $collection->count();

        $user->user_address1 = uniqid('adresse1_');
        $user->store();

        $client->request('GET', $uri);
        $collection = $this->getJsonApiCollection($client);
        $this->assertGreaterThan($count_history, $collection->count());

        return $collection->getFirstItem();
    }

    /**
     * @depends testListHistory
     * @throws TestsException
     */
    public function testShowHistory(Item $item_depends): void
    {
        $user = $this->getObjectFromFixturesReference(CUser::class, UsersFixtures::REF_USER_LOREM_IPSUM);
        $uri  = "/api/history/user/{$user->_id}/{$item_depends->getId()}";

        $client = static::createClient();
        $client->request('GET', $uri);

        $item = $this->getJsonApiItem($client);
        $this->assertTrue($item->hasRelationship('user'));
    }

}
