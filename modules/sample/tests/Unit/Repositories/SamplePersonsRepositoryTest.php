<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Tests\Unit\Repositories;

use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Exceptions\ApiRequestException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Request\RequestFilter;
use Ox\Core\Api\Request\RequestLimit;
use Ox\Core\Api\Request\RequestRelations;
use Ox\Core\Api\Request\RequestSort;
use Ox\Core\CMbException;
use Ox\Core\CMbModelNotFoundException;
use Ox\Mediboard\Sample\Entities\CSamplePerson;
use Ox\Mediboard\Sample\Repositories\SamplePersonsRepository;
use Ox\Mediboard\Sample\Tests\Fixtures\SamplePersonFixtures;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Test for SamplePersonsRepository.
 * Also test the initFromRequest for the parent classe AbstractRepository.
 */
class SamplePersonsRepositoryTest extends OxUnitTestCase
{
    /**
     * Test that AbstractRepository::initFromRequest set values in $where, $order, $limit and $seek_keyword by
     * using the parameters of the RequestApi.
     *
     * @throws ApiRequestException|TestsException
     */
    public function testInitFromRequest(): void
    {
        $repository = new SamplePersonsRepository();
        $repository->initFromRequest($this->buildRequestApi());

        $this->assertEquals(
            [
                "`test` = 'lorem'",
                "`ipsum` LIKE '%toto%'",
            ],
            $this->getPrivateProperty($repository, 'where')
        );
        $this->assertEquals('`test` desc,`toto` asc', $this->getPrivateProperty($repository, 'order'));
        $this->assertEquals('5,10', $this->getPrivateProperty($repository, 'limit'));
    }

    /**
     * The massload of the relation 'ALL' must massload each relation for the array of objects.
     * It is necessary to reload the object from DB to empty _fwd et _back fields to avoid errors.
     * The massloading will value _fwd and _back fields, those fields will then be used has cache.
     *
     * @throws ReflectionException|CMbModelNotFoundException|TestsException
     */
    public function testMassLoadRelationAll(): void
    {
        /** @var CSamplePerson $person */
        $person = $this->getObjectFromFixturesReference(CSamplePerson::class, SamplePersonFixtures::ACTOR_TAG_1);

        // Must reload the person because other tests can have load back or fwd refs.
        $person = CSamplePerson::findOrFail($person->_id);

        $this->assertFalse(isset($person->_fwd['nationality_id']));
        $this->assertFalse(isset($person->_back['files']));
        $this->assertFalse(isset($person->_back['roles']));

        $repository = new SamplePersonsRepository();
        $this->invokePrivateMethod(
            $repository,
            'massLoadRelation',
            [$person->_id => $person],
            RequestRelations::QUERY_KEYWORD_ALL
        );

        $this->assertNotNull($person->_fwd['nationality_id']);
        $this->assertCount(1, $person->_back['files']);
        // The actor might not have roles. But the massloading at least create an empty array
        $this->assertTrue(isset($person->_back['roles']));
    }

    /**
     * Calling the function "countList" on an AbstractSampleSeekableRepository with 'seek_keywords' beeing valued should
     * value the $object->_totalSeek field with the total count of lines returned by the query.
     *
     * @throws TestsException
     */
    public function testCountListSeekWithoutSeekCountCache(): void
    {
        /** @var CSamplePerson $actor */
        $actor = $this->getObjectFromFixturesReference(CSamplePerson::class, SamplePersonFixtures::DIRECTOR_TAG);

        $repository = new SamplePersonsRepository();
        $object     = $this->getPrivateProperty($repository, 'object');

        $this->assertNull($object->_totalSeek);

        $reflection_class    = new ReflectionClass($repository);
        $reflection_property = $reflection_class->getProperty('seek_keywords');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($repository, $actor->first_name);

        $count = $repository->count();
        // Cannot check an exact count because the DB could have multiple persons with look alike names.
        $this->assertGreaterThan(0, $count);

        // Check that the total count has been correctly set.
        $this->assertEquals($count, $object->_totalSeek);
    }

    /**
     * Build a RequestApi that can be used for tests.
     *
     * @throws ApiRequestException|ApiException
     */
    private function buildRequestApi(): RequestApi
    {
        $stack = new RequestStack();

        $request = new Request(
            [
                RequestLimit::QUERY_KEYWORD_LIMIT  => '10',
                RequestLimit::QUERY_KEYWORD_OFFSET => 5,
                RequestSort::QUERY_KEYWORD_SORT    => '-test' . RequestSort::SORT_SEPARATOR . '+toto',
                RequestFilter::QUERY_KEYWORD_FILTER => 'test.equal.lorem'
                    . RequestFilter::FILTER_SEPARATOR . 'ipsum.contains.toto'
            ]
        );
        $stack->push($request);

        return new RequestApi($stack);
    }
}
