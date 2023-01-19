<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Transformations;

use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CStoredObject;
use Ox\Interop\Eai\CInteropActor;

/**
 * Description
 */
class CLinkActorSequence extends CMbObject
{
    /** @var int Primary key */
    public $link_actor_sequence_id;

    /** @var int */
    public $actor_id;
    /** @var string */
    public $actor_class;
    /** @var int */
    public $sequence_id;

    /** @var CInteropActor */
    public $_ref_actor;
    /** @var CTransformationRuleSequence */
    public $_ref_sequence;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec = parent::getSpec();

        $spec->table = 'link_actor_sequence';
        $spec->key   = 'link_actor_sequence_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    public function getProps()
    {
        $props = parent::getProps();

        $props["actor_id"]    = "ref notNull class|CInteropActor meta|actor_class back|actor_transformations";
        $props["actor_class"] = "str notNull class maxLength|80";
        $props["sequence_id"] = "ref notNull class|CTransformationRuleSequence back|link_sequences";

        return $props;
    }

    /**
     * Load actor
     *
     * @return CInteropActor
     * @throws \Exception
     */
    public function loadRefActor(): CInteropActor
    {
        return $this->_ref_actor = $this->loadFwdRef("actor_id", true);
    }

    /**
     * Load rule sequence
     *
     * @return CTransformationRuleSequence|CStoredObject
     */
    public function loadRefSequence(): CTransformationRuleSequence
    {
        return $this->_ref_sequence = $this->loadFwdRef("sequence_id", true);
    }
}
