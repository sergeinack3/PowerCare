<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Import\MovieDb;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Exceptions\ApiRequestException;
use Ox\Core\Api\Request\RequestApiBuilder;
use Ox\Core\CApp;
use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\Kernel\Exception\HttpException;
use Ox\Mediboard\Sample\Controllers\SampleMoviesController;
use Ox\Mediboard\Sample\Controllers\SamplePersonsController;
use Ox\Mediboard\Sample\Entities\CSampleCategory;
use Ox\Mediboard\Sample\Entities\CSampleMovie;
use Ox\Mediboard\Sample\Entities\CSamplePerson;
use Ox\Mediboard\Sample\Exceptions\Import\SampleMovieImportException;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceHTTP;
use Symfony\Component\HttpFoundation\Response;

/**
 * Import random movies from the API of the movie DB.
 * Before using this class you need to create a CSourceHTTP named using the const self::HTTP_SOURCE_NAME and set
 * your API key in this source.
 * This class will :
 *  - Get a list of 20 movies from the API and convert them to MovieDbMovie in order to created them using the
 *      SampleMoviesController::createMovie().
 *  - For each movie get the director and the actors from the API and convert them to MovieDbPerson in order to create
 *      them using the SamplePersonsController::createPerson().
 *
 * @link https://themoviedb.org The movie DB
 */
class SampleMovieImport
{
    public const IMPORT_TAG_NAME = 'sample_import';
    public const BASE_HOST       = 'https://api.themoviedb.org/3';

    private const SEARCH_MOVIE_PATH      = '/search/movie?query=%s&page=%s&include_adult=false';
    private const GET_MOVIE_PATH         = '/movie/%s?append_to_response=credits';
    private const LIST_CATEGORIES_PATH   = '/genre/movie/list';
    private const GET_PERSON_PATH        = '/person/%s';
    private const GET_CONFIGURATION_PATH = '/configuration';

    private const PATH_ARGUMENTS = 'api_key=%s&language=%s';

    // TODO Use pref + languages mapping to ISO-639-1
    private const LANGUAGE = 'fr-FR';

    private const IMPORT_MOVIE_COUNT = 20;
    private const MAX_CAST_PER_MOVIE = 5;

    private SamplePersonsController $persons_controller;
    private SampleMoviesController  $movies_controller;

    private ?CSourceHTTP $http_source;

    private array $movies = [];

    private array $persons = [];

    private array $categories = [];

    private array $existing_ids = [];

    private ?MovieDbImageConfiguration $configuration;

    private int $movie_count = 0;

    public function __construct(SamplePersonsController $persons_controller, SampleMoviesController $movies_controller)
    {
        $this->persons_controller = $persons_controller;
        $this->movies_controller  = $movies_controller;
    }

    /**
     * Import movies from the Movie DB API.
     * See class comment for details.
     *
     * @throws CMbException|SampleMovieImportException
     */
    public function importMovies(int $movie_count = self::IMPORT_MOVIE_COUNT): int
    {
        $this->http_source = static::getSource();

        if (!$this->http_source->_id) {
            throw SampleMovieImportException::httpSourceNotFound();
        }

        $this->requestImagesConfiguration();

        $this->requestCategoriesList();

        // Load imported persons cache.
        // Created persons will be added to this cache.
        $this->persons = $this->loadExistingIds(CClassMap::getSN(CSamplePerson::class));

        while ($this->movie_count < $movie_count) {
            // Reset movie to create in case of loop.
            $this->movies = [];

            // Must reload the imported movie cache for each loop because movies are bulked imported and no mapping
            // can be done (yet) between external_id and internal_id.
            // This query is not costy, the fields are loaded and not the objects.
            $this->existing_ids = $this->loadExistingIds(CClassMap::getSN(CSampleMovie::class));

            $external_movies = $this->requestMovieList();
            $external_movies = CMbArray::mapRecursive('utf8_decode', $external_movies);

            // Get the details for each movie and get (or create) their director and up to 5 actors.
            foreach ($external_movies as $movie_id => &$movie) {
                // Cannot create movie without category.
                // Do not import multiple time the same movie.
                if (!$movie['genre'] || array_key_exists($movie['id'], $this->existing_ids)) {
                    continue;
                }

                $details = $this->get($this->buildQuery(self::GET_MOVIE_PATH, $movie_id));

                $movie['runtime']          = $details['runtime'];
                $movie['spoken_languages'] = $details['spoken_languages'];

                // The getOrCreate{Director|Casting} functions will return the internal ids for corresponding
                // CSamplePerson.
                $movie['director'] = $this->getOrCreateDirector($details['credits']['crew']);
                // Cannot create a movie without a director.
                if (!$movie['director']) {
                    continue;
                }

                $movie['casting'] = $this->getOrCreateCasting($details['credits']['cast']);

                if ($movie['poster_path']) {
                    $movie['poster'] = $this->getImage($this->configuration->getPosterPrefix() . $movie['poster_path']);
                }

                $this->movies[] = new MovieDbMovie($movie);
            }

            try {
                $this->createMovies();
            } catch (SampleMovieImportException $e) {
                // Silence exceptions
            }
        }

        return $this->movie_count;
    }

