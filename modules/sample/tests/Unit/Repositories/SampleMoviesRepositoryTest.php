<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Tests\Unit\Repositories;

use Exception;
use Ox\Core\Api\Request\RequestRelations;
use Ox\Core\CMbException;
use Ox\Core\CMbModelNotFoundException;
use Ox\Mediboard\Sample\Entities\CSampleMovie;
use Ox\Mediboard\Sample\Entities\CSampleNationality;
use Ox\Mediboard\Sample\Entities\CSamplePerson;
use Ox\Mediboard\Sample\Repositories\SampleMoviesRepository;
use Ox\Mediboard\Sample\Tests\Fixtures\SampleMovieFixtures;
use Ox\Mediboard\Sample\Tests\Fixtures\SamplePersonFixtures;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use ReflectionClass;
use ReflectionException;

/**
 * Tests for the SampleMoviesRepository.
 * This repository have 2 additionnal functions that allow the load of casting from a movie and the load of movies
 * by their director's nationality.
 */
class SampleMoviesRepositoryTest extends OxUnitTestCase
{
    /**
     * The load of casting for a movie with no id should return an empty array.
     *
     * @throws Exception
     */
    public function testFindCastingOnNotStoredMovie(): void
    {
        $this->assertEquals([], (new SampleMoviesRepository())->findCasting(new CSampleMovie()));
    }

    /**
     * The load of casting use the CStoredObject::loadBackRef function with parameters from the repository.
     *
     * @throws Exception
     */
    public function testFindCastingOk(): void
    {
        /** @var CSampleMovie $movie */
        $movie = $this->getObjectFromFixturesReference(CSampleMovie::class, SampleMovieFixtures::MOVIE_1);

        $repository = new SampleMoviesRepository();
        $reflection = new ReflectionClass($repository);
        $property = $reflection->getProperty('limit');
        $property->setAccessible(true);
        $property->setValue($repository, '1');

        $this->assertCount(1, $repository->findCasting($movie));
    }

    /**
     * The load of movies should return an empty array if the nationality has no id.
     *
     * @throws Exception
     */
    public function testFindMoviesByDirectorNationalityWithNotStoredNationality(): void
    {
        $this->assertEquals(
            [],
            (new SampleMoviesRepository())->findMoviesByDirectorNationality(new CSampleNationality())
        );
    }

    /**
     * Count and load the movies with a directory of the same nationality of $director.
     * The database could have multiple data, we can only check that the count is greater than one (the director from
     * the fixture have at least one movie).
     *
     * @throws Exception
     */
    public function testFindMoviesByDirectorNationalityOk(): void
    {
        /** @var CSamplePerson $director */
        $director = $this->getObjectFromFixturesReference(CSamplePerson::class, SamplePersonFixtures::DIRECTOR_TAG);
        /** @var CSampleNationality $nationality */
        $nationality = $director->loadFwdRef('nationality_id');

        $repository = new SampleMoviesRepository();
        $count = $repository->countMoviesByDirectorNationality($nationality);
        $this->assertGreaterThan(0, $count);

        $this->assertCount($count, $repository->findMoviesByDirectorNationality($nationality));
    }

    /**
     * The count of movies from director's nationality should return 0 if the nationality has no ID.
     *
     * @throws Exception
     */
    public function testCountMoviesByDirectorNationalityWithNotStoredNationality(): void
    {
        $this->assertEquals(
            0,
            (new SampleMoviesRepository())->countMoviesByDirectorNationality(new CSampleNationality())
        );
    }

    /**
     * The massload of the relation 'ALL' must massload each relation for the array of objects.
     * It is necessary to reload the object from DB to empty _fwd et _back fields to avoid errors.
     * The massloading will value _fwd and _back fields, those fields will then be used has cache.
     *
     * @throws ReflectionException|TestsException|CMbModelNotFoundException
     */
    public function testMassLoadRelationAll(): void
    {
        $movie = $this->getObjectFromFixturesReference(CSampleMovie::class, SampleMovieFixtures::MOVIE_2);

        // To reset the _back and _fwd fields the object must be reloaded from DB.
        $movie = CSampleMovie::findOrFail($movie->_id);

        $this->assertFalse(isset($movie->_fwd['director_id']));
        $this->assertFalse(isset($movie->_fwd['category_id']));
        $this->assertFalse(isset($movie->_back['files']));

        $repository = new SampleMoviesRepository();
        $this->invokePrivateMethod(
            $repository,
            'massLoadRelation',
            [$movie->_id => $movie],
            RequestRelations::QUERY_KEYWORD_ALL
        );

        $this->assertNotNull($movie->_fwd['director_id']);
        $this->assertNotNull($movie->_fwd['category_id']);
        $this->assertCount(1, $movie->_back['files']);
    }
}
