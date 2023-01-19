<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Tests\Unit\Entities;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Sample\Entities\CSampleCasting;
use Ox\Mediboard\Sample\Entities\CSampleCategory;
use Ox\Mediboard\Sample\Entities\CSampleMovie;
use Ox\Mediboard\Sample\Entities\CSamplePerson;
use Ox\Mediboard\Sample\Tests\Fixtures\SampleMovieFixtures;
use Ox\Mediboard\Sample\Tests\Fixtures\SamplePersonFixtures;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use ReflectionException;

/**
 * Test class for CSampleMovie.
 * Also test the optimization of relations in serialization json_api.
 */
class CSampleMovieTest extends OxUnitTestCase
{
    /**
     * Serialize the movie with relations and check if the relationships node have the relation_name as a key instead
     * of the object_type.
     *
     * @throws ApiException
     */
    public function testSerializeMovieWithRelations(): void
    {
        $movie = $this->getObjectFromFixturesReference(CSampleMovie::class, SampleMovieFixtures::MOVIE_1);
        $item  = new Item($movie);
        $item->setModelRelations([CSampleMovie::RELATION_DIRECTOR, CSampleMovie::RELATION_FILES]);

        // Encode using JsonApiSerializer and decode to make assertions on the returned array.
        $serial = json_decode(json_encode($item, JSON_HEX_QUOT | JSON_HEX_APOS), true);

        $this->assertArrayHasKey('relationships', $serial['data']);

        // Check if the relation 'director' is correctly set
        $this->assertArrayHasKey(CSampleMovie::RELATION_DIRECTOR, $serial['data']['relationships']);
        $this->assertEquals(
            CSamplePerson::RESOURCE_TYPE,
            $serial['data']['relationships'][CSampleMovie::RELATION_DIRECTOR]['data']['type']
        );

        $director_id = $serial['data']['relationships'][CSampleMovie::RELATION_DIRECTOR]['data']['id'];

        // Check if the relation 'cover' is correctly set
        $this->assertArrayHasKey(CSampleMovie::RELATION_FILES, $serial['data']['relationships']);
        $this->assertEquals(
            CFile::RESOURCE_TYPE,
            $serial['data']['relationships'][CSampleMovie::RELATION_FILES]['data']['type']
        );

        $file_id = $serial['data']['relationships'][CSampleMovie::RELATION_FILES]['data']['id'];

        // Check if the sample_person and the file are in the included with their types and not the relation name
        $sample_person_present = $file_present = false;

        foreach ($serial['included'] as $include) {
            if ($include['type'] === CSamplePerson::RESOURCE_TYPE && $include['id'] === $director_id) {
                $sample_person_present = true;
            } elseif ($include['type'] === CFile::RESOURCE_TYPE && $include['id'] === $file_id) {
                $file_present = true;
            }
        }

        $this->assertTrue($sample_person_present);
        $this->assertTrue($file_present);
    }

    /**
     * Assert that the creation of a cover on a not stored movie trow an exception.
     * This should not happen because of the createCover beeing private.
     *
     * @throws TestsException|ReflectionException
     */
    public function testCreateCoverThrowException(): void
    {
        $movie = new CSampleMovie();

        $this->expectExceptionObject(new CMbException('CSampleMovie-Error-Cannot-create-cover-for-non-stored-movie'));

        $this->invokePrivateMethod($movie, 'createCover');
    }

    /**
     * The buildCoverLink function should return an empty array if the movie has no cover.
     *
     * @throws Exception
     */
    public function testBuildCoverLinkWithNoCover(): void
    {
        $movie = new CSampleMovie();
        $this->assertEquals([], $movie->buildCoverLink());
    }

    /**
     * Assert that the getResourceCover on a movie with a cover will return an item containing the cover (CFile).
     *
     * @throws ApiException
     */
    public function testGetResourceCoverOk(): void
    {
        /** @var CSampleMovie $movie */
        $movie = $this->getObjectFromFixturesReference(CSampleMovie::class, SampleMovieFixtures::MOVIE_2);

        $item = $movie->getResourceCover();
        $this->assertNotNull($item);

        $data = $item->getDatas();
        $this->assertInstanceOf(CFile::class, $data);
    }

    /**
     * Assert that calling getResourceCover on a movie without cover will return null and not an empty Item.
     *
     * @throws ApiException
     */
    public function testGetResourceCoverNoCover(): void
    {
        $movie = new CSampleMovie();
        $this->assertNull($movie->getResourceCover());
    }

    /**
     * The creation of a movie will put the current user as creator if the creator_id field has not been forced.
     * The cover is automatically created for a new movie.
     *
     * @throws Exception
     */
    public function testStoreNewMovieWithtoutCreator(): void
    {
        $movie = $this->createMovie();
        $this->storeOrFailed($movie);

        $current_user = CMediusers::get();

        $this->assertEquals($current_user->_id, $movie->creator_id);

        $cover = $movie->loadCover();
        $this->assertNotNull($cover->_id);
    }

    /**
     * The store of a new movie let the creator_id field untouched if it has been valued by the user.
     *
     * @throws Exception
     */
    public function testStoreNewMovieWithCreator(): void
    {
        /** @var CMediusers $user */
        $user  = $this->getObjectFromFixturesReference(CMediusers::class, UsersFixtures::REF_USER_CHIR);
        $movie = $this->createMovie($user);
        $this->storeOrFailed($movie);

        $this->assertEquals($user->_id, $movie->creator_id);

        $cover = $movie->loadCover();
        $this->assertNotNull($cover->_id);
    }

    /**
     * Create a new movie using fixtures data.
     * Allow the injection of a movie to mock the object and test the throw of exceptions in CSampleMovie::createCover
     * or CSampleMovie::createCasting.
     *
     * @throws Exception
     */
    private function createMovie(CMediusers $creator = null): CSampleMovie
    {
        $director = $this->getObjectFromFixturesReference(CSamplePerson::class, SamplePersonFixtures::DIRECTOR_TAG);
        $category = $this->getCategory();

        $movie              = new CSampleMovie();
        $movie->name        = uniqid();
        $movie->release     = CMbDT::date();
        $movie->duration    = CMbDT::time();
        $movie->director_id = $director->_id;
        $movie->category_id = $category->_id;
        if ($creator && $creator->_id) {
            $movie->creator_id = $creator->_id;
        }

        return $movie;
    }

    /**
     * Get a category or create it if it does not exists.
     */
    private function getCategory(): CSampleCategory
    {
        $cat       = new CSampleCategory();
        $cat->name = 'Action';
        $cat->loadMatchingObjectEsc();
        if (!$cat->_id) {
            $cat->active = '1';
            $this->storeOrFailed($cat);
        }

        return $cat;
    }
}
