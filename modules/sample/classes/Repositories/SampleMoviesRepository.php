<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Repositories;

use Exception;
use Ox\Core\Api\Request\RequestRelations;
use Ox\Core\CMbException;
use Ox\Core\CRequest;
use Ox\Core\CStoredObject;
use Ox\Core\Repositories\AbstractRequestApiRepository;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Sample\Entities\CSampleCasting;
use Ox\Mediboard\Sample\Entities\CSampleMovie;
use Ox\Mediboard\Sample\Entities\CSampleNationality;
use Ox\Mediboard\Sample\Entities\CSamplePerson;

/**
 * Repository to fetch CSampleMovie objects.
 */
class SampleMoviesRepository extends AbstractRequestApiRepository
{
    /**
     * Load the casting of a movie using the repository parameters.
     *
     * @throws Exception
     */
    public function findCasting(CSampleMovie $movie): array
    {
        if (!$movie->_id) {
            return [];
        }

        return $movie->loadBackRefs(
            'casting',
            $this->order,
            $this->limit,
            null,
            null,
            null,
            null,
            $this->where
        );
    }

    /**
     * Count the casting of a movie using the repository parameters.
     *
     * @throws Exception
     */
    public function countCasting(CSampleMovie $movie): int
    {
        if (!$movie->_id) {
            return 0;
        }

        return $movie->countBackRefs('casting', $this->where);
    }

    /**
     * Load a list of movies with a director of the nationality $nationality.
     *
     * @throws Exception
     */
    public function findMoviesByDirectorNationality(CSampleNationality $nationality): array
    {
        if (!$nationality->_id) {
            return [];
        }

        $this->addWhereNationality($nationality);

        return $this->object->loadList(
            $this->where,
            $this->order,
            $this->limit,
            null,
            ['sample_person' => '`sample_movie`.director_id = `sample_person`.sample_person_id']
        );
    }

    /**
     * Count a list of movies for a director with the nationality $nationality
     *
     * @throws Exception
     */
    public function countMoviesByDirectorNationality(CSampleNationality $nationality): int
    {
        if (!$nationality->_id) {
            return 0;
        }

        $this->addWhereNationality($nationality);

        return $this->object->countList(
            $this->where,
            null,
            ['sample_person' => '`sample_movie`.director_id = `sample_person`.sample_person_id']
        );
    }

    /**
     * Find the top $count bookmarked movies.
     *
     * @return array An array ordered by the most bookmarked movies.
     *               The keys of the array are the movies ids and the value the count of bookmarks.
     *
     * @throws Exception
     */
    public function findMostBookmarked(int $count): array
    {
        $request = new CRequest();
        $request->addSelect(['movie_id', 'COUNT(*) as nb']);
        $request->addTable('sample_bookmark');
        $request->addGroup('movie_id');
        $request->addOrder('nb DESC');
        $request->setLimit($count);

        return $this->object->getDS()->loadHashList($request->makeSelect());
    }

    /**
     * Add a condition on the director's nationality if it's not already set.
     */
    private function addWhereNationality(CSampleNationality $nationality): void
    {
        if (!isset($this->where['sample_person.nationality_id'])) {
            $this->where['sample_person.nationality_id'] =  $this->object->getDS()->prepare('= ?', $nationality->_id);
        }
    }

    /**
     * @inheritDoc
     */
    protected function getObjectInstance(): CStoredObject
    {
        return new CSampleMovie();
    }

    /**
     * @inheritDoc
     *
     * @throws CMbException
     */
    public function massLoadRelations(array $objects, array $relations): void
    {
        parent::massLoadRelations($objects, $relations);

        // Ensure the files relations are loaded because we always send a link to the cover of the movies.
        if (
            !in_array(RequestRelations::QUERY_KEYWORD_ALL, $relations)
            && !in_array(CSampleMovie::RELATION_FILES, $relations)
        ) {
            $this->massLoadRelation($objects, CSampleMovie::RELATION_FILES);
        }
    }

