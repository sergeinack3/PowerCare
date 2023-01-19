<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Entity;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CRequest;
use Ox\Core\CStoredObject;
use Ox\Import\Framework\Configuration\Configuration;
use Ox\Import\Framework\Exception\ImportException;
use Ox\Import\GenericImport\CImportFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Description
 */
class CImportCampaign extends CMbObject
{
    /** @var int Primary key */
    public $import_campaign_id;

    // Todo: Use this name for import tags ?
    /** @var string */
    public $name;

    /** @var string */
    public $creation_date;

    /** @var string */
    public $closing_date;

    /** @var int */
    public $creator_id;

    /** @var Configuration */
    public $_import_configuration;

    public $_creation_date_min;
    public $_creation_date_max;

    public $_closing_date_min;
    public $_closing_date_max;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = 'import_campaign';
        $spec->key   = 'import_campaign_id';

        $spec->uniques['campaign'] = ['name'];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props                  = parent::getProps();
        $props['name']          = 'str notNull';
        $props['creation_date'] = 'dateTime notNull';
        $props['closing_date']  = 'dateTime moreThan|creation_date';
        $props['creator_id']    = 'ref class|CMediusers notNull back|created_import_campaigns';

        $props['_creation_date_min'] = 'dateTime';
        $props['_creation_date_max'] = 'dateTime';
        $props['_closing_date_min']  = 'dateTime';
        $props['_closing_date_max']  = 'dateTime';