    /**
     * Load in memory all the CSampleCategory and order them in an array index by name [category_name => category_id].
     * Query the api to get all the categories.
     * Map the categories to the existing CSampleCategory. Create them if necessary.
     *
     * @throws SampleMovieImportException
     */
    private function requestCategoriesList(): void
    {
        // Load all categories and order them correctly.
        $internal_categories = (new CSampleCategory())->loadList();
        $internal_categories = array_flip(CMbArray::pluck($internal_categories, 'name'));

        $decoded_categories = $this->get($this->buildQuery(self::LIST_CATEGORIES_PATH));

        foreach ($decoded_categories['genres'] as $category) {
            if (!isset($internal_categories[$category['name']])) {
                $cat       = new CSampleCategory();
                $cat->name = $category['name'];
                $cat->loadMatchingObjectEsc();

                if (!$cat->_id) {
                    if ($msg = $cat->store()) {
                        throw SampleMovieImportException::unableToCreateCategory($category['name'], $msg);
                    }
                }

                $internal_categories[$category['name']] = $cat->_id;
            }

            $this->categories[$category['id']] = $internal_categories[$category['name']];
        }
    }

    /**
     * @throws CMbException|SampleMovieImportException
     */
    private function requestImagesConfiguration(): void
    {
        $this->configuration = new MovieDbImageConfiguration(
            $this->get($this->buildQuery(self::GET_CONFIGURATION_PATH))
        );
    }

    /**
     * Make a request to the Movie DB API to get a list of 20 movies starting with a random letter.
     *
     * @throws SampleMovieImportException
     */
    private function requestMovieList(): array
    {
        $external_movies = [];

        $decoded_data = $this->get($this->buildQuery(self::SEARCH_MOVIE_PATH, chr(rand(97, 122)), rand(1, 100)));
        foreach ($decoded_data['results'] as $movie) {
            // Might change this if we handle multiple categories for a single movie.
            $movie['genre'] = (isset($movie['genre_ids'][0])) ? $this->categories[$movie['genre_ids'][0]] : null;

            $external_movies[$movie['id']] = $movie;
        }

        return $external_movies;
    }

    /**
     * Parse the crew members of the movie to find the director, request details about him and create the
     * corresponding CSamplePerson.
     *
     * @return int|null The id of the CSamplePerson or null if no director has been found.
     *
     * @throws SampleMovieImportException|CMbException
     */
    private function getOrCreateDirector(array $crew): ?int
    {
        foreach ($crew as $crew_member) {
            if ($crew_member['job'] === 'Director') {
                if (!isset($this->persons[$crew_member['id']])) {
                    $details                 = $this->get($this->buildQuery(self::GET_PERSON_PATH, $crew_member['id']));
                    $details                 = CMbArray::mapRecursive('utf8_decode', $details);
                    $crew_member['birthday'] = $details['birthday'];
                    $crew_member['director'] = true;

                    if ($crew_member['profile_path']) {
                        $crew_member['profile'] = $this->getImage(
                            $this->configuration->getProfilePrefix() . $crew_member['profile_path']
                        );
                    }

                    if (!$id = $this->createPerson(new MovieDbPerson($crew_member))) {
                        // An error occured in the creation of the CSamplePerson. Try to continue to create another one.
                        continue;
                    }

                    $this->persons[$crew_member['id']] = $id;
                }

                return $this->persons[$crew_member['id']];
            }
        }

        return null;
    }

    /**
     * Parse the cast members of the movie get or create up to 5 of them as CSamplePerson.
     *
     * @return int[] The CSamplePerson ids
     *
     * @throws SampleMovieImportException|CMbException
     */
    private function getOrCreateCasting(array $cast): array
    {
        $casting = [];
        foreach ($cast as $cast_member) {
            if (count($casting) >= self::MAX_CAST_PER_MOVIE) {
                break;
            }

            if (!isset($this->persons[$cast_member['id']])) {
                $details                 = $this->get($this->buildQuery(self::GET_PERSON_PATH, $cast_member['id']));
                $details                 = CMbArray::mapRecursive('utf8_decode', $details);
                $cast_member['birthday'] = $details['birthday'];

                if ($cast_member['profile_path']) {
                    $cast_member['profile'] = $this->getImage(
                        $this->configuration->getProfilePrefix() . $cast_member['profile_path']
                    );
                }

                if (!$id = $this->createPerson(new MovieDbPerson($cast_member))) {
                    continue;
                }

                $this->persons[$cast_member['id']] = $id;
            }

            $casting[] = $this->persons[$cast_member['id']];
        }

        return $casting;
    }

    /**
     * Get the CSourceHTTP that will be used for request.
     */
    public static function getSource(): CSourceHTTP
    {
        /** @var CSourceHTTP $http_source */
        $http_source = CExchangeSource::get('Sample Movie DB', CSourceHTTP::TYPE, true, null, false);
        $http_source->host = self::BASE_HOST;

        return $http_source;
    }

