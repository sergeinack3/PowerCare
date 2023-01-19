<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Tests\Fixtures;

use Ox\Core\CMbArray;
use Ox\Mediboard\Sample\Entities\CSampleCasting;
use Ox\Mediboard\Sample\Entities\CSampleCategory;
use Ox\Mediboard\Sample\Entities\CSampleMovie;
use Ox\Mediboard\Sample\Entities\CSamplePerson;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * Fixtures that create CSampleMovie objects with a random number of cast member for each.
 */
class SampleMovieFixtures extends Fixtures implements GroupFixturesInterface
{
    public const MOVIE_1 = 'sample_movie_1';
    public const MOVIE_2 = 'sample_movie_2';

    private const MOVIES_PATH = 'Resources/movies.json';
    private const MOVIES_NODE = 'movies';

    private const MOVIE_NAME        = 'name';
    private const MOVIE_DESCRIPTION = 'description';
    private const MOVIE_RELEASE     = 'release';
    private const MOVIE_CATEGORY    = 'category';
    private const MOVIE_DURATION    = 'duration';

    private const MAX_LANGUAGES_COUNT   = 3;
    private const MAX_CAST_MEMBER_COUNT = 5;

    /**
     * @inheritDoc
     */
    public function load(): void
    {
        $movie_list = $this->loadMoviesFromFile();

        $i = 1;
        foreach ($movie_list[self::MOVIES_NODE] as $array_movie) {
            $tag = 'self::MOVIE_' . $i;
            $this->createMovie($array_movie, defined($tag) ? constant($tag) : null);

            if ($i++ > 1 && !$this->isFullMode()) {
                break;
            }
        }
    }

    /**
     * Use a json file to create an array of movies that have to be created.
     */
    private function loadMoviesFromFile(): array
    {
        $json_movies = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . self::MOVIES_PATH);

        return CMbArray::mapRecursive('utf8_decode', json_decode($json_movies, true));
    }

    /**
     * Get a CSampleCategory by it's name. If it does not exists create it.
     *
     * @throws FixturesException
     */
    private function getOrCreateCategory(string $name): CSampleCategory
    {
        $category       = new CSampleCategory();
        $category->name = $name;
        $category->loadMatchingObjectEsc();

        // Avoid storing already existing categories to avoid deleting them on purge.
        if (!$category->_id) {
            $category->active = '1';
            $this->store($category);
        }

        return $category;
    }

    /**
     * Create a CSampleMovie with a random director created by SamplePersonFixtures and link up to 5 actors to it.
     *
     * @throws FixturesException
     */
    private function createMovie(array $movie_data, string $tag = null): void
    {
        $category = $this->getOrCreateCategory($movie_data[self::MOVIE_CATEGORY]);
        $director = $this->getRandomDirector();

        // Do not use array_rand because we want the CSA to be possibly null
        $rand_csa = rand(0, count(CSampleMovie::CSA) + 1);

        $movie              = new CSampleMovie();
        $movie->name        = $movie_data[self::MOVIE_NAME];
        $movie->description = $movie_data[self::MOVIE_DESCRIPTION];
        $movie->duration    = $movie_data[self::MOVIE_DURATION];
        $movie->release     = $movie_data[self::MOVIE_RELEASE];
        $movie->category_id = $category->_id;
        $movie->director_id = $director->_id;
        $movie->csa         = CSampleMovie::CSA[$rand_csa] ?? null;

        $count_languages = rand(1, self::MAX_LANGUAGES_COUNT);
        $langs           = [];
        for ($i = 0; $i < $count_languages; $i++) {
            $langs[] = CSampleMovie::LANGUAGES[array_rand(CSampleMovie::LANGUAGES)];
        }
        $movie->languages = implode('|', array_unique($langs));

        $this->store($movie, $tag);

        $actor_count = $this->isFullMode() ? SamplePersonFixtures::ACTOR_COUNT : 5;
        $count_casting = rand(1, self::MAX_CAST_MEMBER_COUNT);
        $cast_id       = rand(1, max(1, ($actor_count - $count_casting)));
        for ($i = 0; $i < $count_casting; $i++) {
            $this->createCasting($movie, $cast_id, $i === 0);
            $cast_id++;
        }
    }

    /**
     * Use the "getReference" to get a CSamplePerson tagged as a director created by SamplePersonFixtures.
     *
     * @throws FixturesException
     */
    private function getRandomDirector(): CSamplePerson
    {
        $id = rand(1, SamplePersonFixtures::DIRECTOR_COUNT);

        return $this->getReference(
            CSamplePerson::class,
            ($id === 1 || !$this->isFullMode())
                ? SamplePersonFixtures::DIRECTOR_TAG
                : (SamplePersonFixtures::DIRECTOR_TAG_PREFIX . $id)
        );
    }

    /**
     * Use "getReference" to get a CSamplePerson tagged as actor created by SamplePersonFixtures.
     *
     * @throws FixturesException
     */
    private function getRandomActor(int $id): CSamplePerson
    {
        return $this->getReference(
            CSamplePerson::class,
            SamplePersonFixtures::ACTOR_TAG_PREFIX . $id
        );
    }

    /**
     * Add an actor in the casting of a movie.
     *
     * @throws FixturesException
     */
    private function createCasting(CSampleMovie $movie, int $id, bool $main = false): void
    {
        $actor = $this->getRandomActor($id);

        $cast                = new CSampleCasting();
        $cast->actor_id      = $actor->_id;
        $cast->movie_id      = $movie->_id;
        $cast->is_main_actor = $main;
        $this->store($cast);
    }

    /**
     * @inheritDoc
     */
    public static function getGroup(): array
    {
        return ['sample_fixtures', 100];
    }
}
