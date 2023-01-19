<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Tests\Functional\Controllers;

use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CStoredObject;
use Ox\Core\Locales\Translator;
use Ox\Mediboard\Sample\Entities\CSampleCategory;
use Ox\Mediboard\Sample\Entities\CSampleMovie;
use Ox\Mediboard\Sample\Entities\CSamplePerson;
use Ox\Mediboard\Sample\Tests\Fixtures\SampleUtilityFixtures;
use Ox\Mediboard\Sample\Tests\Fixtures\SampleMovieFixtures;
use Ox\Mediboard\Sample\Tests\Fixtures\SamplePersonFixtures;
use Ox\Tests\JsonApi\Collection;
use Ox\Tests\JsonApi\Item;
use Ox\Tests\OxWebTestCase;
use Ox\Tests\TestsException;

class SampleMoviesControllerTest extends OxWebTestCase
{
    /**
     * @throws TestsException
     */
    public function testListMovies(): void
    {
        $client = $this->createClient();
        $client->request('GET', '/api/sample/movies', ['limit' => 3, 'relations' => 'all']);

        $this->assertResponseStatusCodeSame(200);

        $collection = $this->getJsonApiCollection($client);

        $this->assertEquals(3, $collection->getMeta('count'));

        /** @var Item $item */
        foreach ($collection as $item) {
            $this->assertEquals('sample_movie', $item->getType());
            $this->assertNotNull($item->getId());

            $this->assertTrue($item->hasRelationship('director'));
            $this->assertEquals('sample_person', $item->getRelationship('director')->getType());
            $this->assertNotNull($item->getRelationship('director')->getId());

            $this->assertTrue($item->hasRelationship('category'));
            $this->assertEquals('sample_category', $item->getRelationship('category')->getType());
            $this->assertNotNull($item->getRelationship('category')->getId());

            $this->assertTrue($item->hasRelationship('bookmarks'));
            $this->assertTrue($item->hasRelationship('cover'));
        }
    }

    /**
     * @throws TestsException
     */
    public function testListMoviesWithNationality(): void
    {
        $dir         = $this->getObjectFromFixturesReference(CSamplePerson::class, SamplePersonFixtures::DIRECTOR_TAG);
        $nationality = $dir->loadFwdRef('nationality_id');

        $client = self::createClient();
        $client->request(
            'GET',
            '/api/sample/movies',
            ['nationality_id' => $nationality->_id, 'limit' => 5, 'relations' => 'director']
        );

        $this->assertResponseStatusCodeSame(200);

        $collection = $this->getJsonApiCollection($client);
        $this->assertTrue(0 < $collection->getMeta('count'));

        $person_ids = [];
        /** @var Item $director */
        foreach ($collection->getIncluded() as $director) {
            $person_ids[] = $director->getId();
        }

        $persons = (new CSamplePerson())->loadAll($person_ids);
        CStoredObject::massLoadFwdRef($persons, 'nationality_id');

        foreach ($persons as $person) {
            $this->assertEquals($nationality->_id, $person->nationality_id);
        }
    }

    /**
     * @throws TestsException
     */
    public function testListMoviesWithSearch(): void
    {
        $client = $this->createClient();
        $client->request('GET', '/api/sample/movies', ['search' => 'batman', 'fieldsets' => 'default,details']);

        $this->assertResponseStatusCodeSame(200);

        /** @var Item $item */
        foreach ($this->getJsonApiCollection($client) as $item) {
            $film_valid = false;
            if (
                str_contains(strtolower($item->getAttribute('name')), 'batman')
                || str_contains(strtolower($item->getAttribute('description')), 'batman')
            ) {
                $film_valid = true;
            }

            $this->assertTrue($film_valid);
        }
    }

    /**
     * @throws TestsException
     */
    public function testListMoviesMostBookmarked(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/sample/movies', ['top_bookmarked' => 10]);

        $this->assertResponseStatusCodeSame(200);

        $collection = $this->getJsonApiCollection($client);

        $last_total = null;
        /** @var Item $item */
        foreach ($collection as $item) {
            $this->assertTrue($item->hasMeta('bookmarked_count'));

            $total = $item->getMeta('bookmarked_count');
            if ($last_total !== null) {
                $this->assertTrue($last_total >= $total);
            }

            $last_total = $total;
        }
    }

    /**
     * @throws TestsException
     */
    public function testGetMovie(): void
    {
        /** @var CSampleMovie $movie */
        $movie = $this->getObjectFromFixturesReference(CSampleMovie::class, SampleMovieFixtures::MOVIE_1);

        $client = $this->createClient();
        $client->request('GET', '/api/sample/movies/' . $movie->_id);

        $this->assertResponseStatusCodeSame(200);

        $item = $this->getJsonApiItem($client);
        $this->assertEquals($movie->_id, $item->getId());
        $this->assertEquals('sample_movie', $item->getType());
        $this->assertEquals($movie->name, $item->getAttribute('name'));
        $this->assertEquals($movie->release, $item->getAttribute('release'));
        $this->assertEquals($movie->duration, $item->getAttribute('duration'));
        $this->assertEquals($movie->languages, $item->getAttribute('languages'));

        // Check links
        $this->assertEquals('/api/sample/movies/' . $movie->_id, $item->getLink('self'));
        $this->assertEquals('/api/schemas/sample_movie', $item->getLink('schema'));
        $this->assertEquals('/api/history/sample_movie/' . $movie->_id, $item->getLink('history'));
    }

