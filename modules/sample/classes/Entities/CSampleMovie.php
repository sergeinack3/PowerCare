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
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Sample\Controllers\Legacy\SampleMoviesController;
use Symfony\Component\Routing\RouterInterface;

/**
 * Representation of a movie.
 * This is the main class that will be used for search, creation or update in this module.
 */
class CSampleMovie extends CMbObject
{
    public const RESOURCE_TYPE = 'sample_movie';

    public const FIELDSET_DETAILS = 'details';

    public const RELATION_DIRECTOR = 'director';

    public const RELATION_CATEGORY = 'category';

    public const RELATION_FILES = 'cover';

    public const RELATION_CASTING = 'casting';

    public const RELATION_ACTORS = 'actors';

    public const RELATION_BOOKMARKS = 'bookmarks';

    public const CSA_10 = '10';
    public const CSA_12 = '12';
    public const CSA_16 = '16';
    public const CSA_18 = '18';

    public const CSA = [self::CSA_10, self::CSA_12, self::CSA_16, self::CSA_18];

    public const LANGUAGE_ENGLISH = 'en';
    public const LANGUAGE_SPANISH = 'es';
    public const LANGUAGE_FRENCH  = 'fr';
    public const LANGUAGE_GERMAN  = 'ger';
    public const LANGUAGE_ITALIAN = 'it';

    public const LANGUAGES = [
        self::LANGUAGE_ENGLISH,
        self::LANGUAGE_SPANISH,
        self::LANGUAGE_FRENCH,
        self::LANGUAGE_GERMAN,
        self::LANGUAGE_ITALIAN,
    ];

    public const COVER_NAME = 'sample_movie_cover';

    private const COVER_PATH = 'resources/Images/cover.jpg';

    private const COVER_URL = '?m=files&raw=thumbnail&document_id=%d&thumb=0';

    /** @var int */
    public $sample_movie_id;

    /** @var string */
    public $name;

    /** @var string */
    public $release;

    /** @var string */
    public $duration;

    /** @var string */
    public $description;

    /** @var int */
    public $category_id;

    /** @var string */
    public $csa;

    /** @var int */
    public $director_id;

    /** @var int */
    public $creator_id;

    /** @var string */
    public $languages;

    /** @var CFile */
    private $cover;

    /**
     * Set the spec seek to 'match' to allow the use of MATCH AGAINST syntax for the CStoredObject::seek function.
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = "sample_movie";
        $spec->key   = "sample_movie_id";

        $spec->seek = 'match';

        return $spec;
    }

    public function getProps(): array
    {
        $props = parent::getProps();

        $props['name']        = 'str notNull seekable fieldset|' . self::FIELDSET_DEFAULT;
        $props['release']     = 'date notNull fieldset|' . self::FIELDSET_DEFAULT;
        $props['duration']    = 'time min|00:00:01 notNull fieldset|' . self::FIELDSET_DEFAULT;
        $props['description'] = 'text seekable fieldset|' . self::FIELDSET_DETAILS.' helped';
        $props['category_id'] = 'ref class|CSampleCategory notNull back|movies';
        $props['csa']         = 'enum list|' . implode('|', self::CSA) . ' fieldset|' . self::FIELDSET_DEFAULT;
        $props['director_id'] = 'ref class|CSamplePerson notNull back|movies_directed';
        $props['creator_id']  = 'ref class|CMediusers back|movies_created';
        $props['languages']   = 'set list|' . implode('|', self::LANGUAGES) . ' default|' . self::LANGUAGE_FRENCH .
            ' fieldset|' . self::FIELDSET_DEFAULT ;

        return $props;
    }

    /**
     * For the first store set the creator_id to the current user if it has not already been done.
     * This information can be found in user_action but it is better to denormalize it to allow the search of all the
     * movies created by a user (a query on the user_action table is always slower).
     */
    public function store(): ?string
    {
        $new = !$this->_id;

        if (!$this->_id && !$this->creator_id) {
            $this->creator_id = CMediusers::get()->_id;
        }

        if ($msg = parent::store()) {
            return $msg;
        }

        // For the first store create the cover.
        // If $_casting is set, create the cast.
        if ($new) {
            try {
                $this->createCover();
            } catch (CMbException $e) {
                return $e->getMessage();
            }
        }

        return null;
    }

    /**
     * Generate and return the self link.
     */
    public function getApiLink(RouterInterface $router): string
    {
        return $router->generate('sample_movies_show', ['sample_movie_id' => $this->_id]);
    }

    /**
     * Return the director of the movie has an Item.
     * This function is automatically called when using the route sample_movies_show with relations=director
     *
     * @throws ApiException
     */
    public function getResourceDirector(): Item
    {
        /** @var CSamplePerson $director */
        $director = $this->loadFwdRef('director_id', true);
        $item = new Item($director);
        $item->addLinks($director->buildProfilePictureLink());

        return $item;
    }

    /**
     * Set $this->director_id using the JsonApiItem passed.
     *
     * @throws RequestContentException
     */
    public function setResourceDirector(?JsonApiItem $json_api_director): void
    {
        $this->director_id = $json_api_director === null
            ? ''
            : $json_api_director->createModelObject(CSamplePerson::class, false)->getModelObject()->_id;
    }

