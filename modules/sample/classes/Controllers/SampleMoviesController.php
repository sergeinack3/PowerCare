<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Controllers;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Request\RequestRelations;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CAppUI;
use Ox\Core\CController;
use Ox\Core\CMbException;
use Ox\Core\Kernel\Exception\HttpException;
use Ox\Mediboard\Sample\Entities\CSampleCasting;
use Ox\Mediboard\Sample\Entities\CSampleMovie;
use Ox\Mediboard\Sample\Entities\CSampleNationality;
use Ox\Mediboard\Sample\Entities\CSamplePerson;
use Ox\Mediboard\Sample\Import\MovieDb\SampleMovieImport;
use Ox\Mediboard\Sample\Repositories\SampleMoviesRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller that answer the api calls on movies.
 */
class SampleMoviesController extends CController
{
    public const SEARCH_NATIONALITY = 'nationality_id';
    public const MOVIE_CASTING_LINK = 'casting';

    /**
     * @see SampleMoviesRepository::initFromRequest
     *
     * @api
     *
     * Load a list of movies using the request parameters.
     * Before being autowire the SampleMoviesRepository is initialized using the RequestApi.
     * A collection of movies is created using the arguments of the RequestApi for fieldsets, relationships, ...
     *
     * @throws Exception
     *
     * TODO When conditions for routes are implemented use them for nationality and top_bookmarked
     */
    public function listMovies(RequestApi $request_api, SampleMoviesRepository $movies_repository): Response
    {
        if ($bookmark_count = $request_api->getRequest()->query->get('top_bookmarked')) {
            return $this->listMostBookmarkedMovies($request_api, $movies_repository, $bookmark_count);
        }

        $nationality = null;
        if ($nationality_id = $request_api->getRequest()->get(self::SEARCH_NATIONALITY)) {
            $nationality = CSampleNationality::findOrFail($nationality_id);
        }

        $total = $nationality
            ? $movies_repository->countMoviesByDirectorNationality($nationality)
            : $movies_repository->count();

        $movies = $nationality
            ? $movies_repository->findMoviesByDirectorNationality($nationality)
            : $movies_repository->find();

        // Massload the relations that will be loaded from cache in the links later.
        $movies_repository->massLoadRelations($movies, $request_api->getRelations());

        /** @var Collection $collection */
        $collection = Collection::createFromRequest($request_api, $movies);

        // Build cover link + legacy view link
        foreach ($collection as $item) {
            /** @var CSampleMovie $movie */
            $movie = $item->getDatas();
            $item->addLinks($movie->buildLinks());
        }

        $collection->createLinksPagination(
            $request_api->getOffset(),
            $request_api->getLimit(),
            $total
        );

        return $this->renderApiResponse($collection);
    }

    /**
     * @api
     *
     * Use the Ox\Core\Kernel\Resolver\CStoredObjectAttributeValueResolver to inject the CSampleMovie from the
     * parameter sample_movie_id.
     *
     * @see CStoredObjectAttributeValueResolver
     */
    public function getMovie(CSampleMovie $movie, RequestApi $request_api): Response
    {
        $item = Item::createFromRequest($request_api, $movie);
        $item->addLinks($movie->buildLinks());

        // Add the link to the casting of the movie.
        // This link can be used to list, add or delete actors from a movie.
        $item->addLinks(
            [self::MOVIE_CASTING_LINK => $this->generateUrl('sample_casting_list', ['sample_movie_id' => $movie->_id])]
        );

        return $this->renderApiResponse($item);
    }

    /**
     * @api
     *
     * Use the RequestApi to create a collection of movies from the body content.
     * Store the collection of movies en return it.
     *
     * @throws ApiException|CMbException
     */
    public function createMovie(RequestApi $request_api): Response
    {
        $movies = $request_api->getModelObjectCollection(
            CSampleMovie::class,
            [
                CSampleMovie::FIELDSET_DEFAULT,
                CSampleMovie::FIELDSET_DETAILS,
            ]
        );

        $collection = $this->storeCollection($movies);
        $collection->setModelFieldsets($request_api->getFieldsets());
        $collection->setModelRelations($request_api->getRelations());

        $sample_movies = [];
        foreach ($movies as $movie) {
            $sample_movies[$movie->_id] = $movie;
        }

        $repository = new SampleMoviesRepository();
        $repository->massLoadRelations(
            $sample_movies,
            array_merge([CSampleMovie::RELATION_FILES], $request_api->getRelations())
        );

        // Add the link cover and casting to movies created
        foreach ($collection as $item) {
            /** @var CSampleMovie $movie */
            $movie = $item->getDatas();
            $item->addLinks($movie->buildCoverLink());
            $item->addLinks(
                [
                    self::MOVIE_CASTING_LINK => $this->generateUrl(
                        'sample_casting_list',
                        ['sample_movie_id' => $movie->_id]
                    ),
                ]
            );
        }

        return $this->renderApiResponse($collection, Response::HTTP_CREATED);
    }