    /**
     * @throws TestsException
     */
    public function testGetMovieDoesNotExists(): void
    {
        $client = $this->createClient();
        $client->request('GET', '/api/sample/movies/' . PHP_INT_MAX);

        $this->assertResponseStatusCodeSame(404);

        $error = $this->getJsonApiError($client);
        $this->assertEquals('Objet non trouvé', $error->getMessage());
    }

    /**
     * @throws TestsException
     */
    public function testCreateMovieFailed(): void
    {
        $item = (new Item('sample_movie'))
            ->setAttributes(['name' => 'test', 'release' => '2000-01-01']);

        $client = $this->createClient();
        $client->request('POST', '/api/sample/movies', [], [], [], json_encode($item));

        $this->assertResponseStatusCodeSame(500);

        $error = $this->getJsonApiError($client);
        $this->assertStringContainsString(
            "<strong title='duration'>Durée</strong> : Ne peut pas avoir une valeur nulle",
            $error->getMessage()
        );
        $this->assertStringContainsString(
            "<strong title='category_id'>Catégorie</strong> : Ne peut pas avoir une valeur nulle",
            $error->getMessage()
        );
    }

    /**
     * @throws TestsException|CMbModelNotFoundException
     */
    public function testCreateMovie(): CSampleMovie
    {
        $director = $this->getObjectFromFixturesReference(CSamplePerson::class, SamplePersonFixtures::DIRECTOR_TAG);
        $category = $this->getObjectFromFixturesReference(CSampleCategory::class, SampleUtilityFixtures::CATEGORY);

        $item = (new Item('sample_movie'))
            ->setAttributes(
                [
                    'name'        => 'functional_test',
                    'release'     => '2000-01-01',
                    'duration'    => '01:00:00',
                    'description' => 'An interesting movie',
                ]
            )->setRelationships(
                [
                    'director' => new Item('sample_person', $director->_id),
                    'category' => new Item('sample_category', $category->_id),
                ]
            );

        $client = $this->createClient();
        $client->request(
            'POST',
            '/api/sample/movies?fieldsets=default,details&relations=director,category',
            [],
            [],
            [],
            json_encode($item)
        );

        $this->assertResponseStatusCodeSame(201);

        $item = $this->getJsonApiCollection($client)->getFirstItem();

        $this->assertEquals('sample_movie', $item->getType());
        $movie_id = $item->getId();
        $this->assertNotNull($movie_id);
        $this->assertEquals('functional_test', $item->getAttribute('name'));
        $this->assertEquals('2000-01-01', $item->getAttribute('release'));
        $this->assertEquals('01:00:00', $item->getAttribute('duration'));
        $this->assertEquals('An interesting movie', $item->getAttribute('description'));
        $this->assertEquals($director->_id, $item->getRelationship('director')->getId());
        $this->assertEquals($category->_id, $item->getRelationship('category')->getId());

        return CSampleMovie::findOrFail($movie_id);
    }

    /**
     * @depends testCreateMovie
     *
     * @throws TestsException
     */
    public function testUpdateMovie(CSampleMovie $movie): void
    {
        $new_category = $this->getObjectFromFixturesReference(
            CSampleCategory::class,
            SampleUtilityFixtures::CATEGORY_2
        );

        $item = (new Item('sample_movie', $movie->_id))
            ->setAttributes(['release' => '2000-01-02'])
            ->setRelationships(['category' => new Item('sample_category', $new_category->_id)]);

        $client = $this->createClient();
        $client->request('PATCH', "/api/sample/movies/$movie->_id?relations=category", [], [], [], json_encode($item));

        $this->assertResponseStatusCodeSame(200);

        $item = $this->getJsonApiItem($client);

        $this->assertEquals($movie->_id, $item->getId());
        $this->assertEquals('sample_movie', $item->getType());
        $this->assertEquals('2000-01-02', $item->getAttribute('release'));
        $this->assertEquals($new_category->_id, $item->getRelationship('category')->getId());
    }

    /**
     * @throws TestsException
     */
    public function testUpdateMovieFailed(): void
    {
        $client = $this->createClient();
        $client->request('PATCH', '/api/sample/movies/' . PHP_INT_MAX);

        $this->assertResponseStatusCodeSame(404);

        $error = $this->getJsonApiError($client);
        $this->assertEquals('Objet non trouvé', $error->getMessage());
    }

