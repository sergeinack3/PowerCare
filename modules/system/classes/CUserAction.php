<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use FineDiff\Diff;
use FineDiff\Granularity\Word;
use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CModelObject;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\FieldSpecs\CHtmlSpec;
use Ox\Core\FieldSpecs\CTextSpec;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * The CUserAction Class
 */
class CUserAction extends CStoredObject
{
    // DB Table key
    public $user_action_id;

    // DB Fields
    public $user_id;
    public $date;
    public $type;
    public $ip_address;
    public $object_id;
    public $object_class_id;

    // Filter Fields
    public $_date_min;
    public $_date_max;

    public $_datas;

    // Object References
    public $_ref_user_action_datas;
    public $_ref_user;
    public $_ref_object_class;
    public $_ref_object;

    public $_old_values;
    public $_diff_values;
    public $_canUndo;
    public $_undo;

    public $_merged_ids; // Tableau d'identifiants des objets fusionnés

    /**
     * Counts the recent user logs
     *
     * @param string   $object_class The object class
     * @param string[] $ids          The list of IDs
     * @param string   $recent       The date considered as recent
     *
     * @return int
     * @deprecated no use
     */
    static function countRecentFor($object_class, $ids, $recent)
    {
        if (!count($ids)) {
            return 0;
        }

        $log                   = new CUserLog();
        $where                 = [];
        $where["object_class"] = "= '$object_class'";
        $where["date"]         = "> '$recent'";
        $where["object_id"]    = CSQLDataSource::prepareIn($ids);

        return $log->countList($where);
    }

    /**
     * @param string $start           Datetime where the search starts
     * @param string $end             Datetime where the search ends
     * @param string $period          Aggregation period
     * @param string $type            User log type to filter
     * @param int    $user_id         User ID to filter
     * @param string $object_class_id Class to filter
     * @param int    $object_id       Object ID to filter
     *
     * @return array|bool
     * @throws Exception
     * @todo ref after migration
     * Load period aggregation for the system view
     *
     */
    static function loadPeriodAggregation(
        $start,
        $end,
        $period,
        $type = null,
        $user_id = null,
        $object_class_id = null,
        $object_id = null
    ) {
        switch ($period) {
            default:
            case "hour":
                $period_format = "%d-%m-%Y %Hh";
                break;

            case "day":
                $period_format = "%d-%m-%Y";
                break;

            case "week":
                $period_format = "%Y Sem. %u";
                break;

            case "month":
                $period_format = "%m-%Y";
                break;

            case "year":
                $period_format = "%Y";
        }

        $query = "SELECT
        COUNT(*) AS count,
      DATE_FORMAT(`date`, '$period_format') AS `gperiod`
      FROM `user_action`
      USE INDEX (date)
      WHERE `date` >= '$start'";

        if ($end) {
            $query .= "\nAND `date` <= '$end'";
        }

        if ($type) {
            $query .= "\nAND `type` = '$type'";
        }

        if ($user_id) {
            $query .= "\nAND `user_id` = '$user_id'";
        }

        if ($object_class_id) {
            $query .= "\nAND `object_class_id` = '$object_class_id'";
        }

        if ($object_id) {
            $query .= "\nAND `object_id` = '$object_id'";
        }

        $query .= "\nGROUP BY `gperiod` ORDER BY `date`";

        $log = new self;

        return $log->_spec->ds->loadList($query);
    }

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec              = parent::getSpec();
        $spec->loggable    = false;
        $spec->table       = 'user_action';
        $spec->key         = 'user_action_id';
        $spec->measureable = true;

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                    = parent::getProps();
        $props["user_id"]         = "ref notNull class|CUser back|owned_actions";
        $props["object_id"]       = "ref notNull class|CStoredObject meta|object_class_id unlink back|user_actions";
        $props["object_class_id"] = "ref notNull class|CObjectClass back|user_action";
        $props["date"]            = "dateTime notNull";
        $props["type"]            = "enum notNull list|create|store|merge|delete";
        $props["ip_address"]      = "ipAddress";

        $props["_date_min"] = "dateTime";
        $props["_date_max"] = "dateTime moreEquals|_date_min";

