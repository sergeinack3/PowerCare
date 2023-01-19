<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\DataModels;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Jfse\Exceptions\UserManagement\EstablishmentException;
use Ox\Mediboard\Mediusers\CFunctions;

final class CJfseEstablishment extends CMbObject
{
    /** @var array The list of authorized object classes */
    protected static $object_classes = ['CGroups', 'CFunctions'];

    /** @var int Primary key */
    public $jfse_establishments_id;

    /** @var int */
    public $jfse_id;

    /** @var int */
    public $object_id;

    /** @var string */
    public $object_class;

    /** @var string */
    public $creation;

    /** @var CMbObject */
    public $_object;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec = parent::getSpec();

        $spec->table = 'jfse_establishments';
        $spec->key   = 'jfse_establishment_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['jfse_id']       = 'str notNull';
        $props['object_id']     = 'ref meta|object_class back|jfse_establishment';
        $props['object_class']  = 'enum list|' . implode('|', self::$object_classes);
        $props['creation']      = 'dateTime notNull default|now';

        return $props;
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function store(): ?string
    {
        if (!$this->_id) {
            $this->creation = CMbDT::dateTime();
        }

        return parent::store();
    }

    /**
     * @param bool $cache
     *
     * @return CGroups|CFunctions
     */
    public function loadLinkedObject(bool $cache = true): ?CMbObject
    {
        if (!$this->_object) {
            try {
                $this->_object = $this->loadFwdRef('object_id', $cache);
            } catch (Exception $e) {
                $this->_object = null;
            }
        }

        return $this->_object;
    }

    /**
     * @param int    $object_id
     * @param string $object_class
     *
     * @return self
     */
    public function setLinkedObject(int $object_id, string $object_class): self
    {
        if (!in_array($object_class, self::$object_classes)) {
            EstablishmentException::unauthorizedObjectType($object_class);
        }

        try {
            $object = $object_class::findOrFail($object_id);
        } catch (Exception $e) {
            throw EstablishmentException::objectNotFound($object_class, $object_id, $e);
        }

        $this->object_id    = $object->_id;
        $this->object_class = $object->_class;

        return $this;
    }

    /**
     * @return self
     */
    public function unsetLinkedObject(): self
    {
        $this->object_id    = '';
        $this->object_class = '';

        return $this;
    }

    public static function getFromJfseId(int $jfse_id): self
    {
        $jfse_establishment = new self();
        $jfse_establishment->jfse_id = $jfse_id;
        $jfse_establishment->loadMatchingObjectEsc();

        return $jfse_establishment;
    }
}