        return $props;
    }

    /**
     * @inheritDoc
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();

        $this->_view = $this->name;
    }

    /**
     * @inheritDoc
     */
    public function store(): ?string
    {
        if (!$this->_id) {
            $this->creation_date = ($this->creation_date) ?: CMbDT::dateTime();
            $this->creator_id    = ($this->creator_id) ?: CMediusers::get()->_id;
        }

        return parent::store();
    }

    /**
     * @return void
     * @throws CMbException
     */
    public function close(): void
    {
        if (!$this->_id) {
            throw new CMbException('CImportCampaign-error-Campaign does not exist');
        }

        $this->completeField('closing_date');

        if ($this->closing_date) {
            throw new CMbException('CImportCampaign-error-Campaign already closed');
        }

        $this->closing_date = CMbDT::dateTime();

        if ($msg = $this->store()) {
            throw new CMbException($msg);
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $external_class
     * @param string $external_id
     *
     * @return CImportEntity|null
     */
    public function getImportedEntity(string $external_class, string $external_id): ?CImportEntity
    {
        $import_entity                     = new CImportEntity();
        $import_entity->import_campaign_id = $this->_id;
        $import_entity->external_class     = $external_class;
        $import_entity->external_id        = $external_id;

        if ($import_entity->loadMatchingObjectEsc()) {
            // TODO Check if this is usefull to keep the object ref
            return $this->_back['import_entities'][$import_entity->_id] = $import_entity;
        }

        return null;
    }

    /**
     * @param EntityInterface    $external_object
     * @param CStoredObject|null $internal_object
     * @param string             $error_msg
     *
     * @return void
     * @throws ImportException
     *
     */
    public function addImportedObject(
        EntityInterface $external_object,
        CStoredObject $internal_object = null,
        string $error_msg = ''
    ): void {
        $entity = new CImportEntity();
        $entity->setExternalObject($external_object);

        // Loadmatching before adding CMbObject
        $entity->import_campaign_id = $this->_id;
        $entity->loadMatchingObjectEsc();

        if ($internal_object) {
            $entity->setInternalObject($internal_object);
        }

        $entity->last_error = $error_msg;
        if ($msg = $entity->store()) {
            throw new ImportException($msg);
        }

        if ($internal_object) {
            $this->addExternalId($internal_object, $external_object->getExternalId());
        }
    }

    /**
     * @param CStoredObject $object
     * @param string        $ext_id
     *
     * @return void
     * @throws ImportException
     *
     * @todo Remove this when CIdSante400 are not necessary anymore
     *
     */
    private function addExternalId(CStoredObject $object, string $ext_id): void
    {
        $idx               = new CIdSante400();
        $idx->object_class = $object->_class;
        $idx->object_id    = $object->_id;
        $idx->tag          = $this->name;
        $idx->id400        = $ext_id;
        $idx->loadMatchingObjectEsc();

        $idx->_ignore_eai_handlers = true;

        if ($msg = $idx->store()) {
            throw new ImportException($msg);
        }
    }

    /**
     * @return CImportCampaign[]|null
     * @throws Exception
     */
    public static function getCampaignsInProgress()
    {
        $campaign = new self();
        $ds       = $campaign->getDS();

        $where = [
            'closing_date'  => 'IS NULL',
            'creation_date' => $ds->prepare('< ?', CMbDT::dateTime()),
        ];

        return $campaign->loadList($where);
    }

    /**
     * @return CImportCampaign|null
     * @throws Exception
     */
    public static function getLastCampaign(): ?CImportCampaign
    {
        $campaign = new self();
        $ds       = $campaign->getDS();

        $where = [
            'closing_date'  => 'IS NULL',
            'creation_date' => $ds->prepare('< ?', CMbDT::dateTime()),
        ];

        $campaign->loadObject($where, "creation_date DESC");

        return $campaign;
    }

    /**
     * @param Configuration $configuration
     *
     * @return void
     */
    public function setConfiguration(Configuration $configuration): void
    {
        $this->_import_configuration = $configuration;
    }

    /**
     * @return Configuration
     */
    public function getConfiguration(): ?Configuration
    {
        return $this->_import_configuration;
    }

    /**
     * @param string $type all|valid|error
     * @param bool   $counts
     *
     * @return array
     * @throws Exception
     */
    public function getImportedEntities(string $type = 'valid', $counts = false): array
    {
        if (!$this->_id) {
            return [];
        }

        $entity = new CImportEntity();
        $ds     = $entity->getDS();

        $column = 'external_class';

        $select = [
            "{$column} as class_name",
        ];

        if ($counts) {
            $select[] = 'COUNT(*) as count';
        }

        $where = [
            'import_campaign_id' => $ds->prepare('= ?', $this->_id),
        ];

        switch ($type) {
            case 'valid':
                $where['internal_class'] = 'IS NOT NULL';
                break;

            case 'error':
                $where['internal_class'] = 'IS NULL';
                break;

            default:
        }

        $query = new CRequest();
        $query->addSelect($select);
        $query->addTable($entity->_spec->table);
        $query->addWhere($where);
        $query->addGroup($column);

        return $ds->loadList($query->makeSelect());
    }

    /**
     * @param string $class_name
     * @param string $type all|valid|error
     * @param int    $start
     * @param int    $step
     *
     * @return array
     * @throws Exception
     */
    public function loadEntityByClass(string $class_name, string $type = 'valid', int $start = 0, int $step = 50): array
    {
        if (!$this->_id) {
            return [];
        }

        $ds = $this->getDS();

        $where = [
            'external_class' => $ds->prepare('= ?', $class_name),
        ];

        switch ($type) {
            case 'valid':
                $where['last_error'] = 'IS NULL';
                break;

            case 'error':
                $where['last_error'] = 'IS NOT NULL';
                break;

            default:
        }

        return $this->loadBackRefs(
            'import_entities',
            'last_import_date DESC',
            "$start,$step",
            null,
            null,
            null,
            null,
            $where
        );
    }

    /**
     * @param string $class_name
     * @param string $type all|valid|error
     *
     * @return int
     * @throws Exception
     */
    public function countEntityByClass(string $class_name, string $type = 'valid'): int
    {
        if (!$this->_id) {
            return 0;
        }

        $ds = $this->getDS();

        $where = [
            'external_class' => $ds->prepare('= ?', $class_name),
        ];

        switch ($type) {
            case 'valid':
                $where['last_error'] = 'IS NULL';
                break;

            case 'error':
                $where['last_error'] = 'IS NOT NULL';
                break;

            default:
        }

        return $this->countBackRefs('import_entities', $where);
    }

    /**
     * Get all mapped files for this campaign
     * @throws Exception
     */
    public function getMappedFiles(): array
    {
        $files        = $this->loadBackRefs('import_files');
        $mapped_files = [];

        /** @var CImportFile $file */
        foreach ($files as $file) {
            if ($file->entity_type) {
                $mapped_files[] = $file->entity_type;
            }
        }

        return $mapped_files;
    }
}
