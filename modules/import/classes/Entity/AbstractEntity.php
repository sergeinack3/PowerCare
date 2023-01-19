<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Entity;

use Closure;
use Exception;
use Ox\Core\CStoredObject;
use Throwable;

/**
 * External object generic functions to use to create a CMbObject
 */
abstract class AbstractEntity implements EntityInterface, ValidationAwareInterface
{
    /** @var mixed */
    protected $external_id;

    /** @var callable */
    protected $custom_ref_entities_callable;

    /** @var CStoredObject */
    protected $mb_object = null;

    /**
     * AbstractEntity constructor.
     */
    final public function __construct()
    {
    }

    /**
     * @inheritDoc
     */
    public function getExternalID()
    {
        return $this->external_id;
    }

    /**
     * @inheritDoc
     */
    public static function fromState(array $data): EntityInterface
    {
        // Not allowed
        if (array_key_exists('custom_ref_entities_callable', $data)) {
            throw new Exception();
        }

        $object = new static();
        $object->mapStateByProperty($data);

        return $object;
    }

    /**
     * Default state mapping
     *
     * @param array $state
     *
     * @return void
     */
    private function mapStateByProperty(array $state): void
    {
        foreach ($state as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
    }

    public function setCustomRefEntities(callable $ref_entities_callable): void
    {
        $this->custom_ref_entities_callable = $ref_entities_callable;
    }

    final public function getRefEntities(): array
    {
        if ($this->custom_ref_entities_callable) {
            try {
                $closure = Closure::fromCallable($this->custom_ref_entities_callable);

                return $closure->call($this);
            } catch (Throwable $e) {
                throw new Exception();
            }
        }

        return $this->getDefaultRefEntities();
    }

    abstract public function getDefaultRefEntities(): array;

    public function getCollections(): array
    {
        return [];
    }

    public function setExternalId(string $ext_id): void
    {
        $this->external_id = $ext_id;
    }

    public function setMbObject(CStoredObject $mb_object): void
    {
        $this->mb_object = $mb_object;
    }

    public function getMbObject(): ?CStoredObject
    {
        return $this->mb_object;
    }
}
