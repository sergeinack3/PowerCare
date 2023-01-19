<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Entities;

use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\Content\JsonApiItem;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Files\CFile;

/**
 * Representation of a cast member of a movie.
 * This is a join table between movies and persons.
 */
class CSampleCasting extends CMbObject
{
    public const RESOURCE_TYPE = 'sample_casting';

    public const RELATION_ACTOR = 'actor';

    /** @var int */
    public $sample_casting_id;

    /** @var int */
    public $actor_id;

    /** @var int */
    public $movie_id;

    /** @var bool */
    public $is_main_actor;

    /**
     * @inheritDoc
     *
     * An actor can only be on the casting of a movie once.
     * Unique keys are declared using the FW and not in unique index in MySQL.
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = "sample_casting";
        $spec->key   = "sample_casting_id";

        $spec->loggable = CMbObjectSpec::LOGGABLE_NEVER;

        $spec->uniques['actor_movie'] = ['actor_id', 'movie_id'];

        return $spec;
    }

    /**
     * Use the prop cascade for actor_id and movie_id to delete the object if the actor or the movie is deleted.
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['actor_id']      = 'ref class|CSamplePerson notNull cascade back|roles';
        $props['movie_id']      = 'ref class|CSampleMovie notNull cascade back|casting';
        $props['is_main_actor'] = 'bool default|0 notNull fieldset|default';

        return $props;
    }

    /**
     * Only one actor can be the main actor for a movie.
     */
    public function check(): ?string
    {
        $msg = parent::check();

        if ($this->is_main_actor && $this->hasMovieAMainActor()) {
            $msg .= CAppUI::tr('CSampleCasting-error-Only-one-actor-can-be-main-actor-for-a-movie');
        }

        return $msg;
    }

    /**
     * Get the actor from the casting.
     * Add the link to the profile picture of the actor.
     *
     * @throws ApiException
     */
    public function getResourceActor(): Item
    {
        /** @var CSamplePerson $actor */
        $actor = $this->loadFwdRef('actor_id');
        $item  = new Item($actor);
        $item->addLinks($actor->buildProfilePictureLink());

        return $item;
    }

    public function setResourceActor(?JsonApiItem $item): void
    {
        if ($item !== null) {
            /** @var CSamplePerson $actor */
            $actor = $item->createModelObject(CSamplePerson::class, false)->getModelObject();

            $this->actor_id = $actor->_id;
        }
    }

    /**
     * Check if a CSampleCasting already exists for this movie with is_main_actor to true.
     * If it already exists and is not the current CSampleCasting return true.
     */
    private function hasMovieAMainActor(): bool
    {
        $casting                = new self();
        $casting->movie_id      = $this->movie_id;
        $casting->is_main_actor = '1';
        $casting->loadMatchingObjectEsc();

        return $casting->_id && $casting->_id !== $this->_id;
    }
}