    /**
     * @api
     */
    public function importMovies(SamplePersonsController $persons_controller): Response
    {
        $import = new SampleMovieImport($persons_controller, $this);
        try {
            $count = $import->importMovies();
        } catch (CMbException $e) {
            return $this->renderResponse($e->getMessage(), 500);
        }

        return $this->renderResponse($count);
    }

    /**
     * @api
     *
     * Update an existing movie ($movie) using the body of the request.
     * The user must have the edit permission on the movie
     *
     * @throws ApiException|CMbException
     */
    public function updateMovie(CSampleMovie $movie, RequestApi $request_api): Response
    {
        /** @var CSampleMovie $movie_from_request */
        $movie_from_request = $request_api->getModelObject(
            $movie,
            [
                CSampleMovie::FIELDSET_DEFAULT,
                CSampleMovie::FIELDSET_DETAILS,
            ]
        );

        $item = $this->storeObject($movie_from_request);
        $item->addLinks($movie->buildCoverLink());
        $item->setModelFieldsets($request_api->getFieldsets());
        $item->setModelRelations($request_api->getRelations());

        return $this->renderApiResponse($item, Response::HTTP_OK);
    }

    /**
     * @api
     *
     * Delete a movie ($movie). The user must have the edit permission on the movie.
     */
    public function deleteMovie(CSampleMovie $movie): Response
    {
        $this->deleteObject($movie);

        return $this->renderResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @api
     *
     * @throws Exception
     */
    public function listCasting(
        CSampleMovie $movie,
        SampleMoviesRepository $repository,
        RequestApi $request_api
    ): Response {
        $casting = $repository->findCasting($movie);

        if (
            in_array(RequestRelations::QUERY_KEYWORD_ALL, $request_api->getRelations())
            || in_array(CSampleCasting::RELATION_ACTOR, $request_api->getRelations())
        ) {
            $repository->massLoadActorsFromCasting($casting);
        }

        $collection = Collection::createFromRequest($request_api, $casting);

        $collection->createLinksPagination(
            $request_api->getOffset(),
            $request_api->getLimit(),
            $repository->countCasting($movie)
        );

        return $this->renderApiResponse($collection);
    }

    /**
     * @api
     *
     * @throws ApiException|CMbException
     */
    public function setCasting(CSampleMovie $movie, RequestApi $request_api): Response
    {
        $casting = $request_api->getModelObjectCollection(CSampleCasting::class);

        /** @var CSampleCasting $cast */
        foreach ($casting as $cast) {
            $cast->movie_id = $movie->_id;
        }

        // Delete old casting before storing new ones.
        // For bigger objects you should try to match the posted ones with the existing ones instead of deleting them.
        if ($old_casting_ids = $movie->loadBackIds('casting')) {
            $cast = new CSampleCasting();
            $cast->deleteAll($old_casting_ids);
        }

        $collection = $this->storeCollection($casting);

        return $this->renderApiResponse($collection, Response::HTTP_CREATED);
    }

    /**
     * @api
     *
     * @throws CMbException|HttpException
     */
    public function deleteCasting(CSampleMovie $movie, CSamplePerson $actor): Response
    {
        $casting = new CSampleCasting();
        $casting->movie_id = $movie->_id;
        $casting->actor_id = $actor->_id;
        $casting->loadMatchingObjectEsc();

        if (!$casting->_id) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                CAppUI::tr('CSampleCasting-error-The-actor-does-not-play-in-the-movie', $actor, $movie)
            );
        }

        $this->deleteObject($casting);

        return $this->renderResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Find the top $count bookmarked movies and return them in order.
     *
     * @throws ApiException
     */
    private function listMostBookmarkedMovies(
        RequestApi $request,
        SampleMoviesRepository $repository,
        int $count
    ): Response {
        $movies_bookmarked = $repository->findMostBookmarked($count);

        $movies = (new CSampleMovie())->loadAll(array_keys($movies_bookmarked));

        usort($movies, function (CSampleMovie $elem1, CSampleMovie $elem2) use ($movies_bookmarked) {
            return $movies_bookmarked[$elem2->_id] <=> $movies_bookmarked[$elem1->_id];
        });

        $collection = Collection::createFromRequest($request, $movies);

        /** @var Item $item */
        foreach ($collection as $item) {
            $movie = $item->getDatas();
            $item->addMeta('bookmarked_count', $movies_bookmarked[$movie->_id]);
        }

        return $this->renderApiResponse($collection);
    }
}
