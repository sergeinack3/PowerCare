<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Entity;

use Exception;
use Ox\Core\CClassMap;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CStoredObject;
use Ox\Import\Framework\ImportableInterface;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Description
 */
class CImportEntity extends CMbObject
{
    /** @var array */
    private static $importable_classes = [];

    /** @var int Primary key */
    public $import_entity_id;

    /** @var int */
    public $import_campaign_id;

    /** @var string */
    public $last_import_date;

    /** @var string */
    public $external_id;

    /** @var string */
    public $external_class;

    /** @var int */
    public $internal_id;

    /** @var string */
    public $internal_class;

    /** @var bool */
    public $reimport;

    /** @var string */
    public $last_error;

    /** @var CMbObject */
    public $_internal_object;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec                             = parent::getSpec();
        $spec->table                      = 'import_entity';
        $spec->key                        = 'import_entity_id';
        $spec->uniques['imported_object'] = ['import_campaign_id', 'external_class', 'external_id'];
        $spec->loggable                   = false;

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props                       = parent::getProps();
        $props['import_campaign_id'] = 'ref class|CImportCampaign notNull back|import_entities';
        $props['last_import_date']   = 'dateTime notNull';
        $props['external_id']        = 'str notNull';
        $props['external_class']     = 'str notNull';

        $props['internal_id']    = 'ref meta|internal_class back|import_entities cascade';
        $props['internal_class'] = 'enum list|' . implode('|', $this->getImportableClasses());

        $props['reimport'] = 'bool notNull default|0';

        $props['last_error'] = 'text';

        return $props;
    }

    /**
     * @inheritDoc
     */
    public function store(): ?string
    {
        $this->last_import_date = CMbDT::dateTime();

        // Todo: Purify last_error text

        return parent::store();
    }

    /**
     * @return mixed|CMbObject|null
     * @throws Exception
     */
    public function getInternalObject()
    {
        if (!$this->internal_class || !$this->internal_id) {
            return null;
        }

        $this->_internal_object = new $this->internal_class();
        $this->_internal_object->load($this->internal_id);

        return ($this->_internal_object->_id) ? $this->_internal_object : null;
    }

    /**
     * @param EntityInterface $object
     *
     * @return void
     */
    public function setExternalObject(EntityInterface $object)
    {
        $this->external_id    = $object->getExternalId();
        $this->external_class = $object->getExternalClass();
    }

    /**
     * @param CStoredObject $object
     *
     * @return void
     */
    public function setInternalObject(CStoredObject $object)
    {
        $this->internal_id    = $object->_id;
        $this->internal_class = $object->_class;
    }

    /**
     * Tell whether an entity is in error
     *
     * @return bool
     */
    public function isInError(): bool
    {
        return (!$this->external_class || !$this->external_id || ($this->last_error !== null));
    }

    public static function getMapped(CImportCampaign $import_campaign, string $type): array
    {
        $entity                     = new self();
        $entity->import_campaign_id = $import_campaign->_id;
        $entity->external_class     = $type;

        return $entity->loadMatchingList();
    }

    private function getImportableClasses(): array
    {
        if (empty(self::$importable_classes)) {
            foreach (CClassMap::getInstance()->getClassChildren(ImportableInterface::class) as $_class_name) {
                self::$importable_classes[] = CClassMap::getSN($_class_name);
            }

            self::$importable_classes[] = CClassMap::getSN(CMediusers::class);
        }

        return self::$importable_classes;
    }
}