        return $props;
    }

    function loadRefObject()
    {
        return $this->_ref_object = $this->loadFwdRef('object_id');
    }

    /**
     * @deprecated
     * @inheritdoc
     */
    function loadRefsNotes($perm = PERM_READ)
    {
        $this->_ref_notes = [];

        return 0;
    }

    /**
     * @deprecated
     * @inheritdoc
     */
    function loadLogs()
    {
        $this->_ref_logs = [];
    }

    /**
     * @deprecated
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();
        //    if ($this->fields) {
        //      $this->_fields = explode(" ", $this->fields);
        //    }
    }

    /**
     * @deprecated
     * @inheritdoc
     */
    function updatePlainFields()
    {
        parent::updatePlainFields();

        //    if ($this->_fields) {
        //      $this->fields = implode(" ", $this->_fields);
        //    }
    }

    /**
     * Load the object class
     *
     * @param bool $cache Use object cache
     *
     * @return CUser
     */
    function loadRefObjectClass($cache = true)
    {
        return $this->_ref_object_class = $this->loadFwdRef("object_class_id", $cache);
    }

    /**
     * @inheritdoc
     * @deprecated
     */
    function loadRefsFwd()
    {
        parent::loadRefsFwd();
        $this->loadRefUser();
    }

    /**
     * Load the user who did the change
     *
     * @param bool $cache Use object cache
     *
     * @return CUser
     */
    function loadRefUser($cache = true)
    {
        return $this->_ref_user = $this->loadFwdRef("user_id", $cache);
    }

    /**
     * @inheritdoc
     */
    function loadView()
    {
        parent::loadView();

        $this->getOldValues();
        $this->canUndo();
        $this->loadTargetObject()->loadHistory();
    }

    /**
     * Gets old values (before the change happened)
     *
     * @return array
     */
    function getOldValues()
    {
        $this->_old_values = [];
        if ($this->_ref_user_action_datas && ($this->type === "store" || $this->type === "merge")) {
            foreach ($this->_ref_user_action_datas as $_user_action_data) {
                $_field = $_user_action_data->field;
                $_value = $_user_action_data->value;
                if ($_uncompress = @gzuncompress($_value ?? '')) {
                    $_value = $_uncompress;
                }
                $this->_old_values[$_field] = $_value;
            }
        }

        return $this->_old_values;
    }

    /**
     * Tells if we can undo the change
     *
     * @return bool
     */
    function canUndo()
    {
        $this->loadRefUserActionDatas();
        $this->completeField("type");

        if (!$this->_id || ($this->type != "store") || empty($this->_ref_user_action_datas) || !$this->canEdit(
            ) || !$this->_ref_module->canAdmin()) {
            return $this->_canUndo = false;
        }

        $this->completeField("object_id", "object_class_id");

        $where = [
            "object_id"           => "= '$this->object_id'",
            "object_class_id"     => "= '$this->object_class_id'",
            "{$this->_spec->key}" => "> '$this->_id'",
        ];

        return $this->_canUndo = ($this->countList($where) == 0);
    }

    /**
     * Load the user action data
     *
     * @param bool $cache Use object cache
     *
     * @return CUser
     */
    function loadRefUserActionDatas($cache = true)
    {
        return $this->_ref_user_action_datas = $this->loadBackRefs("user_action_datas");
    }

    /**
     * @param bool $cache Utilisation du cache
     *
     * @return CMbObject
     * @todo ref after migration
     *       Load target of meta object
     *
     */
    function loadTargetObject($cache = true)
    {
        return $this->_ref_object = $this->loadFwdRef("object_id", $cache);

        if ($this->_ref_object || !$this->object_class) {
            return $this->_ref_object;
        }

        if (!class_exists($this->object_class)) {
            $ex_object = CExObject::getValidObject($this->object_class);

            if (!$ex_object) {
                CModelObject::error("Unable-to-create-instance-of-object_class%s-class", $this->object_class);

                return null;
            } else {
                $ex_object->load($this->object_id);
                $this->_ref_object = $ex_object;
            }
        } else {
            $this->_ref_object = $this->loadFwdRef("object_id", $cache);
        }

        if (!$this->_ref_object->_id) {
            $this->_ref_object->load(null);
            $this->_ref_object->_view = "Element supprimé";
        }

        return $this->_ref_object;
    }

    /**
     * @return string[]
     * @deprecated no use
     *             Gets all the IDs implied in the merging
     *
     */
    function loadMergedIds()
    {
        if ($this->type === "merge") {
            $date_max = CMbDT::dateTime("+3 seconds", $this->date);
            $where    = [
                "user_id" => "= '$this->user_id'",
                "type"    => " = 'delete'",
                "date"    => "BETWEEN '$this->date' AND '$date_max'",
            ];

            /** @var self[] $logs */
            $logs = $this->loadList($where);

            foreach ($logs as $_log) {
                $this->_merged_ids[] = $_log->object_id;
            }
        }
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        if ($msg = $this->check()) {
            return $msg;
        }

        if ($this->_undo) {
            $this->_undo = null;

            return $this->undo();
        }

        $return = parent::store();

        // Store user_action_data
        $this->_ref_user_action_datas = [];
        $this->_datas                 = is_array($this->_datas) ? $this->_datas : [];

        foreach ($this->_datas as $field => $value) {
            // Prevent runtime error
            if (!$field) {
                continue;
            }
            $CUserActionData                 = new CUserActionData();
            $CUserActionData->user_action_id = $this->_id;
            $CUserActionData->field          = $field;
            $CUserActionData->value          = $value;
            $CUserActionData->rawStore();
            $this->_ref_user_action_datas[$CUserActionData->_id] = $CUserActionData;
        }

        // @todo witch result return ? transaction ?
        return $return;
    }

    /**
     *
     * Undo the change
     *
     * @return null|string
     */
    function undo()
    {
        if (!$this->canUndo()) {
            return "CUserLog-undo-ko";
        }

        $object = $this->loadTargetObject();
        $object->loadHistory();
        $object->_spec->loggable = false;

        $this->getOldValues();
        $this->undiff_old_Values(false);

        // Revalue fields
        foreach ($this->_old_values as $_field => $_value) {
            $object->$_field = $_value;
        }
        $object->updateFormFields();

        // Prevent disturbing checks
        $object->_merging = true;

        $msg = $object->store();

        $object->_spec->loggable = true;

        if ($msg) {
            return $msg;
        }

        return $this->delete();
    }

    /**
     * Replace diff opcodes in _old_values (using object history)
     * Construct diff html from previous to next value
     *
     * @param bool $nl2br
     *
     * @throws Exception
     */
    public function undiff_old_Values($nl2br = true)
    {
        $this->_diff_values = [];
        $granularity        = new Word();
        $diff               = new Diff($granularity);

        // Mock deleted object ref (the last log store the object->datas)
        if (!$this->_ref_object->_id) {
            $class = $this->object_class;
            /** @var CStoredObject $_ref_object */
            $_ref_object      = new $class;
            $_ref_object->_id = $this->object_id;
            $last_log         = $_ref_object->loadLastLog();
            $_old_values      = $last_log->getOldValues(true);
            $_key             = $_ref_object->_spec->key;
            unset($_old_values[$_key]);
            foreach ($_old_values as $_field => $_value) {
                $_ref_object->$_field = $_value;
            }
            $this->_ref_object = $_ref_object;
            $this->_ref_object->loadHistory();
        }

        $_history         = $this->_ref_object->_history;
        $_history_key     = array_reverse(array_keys($_history));
        $_current_key     = array_search($this->_id, $_history_key);
        $_previous_log_id = $_current_key > 0 ? $_history_key[$_current_key - 1] : false;

        foreach ($this->_old_values as $_filed => $_value) {
            $_from = null;
            if (isset($_history[$_previous_log_id])) {
                $_from = $_history[$_previous_log_id][$_filed];
            }

            $_to = null;
            if (isset($_history[$this->_id])) {
                $_to = $_history[$this->_id][$_filed];
            }

            $_sepc = $this->_ref_object->_specs[$_filed];
            if ($_previous_log_id && ($_sepc instanceof CTextSpec || $_sepc instanceof CHtmlSpec)) {
                $this->_old_values[$_filed]  = $_from;
                $render                      = $diff->render($_from, $_to);
                $this->_diff_values[$_filed] = $nl2br ? nl2br($render) : $render;
            } else {
                $_from_html = $this->getValue($_filed, $_from);
                $_to_html   = $this->getValue($_filed, $_to, true);
                if (!CMbString::isHtml($_from_html) && !CMbString::isHtml($_to)) {
                    $render                      = $diff->render($_from_html, $_to_html);
                    $this->_diff_values[$_filed] = nl2br(html_entity_decode($render));
                }
            }
        }
    }


    private function getValue($field, $value, $accept_empty_value = false)
    {
        $object = $this->_ref_object;

        if ($value !== null || $accept_empty_value) {
            $object->$field = $value;
        }

        /** @var CMbFieldSpec $spec */
        $spec   = $object->_specs[$field];
        $params = [
            'accept_empty_value' => $accept_empty_value,
            'tooltip'            => 1,
        ];

        return $spec->getHtmlValue($object, $params);
    }

    /**
     * @inheritdoc
     */
    function canDeleteEx()
    {
        if (!$this->canEdit() || !$this->_ref_module->canAdmin()) {
            return false;
        }

        return parent::canDeleteEx();
    }

    /**
     * @duplicate from user_log
     * Count logs by period aggregation
     *
     * @param string $date_min      Datetime where the search starts
     * @param string $date_max      Datetime where the search ends
     * @param string $period_format Aggregation period format
     * @param int    $user_id       User ID to filter
     * @param string $type          User log type to filter
     * @param string $object_class  Class to filter
     * @param int    $object_id     Object ID to filter
     *
     * @return array
     */
    static function countPeriodAggregation(
        $date_min,
        $date_max,
        $period_format,
        $user_id = null,
        $type = null,
        $object_class = null,
        $object_id = null
    ) {
        // Convert date format from PHP to MySQL
        $period_format = str_replace("%M", "%i", $period_format);

        $query = "
       SELECT
        DATE_FORMAT(`date`, '$period_format') AS `gperiod`,
        COUNT(*) AS `count`
      FROM `user_action`
      USE INDEX (`date`)
      WHERE `date` BETWEEN '$date_min' AND '$date_max'";

        if ($type) {
            $query .= "\nAND `type` = '$type'";
        }

        if ($user_id) {
            $query .= "\nAND `user_id` = '$user_id'";
        }

        if ($object_class) {
            $object_class_id = CObjectClass::getID($object_class);
            $query .= "\nAND `object_class_id` = '$object_class_id'";
        }

        if ($object_id) {
            $query .= "\nAND `object_id` = '$object_id'";
        }

        $query .= "\nGROUP BY `gperiod` ORDER BY `date`";

        $that = new self;

        return $that->_spec->ds->loadHashList($query);
    }
}
