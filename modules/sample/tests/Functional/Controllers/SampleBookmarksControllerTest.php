<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Tests\Functional\Controllers;

use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Sample\Entities\CSampleBookmark;
use Ox\Mediboard\Sample\Entities\CSampleCasting;
use Ox\Mediboard\Sample\Entities\CSampleCategory;
use Ox\Mediboard\Sample\Entities\CSampleMovie;
use Ox\Mediboard\Sample\Entities\CSamplePerson;
use Ox\Mediboard\Sample\Tests\Fixtures\SampleBookmarkFixtures;
use Ox\Mediboard\Sample\Tests\Fixtures\SampleUtilityFixtures;
use Ox\Mediboard\Sample\Tests\Fixtures\SampleMovieFixtures;
use Ox\Mediboard\Sample\Tests\Fixtures\SamplePersonFixtures;
use Ox\Tests\JsonApi\Item;
use Ox\Tests\OxWebTestCase;
use Ox\Tests\TestsException;

class SampleBookmarksControllerTest extends OxWebTestCase
{
    public function testList(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/sample/bookmarks', ['relations' => 'user,movie', 'limit' => 2]);

        $this->assertResponseStatusCodeSame(200);

        $collection = $this->getJsonApiCollection($client);

        $this->assertGreaterThan(0, $collection->getMeta('count'));

        /** @var Item $item */
        foreach ($collection as $item) {
            $this->assertEquals(CSampleBookmark::RESOURCE_TYPE, $item->getType());
            $this->assertNotNull($item->getId());

            $this->assertTrue($item->hasRelationship('user'));
            $this->assertTrue($item->hasRelationship('movie'));

            /** @var Item $user */
            $user = $item->getRelationship('user');
            $this->assertEquals(CUser::get()->_id, $user->getId());
            $this->assertEquals(CMediusers::RESOURCE_TYPE, $user->getType());

            /** @var Item $movie */
            $movie = $item->getRelationship('movie');
            $this->assertEquals(CSampleMovie::RESOURCE_TYPE, $movie->getType());
            $this->assertNotNull($movie->getId());
        }
    }

    public function testAddNoData(): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/sample/bookmarks', ['HTTP_Content-Type' => 'application/vnd.api+json']);

        $this->assertResponseStatusCodeSame(500);

        $error = $this->getJsonApiError($client);
        $this->assertEquals(
            'The body content is not a valid JsonApi or the header Content-Type is not valid',
            $error->getMessage()
        );
    }

    public function testAdd(): int
    {
        $movie = $this->createMovie();

        $item = new Item('sample_bookmark', null);
        $item->setRelationships(['movie' => ['data' => ['type' => 'sample_movie', 'id' => $movie->_id]]]);

        $client = self::createClient();
        $client->request('POST', '/api/sample/bookmarks', [], [], [], json_encode($item));

        $this->assertResponseStatusCodeSame(201);

        $collection = $this->getJsonApiCollection($client);

        $this->assertEquals(1, $collection->getMeta('count'));

        $this->assertEquals('sample_bookmark', $collection->getFirstItem()->getType());

        $bookmark_id = $collection->getFirstItem()->getId();
        $this->assertNotNull($bookmark_id);

        return $bookmark_id;
    }

    /**
     * @depends testAdd
     */
    public function testDelete(int $bookmark_id): void
    {
        CSampleBookmark::findOrFail($bookmark_id);

        $client = self::createClient();
        $client->request('DELETE', '/api/sample/bookmarks/' . $bookmark_id);

        $this->assertResponseStatusCodeSame(204);

        $this->assertEmpty($client->getResponse()->getContent());

        $this->assertFalse(CSampleBookmark::find($bookmark_id));
    }

    private function createMovie(): CSampleMovie
    {
        $category = $this->getObjectFromFixturesReference(CSampleCategory::class, SampleUtilityFixtures::CATEGORY);
        $director = $this->getObjectFromFixturesReference(CSamplePerson::class, SamplePersonFixtures::DIRECTOR_TAG);

        $movie              = CSampleMovie::getSampleObject();
        $movie->category_id = $category->_id;
        $movie->director_id = $director->_id;

        if ($msg = $movie->store()) {
            throw new TestsException($msg);
        }

        return $movie;
    }
}
