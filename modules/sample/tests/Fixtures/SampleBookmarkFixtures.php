<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Tests\Fixtures;

use Ox\Core\CMbDT;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Sample\Entities\CSampleBookmark;
use Ox\Mediboard\Sample\Entities\CSampleCategory;
use Ox\Mediboard\Sample\Entities\CSampleMovie;
use Ox\Mediboard\Sample\Entities\CSamplePerson;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * Add 5 bookmarks for the current user.
 */
class SampleBookmarkFixtures extends Fixtures implements GroupFixturesInterface
{
    public const BOOKMARKED_MOVIE     = 'bookmarked_movie';

    private const MOVIE_COUNT = 10;
    private const USER_COUNT  = 10;

    private ?CSampleCategory $category = null;

    public function load()
    {
        $director       = $this->getReference(CSamplePerson::class, SamplePersonFixtures::DIRECTOR_TAG);
        $this->category = $this->getReference(CSampleCategory::class, SampleUtilityFixtures::CATEGORY);

        $users = $this->getUsers(static::USER_COUNT);

        for ($i = 0; $i < static::MOVIE_COUNT; $i++) {
            $movie = $this->createMovie($director, $i === 0 ? static::BOOKMARKED_MOVIE : null);

            // Create a bookmark for the current user
            $this->createBookmark($movie);

            $bookmark_count = rand(1, static::USER_COUNT);
            for ($j = 0; $j < $bookmark_count; $j++) {
                $this->createBookmark($movie, $users[$j]);
            }
        }
    }

    private function createMovie(CSamplePerson $director, string $tag = null): CSampleMovie
    {
        $movie              = new CSampleMovie();
        $movie->name        = uniqid();
        $movie->category_id = $this->category->_id;
        $movie->director_id = $director->_id;
        $movie->release     = CMbDT::date('-10 YEAR');
        $movie->duration    = '00:30:00';
        $this->store($movie, $tag);

        return $movie;
    }

    private function createCategory(): CSampleCategory
    {
        $category       = new CSampleCategory();
        $category->name = 'Bookmark_Fixture';
        $category->loadMatchingObjectEsc();

        if (!$category->_id) {
            $category->active = 1;
            $this->store($category);
        }

        return $category;
    }

    private function createBookmark(CSampleMovie $movie, CMediusers $mediuser = null): void
    {
        $bookmark           = new CSampleBookmark();
        $bookmark->movie_id = $movie->_id;

        if ($mediuser) {
            $bookmark->user_id = $mediuser->_id;
        }

        $this->store($bookmark);
    }

    public static function getGroup(): array
    {
        return ['sample_fixtures', 50];
    }

}