    /**
     * @throws TestsException
     */
    public function testDeleteMovie(): void
    {
        $director           = $this->getObjectFromFixturesReference(
            CSamplePerson::class,
            SamplePersonFixtures::DIRECTOR_TAG
        );
        $category           = $this->getObjectFromFixturesReference(
            CSampleCategory::class,
            SampleUtilityFixtures::CATEGORY
        );
        $movie              = CSampleMovie::getSampleObject();
        $movie->director_id = $director->_id;
        $movie->category_id = $category->_id;
        $this->storeOrFailed($movie);

        $client = $this->createClient();
        $client->request('DELETE', '/api/sample/movies/' . $movie->_id);

        $this->assertResponseStatusCodeSame(204);
        $this->assertEmpty($client->getResponse()->getContent());

        $this->assertFalse(CSampleMovie::find($movie->_id));
    }

    /**
     * @depends testCreateMovie
     *
     * @throws TestsException
     */
    public function testListEmptyCasting(CSampleMovie $movie): void
    {
        $client = $this->createClient();
        $client->request('GET', "/api/sample/movies/{$movie->_id}/casting");

        $this->assertResponseStatusCodeSame(200);

        $collection = $this->getJsonApiCollection($client);

        $this->assertEmpty($collection);
        $this->assertEquals(0, $collection->getLink('count'));
    }

    /**
     * @throws TestsException
     */
    public function testListCasting(): void
    {
        $movie = $this->getObjectFromFixturesReference(CSampleMovie::class, SampleMovieFixtures::MOVIE_1);

        $client = self::createClient();
        $client->request('GET', "/api/sample/movies/{$movie->_id}/casting", ['relations' => 'actor']);

        $this->assertResponseStatusCodeSame(200);

        $collection = $this->getJsonApiCollection($client);

        $this->assertNotEmpty($collection);

        /** @var Item $item */
        foreach ($collection as $item) {
            $this->assertNotNull($item->getId());
            $this->assertEquals('sample_casting', $item->getType());
        }
    }

    /**
     * @depends testCreateMovie
     *
     * @throws TestsException
     */
    public function testSetCasting(CSampleMovie $movie): CSampleMovie
    {
        $actor_1 = $this->getObjectFromFixturesReference(CSamplePerson::class, SamplePersonFixtures::ACTOR_TAG_1);
        $actor_2 = $this->getObjectFromFixturesReference(CSamplePerson::class, SamplePersonFixtures::ACTOR_TAG_2);

        $collection = new Collection(
            [
                (new Item('sample_casting'))
                    ->setAttributes(['is_main_actor' => true])
                    ->setRelationships(['actor' => new Item('sample_person', $actor_1->_id)]),
                (new Item('sample_casting'))
                    ->setRelationships(['actor' => new Item('sample_person', $actor_2->_id)]),
            ]
        );

        $client = self::createClient();
        $client->request('POST', "/api/sample/movies/{$movie->_id}/casting", [], [], [], json_encode($collection));

        $this->assertResponseStatusCodeSame(201);

        $collection = $this->getJsonApiCollection($client);

        $this->assertEquals(2, $collection->getMeta('count'));

        /** @var Item $item */
        foreach ($collection as $item) {
            $this->assertNotNull($item->getId());
            $this->assertEquals('sample_casting', $item->getType());
        }

        return $movie;
    }

    /**
     * @depends testSetCasting
     *
     * @throws TestsException
     */
    public function testSetCastingReplaceIt(CSampleMovie $movie): void
    {
        $actor      = $this->getObjectFromFixturesReference(CSamplePerson::class, SamplePersonFixtures::ACTOR_TAG_3);
        $collection = new Collection(
            [
                (new Item('sample_casting'))
                    ->setAttributes(['is_main_actor' => true])
                    ->setRelationships(['actor' => new Item('sample_person', $actor->_id)]),
            ]
        );

        $client = self::createClient();
        $client->request('POST', "/api/sample/movies/{$movie->_id}/casting", [], [], [], json_encode($collection));

        $this->assertResponseStatusCodeSame(201);

        $collection = $this->getJsonApiCollection($client);

        $this->assertEquals(1, $collection->getMeta('count'));

        $this->assertNotNull($collection->getFirstItem()->getId());
        $this->assertEquals('sample_casting', $collection->getFirstItem()->getType());
    }

    /**
     * @depends testSetCasting
     *
     * @throws TestsException
     */
    public function testDeleteCasting(CSampleMovie $movie): array
    {
        $casting     = $movie->loadBackRefs('casting');
        $remove_cast = reset($casting);
        $actor       = $remove_cast->loadFwdRef('actor_id');

        $client = self::createClient();
        $client->request('DELETE', "/api/sample/movies/{$movie->_id}/casting/{$actor->_id}");

        $this->assertResponseStatusCodeSame(204);
        $this->assertEmpty($client->getResponse()->getContent());

        return [$movie, $actor];
    }

    /**
     * @depends testDeleteCasting
     *
     * @throws TestsException
     */
    public function testDeleteCastingNotInMovie(array $data): void
    {
        [$movie, $actor] = $data;

        $client = self::createClient();
        $client->request('DELETE', "/api/sample/movies/{$movie->_id}/casting/{$actor->_id}");

        $this->assertResponseStatusCodeSame(404);

        $error = $this->getJsonApiError($client);
        $this->assertEquals(
            (new Translator())->tr('CSampleCasting-error-The-actor-does-not-play-in-the-movie', $actor, $movie),
            $error->getMessage()
        );
    }
}
