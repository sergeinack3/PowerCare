<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CMbString;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Admin\CUser;
use Throwable;

/**
 * Description
 */
class CMergeLog extends CStoredObject
{
    /** @var int Primary key */
    public $merge_log_id;

    public $user_id;

    public $object_class;

    public $base_object_id;

    public $object_ids;

    public $fast_merge;

    public $date_start_merge;

    public $merge_checked;

    public $date_before_merge;

    public $date_after_merge;

    public $date_end_merge;

    public $duration;

    public $count_merged_relations;

    public $detail_merged_relations;

    public $last_error_handled;

    /** @var CUser */
    public $_ref_user;

    /** @var CStoredObject|null */
    public $_ref_base;

    /** @var CStoredObject[] */
    public $_ref_objects = [];

    /** @var array */
    public $_object_ids = [];

    /** @var array */
    public $_detail;

    /** @var string */
    public $_detail_pretty;

    public $_min_date_start_merge;
    public $_max_date_start_merge;
    public $_min_date_end_merge;
    public $_max_date_end_merge;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec = parent::getSpec();

        $spec->table = 'merge_log';
        $spec->key   = 'merge_log_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['user_id']        = 'ref class|CUser notNull unlink back|merge_logs_user';
        $props['object_class']   = 'str notNull autocomplete';
        $props['base_object_id'] = 'ref class|CStoredObject meta|object_class notNull unlink back|merge_logs_base';
        $props['object_ids']     = 'str notNull';

        $props['fast_merge']              = 'bool notNull';
        $props['date_start_merge']        = 'dateTime notNull';
        $props['merge_checked']           = 'bool notNull';
        $props['date_before_merge']       = 'dateTime';
        $props['date_after_merge']        = 'dateTime';
        $props['date_end_merge']          = 'dateTime';
        $props['duration']                = 'num';
        $props['count_merged_relations']  = 'num min|0 notNull default|0';
        $props['detail_merged_relations'] = 'text';
        $props['last_error_handled']      = 'text';

        $props['_min_date_start_merge'] = 'dateTime';
        $props['_max_date_start_merge'] = 'dateTime';
        $props['_min_date_end_merge']   = 'dateTime';
        $props['_max_date_end_merge']   = 'dateTime';

        return $props;
    }

    /**
     * @inheritDoc
     */
    public function updateFormFields()
    {
        parent::updateFormFields();

        if ($this->object_ids) {
            $this->_object_ids = explode('-', $this->object_ids);
        }

        if ($this->detail_merged_relations) {
            $this->_detail        = json_decode($this->detail_merged_relations, true);
            $this->_detail_pretty = json_encode($this->_detail, JSON_PRETTY_PRINT);
        }
    }

    public static function logStart(
        string $user_id,
        CStoredObject $base_object,
        array $objects,
        bool $fast
    ): self {
        $log = new self();

        $log->user_id                 = $user_id;
        $log->object_class            = $base_object->_class;
        $log->base_object_id          = $base_object->_id;
        $log->object_ids              = implode('-', array_column($objects, '_id'));
        $log->fast_merge              = ($fast) ? '1' : '0';
        $log->date_start_merge        = CMbDT::dateTime();
        $log->merge_checked           = '0';
        $log->count_merged_relations  = 0;
        $log->detail_merged_relations = '{}';

        $log->store();

        return $log;
    }

    public function logCheck(): void
    {
        if (!$this->_id) {
            return;
        }

        $this->merge_checked = '1';

        $this->store();
    }

    public function logBefore(): void
    {
        if (!$this->_id) {
            return;
        }

        $this->date_before_merge = CMbDT::dateTime();

        $this->store();
    }

    public function logAfter(): void
    {
        if (!$this->_id) {
            return;
        }

        $this->date_after_merge = CMbDT::dateTime();

        $this->store();
    }

    public function logDetailMergedRelations(array $detail): void
    {
        if (!$this->_id) {
            return;
        }

        $this->detail_merged_relations = CMbArray::toJSON($detail, true);
        $this->count_merged_relations  = array_sum($detail);

        $this->store();
    }

    public function logEnd(): void
    {
        if (!$this->_id) {
            return;
        }

        $this->date_end_merge = CMbDT::dateTime();
        $this->duration       = $this->computeDuration();

        $this->store();
    }

    public function logFromThrowable(Throwable $throwable): void
    {
        if (!$this->_id) {
            return;
        }

        $error_message            = CMbString::purifyHTML($throwable->getMessage());
        $this->last_error_handled = $error_message;

        $this->duration = $this->computeDuration();

        $this->store();
    }

    private function computeDuration(): int
    {
        if (!$this->date_start_merge) {
            return 0;
        }

        return time() - strtotime($this->date_start_merge);
    }

    public function wasSuccessful(): bool
    {
        return ($this->_id && $this->date_end_merge/* && ($this->last_error_handled === null)*/);
    }

    public function loadRefUser(): ?CUser
    {
        return $this->_ref_user = $this->loadFwdRef('user_id');
    }

    public function loadBaseObject(): ?CStoredObject
    {
        return $this->_ref_base = $this->loadFwdRef('base_object_id');
    }

    public function loadObjects(): array
    {
        $objects = [];

        if (!$this->object_ids) {
            return $this->_ref_objects = $objects;
        }

        foreach ($this->_object_ids as $_object_id) {
            try {
                $_object = call_user_func([$this->object_class, 'findOrFail'], $_object_id);
            } catch (Exception $e) {
                continue;
            }

            $objects[$_object->_id] = $_object;
        }

        return $this->_ref_objects = $objects;
    }

    /**
     * @return string
     */
    public function getValidObjectIds(): string
    {
        if (!$this->baseObjectStillExists() || !$this->canBeMergedAgain()) {
            return '';
        }

        $object_ids = [
            $this->base_object_id,
        ];

        $object_ids = array_merge($object_ids, array_keys($this->loadObjects()));

        return implode('-', $object_ids);
    }

    /**
     * @return bool
     */
    private function baseObjectStillExists(): bool
    {
        try {
            $base = call_user_func([$this->object_class, 'findOrFail'], $this->base_object_id);
        } catch (Exception $e) {
            return false;
        }

        return ($base && $base->_id);
    }

    /**
     * @param string|null $object_id
     *
     * @return bool
     */
    public function canBeMergedAgain(?string $object_id = null): bool
    {
        if (!$this->baseObjectStillExists()) {
            return false;
        }

        $this->loadObjects();

        if ($object_id === null) {
            return !empty($this->_ref_objects);
        }

        return isset($this->_ref_objects);
    }
}
