<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Entities;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\Content\JsonApiItem;
use Ox\Core\Api\Request\Content\RequestContentException;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Join table between movies and users telling if the user has added the movie to his bookmarked movies.
 * A user can only add the same movie to its bookmarks once.
 */
class CSampleBookmark extends CMbObject
{
    public const RESOURCE_TYPE = 'sample_bookmark';

    public const RELATION_MOVIE = 'movie';
    public const RELATION_USER  = 'user';

    /** @var int */
    public $sample_bookmark_id;

    /** @var int */
    public $user_id;

    /** @var int */
    public $movie_id;

    /** @var string */
    public $datetime;

    /**
     * Use the uniques spec to ensure a movie can be bookmarked only once for a user.
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = "sample_bookmark";
        $spec->key   = "sample_bookmark_id";

        $spec->uniques['user_movie'] = ['user_id', 'movie_id'];

        return $spec;
    }

    /**
     * Use the prop cascade to delete this object if the user or movie is deleted.
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['user_id']  = 'ref class|CMediusers notNull cascade back|bookmarked_movies';
        $props['movie_id'] = 'ref class|CSampleMovie notNull cascade back|bookmarked_by';
        $props['datetime'] = 'dateTime notNull default|now fieldset|default';

        return $props;
    }

    /**
     * Force the datetime to now. If no user_id is provided set the one for the current user.
     *
     * @throws Exception
     */
    public function store(): ?string
    {
        if (!$this->_id) {
            $this->datetime = CMbDT::dateTime();

            if (!$this->user_id) {
                $this->user_id  = CMediusers::get()->_id;
            }
        }

        return parent::store();
    }

    /**
     * Get the bookmarked movie.
     *
     * @throws ApiException
     */
    public function getResourceMovie(): Item
    {
        return new Item($this->loadFwdRef('movie_id', true));
    }

    /**
     * Set the movie_id for the bookmark.
     *
     * @throws RequestContentException
     */
    public function setResourceMovie(?JsonApiItem $json_api_movie): void
    {
        $this->movie_id = $json_api_movie === null
            ? ''
            : $json_api_movie->createModelObject(CSampleMovie::class, false)->getModelObject()->_id;
    }

    /**
     * Get the user of the bookmark.
     *
     * @throws ApiException
     */
    public function getResourceUser(): Item
    {
        return new Item($this->loadFwdRef('user_id', true));
    }
}
