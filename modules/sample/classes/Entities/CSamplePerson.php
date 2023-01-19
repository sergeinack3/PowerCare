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
use Symfony\Component\Routing\RouterInterface;

/**
 * Representation of a person which can be used as actor or director
 */
class CSamplePerson extends CMbObject
{
    public const RESOURCE_TYPE = 'sample_person';

    public const RELATION_NATIONALITY = 'nationality';

    public const RELATION_MOVIES_PLAYED = 'moviesPlayed';

    public const RELATION_FILES = 'profilePicture';

    public const PROFILE_NAME = 'sample_person_profile';

    private const PROFILE_PATH = 'resources/Images/profile.png';

    private const PROFILE_URL = '?m=files&raw=thumbnail&document_id=%d&thumb=0';

    /** @var int */
    public $sample_person_id;

    /** @var string */
    public $last_name;

    /** @var string */
    public $first_name;

    /** @var string */
    public $birthdate;

    /** @var string */
    public $sex;

    /** @var int */
    public $nationality_id;

    /** @var string */
    public $activity_start;

    /** @var bool */
    public $is_director;

    /** @var CFile */
    private $profile_picture;

    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = "sample_person";
        $spec->key   = "sample_person_id";

        $spec->seek = 'match';

        return $spec;
    }

    public function getProps(): array
    {
        $props = parent::getProps();

        $props['last_name']      = 'str notNull seekable fieldset|' . self::FIELDSET_DEFAULT;
        $props['first_name']     = 'str notNull seekable fieldset|' . self::FIELDSET_DEFAULT;
        $props['birthdate']      = 'birthDate fieldset|' . self::FIELDSET_EXTRA;
        $props['sex']            = 'enum list|m|f fieldset|' . self::FIELDSET_EXTRA;
        $props['nationality_id'] = 'ref class|CSampleNationality back|persons';
        $props['activity_start'] = 'date moreThan|birthdate fieldset|' . self::FIELDSET_EXTRA;
        $props['is_director']    = 'bool default|0 fieldset|' . self::FIELDSET_DEFAULT;

        return $props;
    }

    public function store(): ?string
    {
        if ($msg = parent::store()) {
            return $msg;
        }

        try {
            $this->createProfilePicture();
        } catch (CMbException $e) {
            return $e->getMessage();
        }

        return null;
    }

    /**
     * Generate and return the self link.
     */
    public function getApiLink(RouterInterface $router): string
    {
        return $router->generate('sample_persons_show', ['sample_person_id' => $this->_id]);
    }

    /**
     * Get the nationality of the person
     *
     * @throws ApiException
     */
    public function getResourceNationality(): ?Item
    {
        $nationality = $this->loadFwdRef('nationality_id');
        return ($nationality && $nationality->_id) ? new Item($nationality) : null;
    }

    public function setResourceNationality(?JsonApiItem $json_nationality): void
    {
        $this->nationality_id = $json_nationality === null
            ? ''
            : $json_nationality->createModelObject(CSampleNationality::class, false)->getModelObject()->_id;
    }

    /**
     * Get all movies where the person have played.
     *
     * @return Collection|array Collection if movies is a non-empty array. An empty array if movies is empty.
     *
     * @throws ApiException
     */
    public function getResourceMoviesPlayed()
    {
        /** @var CSampleCasting[] $casting */
        $casting = $this->loadBackRefs('roles');

        $movies = [];
        foreach ($casting as $cast) {
            if ($movie = $cast->loadFwdRef('movie_id', true)) {
                $movies[] = $movie;
            }
        }

        return $movies ? new Collection($movies) : [];
    }

    /**
     * Get the relation 'profilePicture'.
     *
     * @throws ApiException
     */
    public function getResourceProfilePicture(): ?Item
    {
        if ($this->loadProfilePicture()) {
            $item = new Item($this->profile_picture);
            $item->addLinks($this->buildProfilePictureLink());

            return $item;
        }

        return null;
    }

    /**
     * Create a CFile using $item. The CFile will be the cover and is only composed of a file_type and the base64 of
     * the image.
     *
     * @throws ApiException|RequestContentException
     */
    public function setResourceProfilePicture(?JsonApiItem $item): void
    {
        if ($item !== null) {
            $this->profile_picture = $item->createModelObject(CFile::class, true)
                ->hydrateObject([], ['file_type', '_base64_content'])
                ->getModelObject();
        }
    }

    /**
     * Build the link to the profile picture.
     * Load the picture if needed.
     *
     * @throws Exception
     */
    public function buildProfilePictureLink(): array
    {
        if (!$this->profile_picture && !$this->loadProfilePicture()) {
            return [];
        }

        return ['profile_picture' => sprintf(self::PROFILE_URL, $this->profile_picture->_id)];
    }

    /**
     * Create the profile picture if it does not already exists.
     * Currently the profile picture is always the same but later the user will be able to upload the image.
     *
     * @throws CMbException
     */
    private function createProfilePicture(): void
    {
        if (!$this->_id) {
            throw new CMbException('CSamplePerson-Error-Cannot-create-profile-picture-for-non-stored-person');
        }

        if (!$this->profile_picture) {
            $this->loadProfilePicture();
        }

        if ($this->profile_picture === null || !$this->profile_picture->_id) {
            $file               = $this->profile_picture === null ? new CFile() : $this->profile_picture;
            $file->file_name    = self::PROFILE_NAME;
            $file->object_class = $this->_class;
            $file->object_id    = $this->_id;

            $file->loadMatchingObjectEsc();

            if ($this->profile_picture === null) {
                $file->file_type = 'image/png';
                $file->setCopyFrom(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . self::PROFILE_PATH);
            } else {
                $file->setContent(base64_decode($file->_base64_content));
            }

            $file->fillFields();
            if ($msg = $file->store()) {
                throw new CMbException($msg);
            }

            $this->profile_picture = $file;
        }
    }

    /**
     * Load the profile picture of the person.
     *
     * @throws Exception
     */
    private function loadProfilePicture(): ?CFile
    {
        /** @var CFile $profile */
        $profile = $this->loadUniqueBackRef(
            'files',
            null,
            null,
            null,
            null,
            null,
            ['file_name' => $this->getDS()->prepare('= ?', self::PROFILE_NAME)]
        );

        return $this->profile_picture = ($profile && $profile->_id ? $profile : null);
    }
}