    /**
     * Return the category of the movie has a Item.
     * This function is automatically called when using the route sample_movies_show with relations=category
     *
     * @throws ApiException
     */
    public function getResourceCategory(): Item
    {
        // The field category_id is not nullable so an Item will always be returned.
        return new Item($this->loadFwdRef('category_id', true));
    }

    /**
     * Set $this->category_id using the JsonApiItem passed.
     *
     * @throws RequestContentException
     */
    public function setResourceCategory(?JsonApiItem $json_api_category): void
    {
        $this->category_id = ($json_api_category === null)
            ? ''
            : $json_api_category->createModelObject(CSampleCategory::class, false)->getModelObject()->_id;
    }

    /**
     * Get the casting of a movie.
     *
     * @return Collection|array Collection if casting is not empty. Empty array if casting is empty.
     *
     * @throws ApiException
     */
    public function getResourceCasting()
    {
        $casting = $this->loadBackRefs('casting');
        return $casting ? new Collection($casting) : [];
    }

    /**
     * Get the actors that play in the movie.
     * For each actor add the profile picture link.
     *
     * This function must not use massloading. Objects must be massloaded by the controller before transforming this.
     *
     * @return Collection|array Collection is actors is not empty. Empty array if actors is empty.
     *
     * @throws ApiException
     */
    public function getResourceActors()
    {
        if ($casting = $this->loadBackRefs('casting')) {
            $actors = [];
            foreach ($casting as $cast) {
                if ($actor = $cast->loadFwdRef('actor_id', true)) {
                    $actors[] = $actor;
                }
            }

            $collection = new Collection($actors);
            foreach ($collection as $item) {
                $item->addLinks($item->getDatas()->buildProfilePictureLink());
            }

            return $collection;
        }

        return [];
    }

    /**
     * Return the cover of the movie has a Item.
     * This function is automatically called when using the route sample_movies_show with relations=cover
     * Add the link to the file in the cover.
     *
     * @throws ApiException
     */
    public function getResourceCover(): ?Item
    {
        if ($this->loadCover()) {
            $item = new Item($this->cover);
            $item->addLinks($this->buildCoverLink());

            return $item;
        }

        return null;
    }

    public function setResourceCover(?JsonApiItem $item): void
    {
        if ($item !== null) {
            $this->cover = $item->createModelObject(CFile::class, true)
                ->hydrateObject([], ['file_type', '_base64_content'])
                ->getModelObject();
        }
    }

    /**
     * Build the link to the cover.
     * Load the cover if needed.
     *
     * @throws Exception
     */
    public function buildCoverLink(): array
    {
        if (!$this->cover && !$this->loadCover()) {
            return [];
        }

        return ['cover' => sprintf(self::COVER_URL, $this->cover->_id)];
    }

    /**
     * Build the legacy detail link for the movie.
     * Migth be remove when vue router will be used.
     */
    public function buildLegacyDetailLink(): array
    {
        return ['self_legacy' => sprintf(SampleMoviesController::LEGACY_MOVIE_DETAIL_LINK, $this->_id)];
    }

    /**
     * Build the cover and self_legacy links.
     *
     * @throws Exception
     */
    public function buildLinks(): array
    {
        return array_merge($this->buildCoverLink(), $this->buildLegacyDetailLink());
    }

    /**
     * Create the cover if it does not already exists.
     * Currently the cover is always the same but later the user will be able to upload the image.
     *
     * @throws CMbException
     */
    private function createCover(): void
    {
        if (!$this->_id) {
            throw new CMbException('CSampleMovie-Error-Cannot-create-cover-for-non-stored-movie');
        }

        if (!$this->cover) {
            $this->loadCover();
        }

        if ($this->cover === null || $this->cover->_id === null) {
            $file               = $this->cover === null ? new CFile() : $this->cover;
            $file->file_name    = self::COVER_NAME;
            $file->object_class = $this->_class;
            $file->object_id    = $this->_id;

            $file->loadMatchingObjectEsc();

            if ($this->cover === null) {
                $file->file_type = 'image/jpg';
                $file->setCopyFrom(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . self::COVER_PATH);
            } else {
                $file->setContent(base64_decode($file->_base64_content));
            }

            $file->fillFields();
            if ($msg = $file->store()) {
                throw new CMbException($msg);
            }

            $this->cover = $file;
        }
    }

    /**
     * Load the cover of the movie.
     *
     * @throws Exception
     */
    public function loadCover(): ?CFile
    {
        /** @var CFile $cover */
        $cover = $this->loadUniqueBackRef(
            'files',
            null,
            null,
            null,
            null,
            null,
            ['file_name' => $this->getDS()->prepare('= ?', self::COVER_NAME)]
        );

        return $this->cover = ($cover && $cover->_id ? $cover : null);
    }

    /**
     * Should optimize to use cache. Currently cache for backrefs with a condition (force user_id) is broken.
     *
     * @throws ApiException
     */
    public function getResourceBookmarks(): ?Item
    {
        $bookmark = $this->loadUniqueBackRef(
            'bookmarked_by',
            null,
            1,
            null,
            null,
            null,
            ['user_id' => $this->getDS()->prepare('= ?', CMediusers::get()->_id)]
        );

        return $bookmark && $bookmark->_id ? new Item($bookmark) : null;
    }
}