    /**
     * Tell if the CSourceHTTP has been found.
     */
    public static function isSourceAvailable(): bool
    {
        $source = static::getSource();

        return (bool) $source->_id;
    }

    /**
     * Get the json_decoded body of the response from the CSourceHTTP.
     *
     * @param string $query
     *
     * @return array
     * @throws CMbException
     * @throws SampleMovieImportException
     */
    protected function get(string $query): array
    {
        $client = $this->http_source->getClient();

        $response = $client->request('GET', $query);

        // Do not use strict comparison because CSourceHTTP convert status code to string.
        if ($response->getStatusCode() != Response::HTTP_OK) {
            ['scheme' => $scheme, 'host' => $host, 'path' => $path] = parse_url($this->http_source->host);

            throw SampleMovieImportException::httpResponseException(
                $response->getBody(),
                sprintf('%s://%s%s', $scheme, $host, $path)
            );
        }

        return json_decode($this->http_source->_acquittement, true);
    }

    protected function getImage(string $image_path): MovieDbImage
    {
        $client = $this->http_source->getClient();

        $response = $client->request('GET', $this->buildImageRequest($image_path));

        return new MovieDbImage(
            $response->getHeader('Content-Type')[0] ?? '',
            base64_encode($response->getBody()->__toString())
        );
    }

    /**
     * @param mixed ...$parameters
     */
    private function buildQuery(string $request_path, ...$parameters): string
    {
        return $this->http_source->getHost()
            . sprintf($request_path, ...$parameters)
            . (strpos($request_path, '?') === false ? '?' : '&')
            . sprintf(self::PATH_ARGUMENTS, $this->http_source->getToken(), self::LANGUAGE);
    }

    /**
     * @param string $request_path
     *
     * @return string
     */
    private function buildImageRequest(string $request_path): string
    {
        return $request_path . '?'
            . sprintf(self::PATH_ARGUMENTS, $this->http_source->getToken(), self::LANGUAGE);
    }

    /**
     * Create a RequestApi to POST a new person.
     * Directly call the controller instead of making an API call.
     * Should try to find the person before.
     *
     * @return int The internal id of the created CSamplePerson.
     *
     * @throws CMbException|SampleMovieImportException
     */
    private function createPerson(MovieDbPerson $person): ?int
    {
        // Build the RequestApi.
        // The MovieDbPerson is serialized to json in the right format to be used in the body of a post of
        // CSamplePerson.
        try {
            $request_api = (new RequestApiBuilder())
                ->setMethod('POST')
                ->setContent(json_encode(['data' => $person]))
                ->buildRequestApi();

            $api_response = $this->persons_controller->createPerson($request_api);
        } catch (CMbException $e) {
            throw new SampleMovieImportException($e->getMessage());
        } catch (ApiRequestException|ApiException $e) {
            // Silence those exceptions
            return null;
        }

        $response = json_decode($api_response->getContent(), true);

        return $response['data'][0]['id'];
    }

    /**
     * Create multiple CSampleMovie using the MovieDbMovie array ($this->movies).
     * The MovieDbMovie are json_encoded which result in a JSON:API body for the POST of movies.
     *
     * @throws CMbException|SampleMovieImportException
     */
    private function createMovies(): void
    {
        /** @var MovieDbMovie $movie */
        foreach ($this->movies as $movie) {
            try {
                // First create the movie
                $request_api = (new RequestApiBuilder())
                    ->setMethod('POST')
                    ->setContent(json_encode(['data' => $movie]))
                    ->buildRequestApi();

                $api_response = $this->movies_controller->createMovie($request_api);
                $content = json_decode($api_response->getContent(), true);
                $sample_movie = CSampleMovie::findOrFail($content['data'][0]['id']);

                // Then add the casting to it
                $request_casting = (new RequestApiBuilder())
                    ->setMethod('POST')
                    ->setContent(json_encode(['data' => $movie->convertCasting()]))
                    ->buildRequestApi();

                $this->movies_controller->setCasting($sample_movie, $request_casting);

                $this->movie_count++;
            } catch (CMbException|ApiRequestException|ApiException|HttpException $e) {
                CApp::log('Sample Import : ' . $e->getMessage());
            }
        }
    }

    /**
     * Load the existing external ids for a class (CSampleMovie | CSamplePerson).
     * This will be used as a cache to avoid importing multiple times the same movie / person.
     *
     * @return array An associative array [external_id => internal_id]
     *
     * @throws Exception
     */
    private function loadExistingIds(string $short_class_name): array
    {
        $ds      = CSQLDataSource::get('std');
        $request = new CRequest();
        $request->addTable('id_sante400');
        $request->addSelect(['id400', 'object_id']);
        $request->addWhere(
            [
                'object_class' => $ds->prepare('= ?', $short_class_name),
                'tag'          => $ds->prepare('= ?', self::IMPORT_TAG_NAME),
            ]
        );

        return $ds->loadHashList($request->makeSelect());
    }
}