    /**
     * Massload the data for the $relation.
     * If the keyword 'all' is passed as a $relation massload all the datas.
     *
     * @param CSampleMovie[] $objects
     *
     * @throws Exception|CMbException
     */
    protected function massLoadRelation(array $objects, string $relation): void
    {
        switch ($relation) {
            case RequestRelations::QUERY_KEYWORD_ALL:
                $this->massLoadDirectors($objects);
                $this->massLoadCategories($objects);
                $this->massLoadCovers($objects);
                $this->massLoadCasting($objects, true);
                $this->massLoadBookmarks($objects);
                $this->massLoadIdx($objects);
                break;
            case CSampleMovie::RELATION_DIRECTOR:
                $this->massLoadDirectors($objects);
                break;
            case CSampleMovie::RELATION_CATEGORY:
                $this->massLoadCategories($objects);
                break;
            case CSampleMovie::RELATION_FILES:
                $this->massLoadCovers($objects);
                break;
            case CSampleMovie::RELATION_CASTING:
                $this->massLoadCasting($objects);
                break;
            case CSampleMovie::RELATION_ACTORS:
                $this->massLoadCasting($objects, true);
                break;
            case CSampleMovie::RELATION_BOOKMARKS:
                $this->massLoadBookmarks($objects);
                break;
            default:
                // Do nothing
        }
    }

    /**
     * Massload the director_id of the CSampleMovie list.
     *
     * @param CSampleMovie[] $objects
     *
     * @throws Exception
     */
    private function massLoadDirectors(array $objects): void
    {
        if ($directors = CStoredObject::massLoadFwdRef($objects, 'director_id')) {
            CStoredObject::massLoadBackRefs(
                $directors,
                'files',
                null,
                ['file_name' => $this->object->getDS()->prepare('= ?', CSamplePerson::PROFILE_NAME)]
            );
        }
    }

    /**
     * Massload the categories of the CSampleMovie list.
     *
     * @param CSampleMovie[] $objects
     *
     * @throws Exception
     */
    private function massLoadCategories(array $objects): void
    {
        CStoredObject::massLoadFwdRef($objects, 'category_id');
    }

    /**
     * Massload the covers of the CSampleMovie list.
     *
     * @param CSampleMovie[] $objects
     *
     * @throws Exception
     */
    private function massLoadCovers(array $objects): void
    {
        CStoredObject::massLoadBackRefs(
            $objects,
            'files',
            null,
            ['file_name' => $this->object->getDS()->prepare('= ?', CSampleMovie::COVER_NAME),]
        );
    }

    private function massLoadBookmarks(array $objects): void
    {
        CStoredObject::massLoadBackRefs(
            $objects,
            'bookmarked_by',
            null,
            ['user_id' => $this->object->getDS()->prepare('= ?', CMediusers::get()->_id),]
        );
    }

    /**
     * @param CSampleMovie[] $objects
     *
     * @throws Exception
     */
    private function massLoadCasting(array $objects, bool $with_actors = false): void
    {
        $casting = CStoredObject::massLoadBackRefs($objects, 'casting');

        if ($casting && $with_actors) {
            if ($actors = CStoredObject::massLoadFwdRef($casting, 'actor_id')) {
                CStoredObject::massLoadBackRefs(
                    $actors,
                    'files',
                    null,
                    ['file_name' => $this->object->getDS()->prepare('= ?', CSamplePerson::PROFILE_NAME)]
                );
            }
        }
    }

    /**
     * Massload actor_id from the CSampleCasting list.
     *
     * @param CSampleCasting[] $objects
     *
     * @throws Exception
     */
    public function massLoadActorsFromCasting(array $objects): void
    {
        $actors = CStoredObject::massLoadFwdRef($objects, 'actor_id');
        CStoredObject::massLoadBackRefs(
            $actors,
            'files',
            null,
            ['file_name' => $this->object->getDS()->prepare('= ?', CSamplePerson::PROFILE_NAME)]
        );
    }

    /**
     * @param CSampleMovie[] $objects
     *
     * @throws Exception
     */
    public function massLoadIdx(array $objects): void
    {
        CStoredObject::massLoadBackRefs($objects, 'identifiants');
    }
}
