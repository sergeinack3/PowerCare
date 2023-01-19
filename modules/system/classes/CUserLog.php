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
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\FieldSpecs\CHtmlSpec;
use Ox\Core\FieldSpecs\CTextSpec;
use Ox\Core\Logger\LoggerLevels;
use Ox\Core\Mutex\CMbMutex;
use Ox\Core\Mutex\CMbRedisMutex;
use Ox\Core\Sessions\CRedisSessionHandler;
use Ox\Core\Sessions\CSessionHandler;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\Forms\CExObject;
use Symfony\Component\Routing\RouterInterface;

/**
 * The CUserLog Class
 */
class CUserLog extends CStoredObject
{

    /** @var int */
    public const USER_ACTION_START_AUTO_INCREMENT = 1000000000;

    /** @var string */
    public const RESOURCE_TYPE = 'history';

    /** @var string */
    public const RELATION_USER = 'user';

    // DB Table key
    public $user_log_id;

    // DB Fields
    public $user_id;
    public $date;
    public $type;
    public $fields;
    public $ip_address;

    public $object_class;
    public $object_id;
    public $_ref_object;

    // Filter Fields
    public $extra;
    public $_date_min;

    // Object References
    public $_date_max;
    public $_fields;
    public $_old_values;
    public $_diff_values;
    public $_ref_user;
    public $_canUndo;
    public $_undo; // Tableau d'identifiants des objets fusionnés
    public $_merged_ids;

    /**
     * @param string   $object_class The object class
     * @param string[] $ids          The list of IDs
     * @param string   $recent       The date considered as recent
     *
     * @return int
     * @deprecated
     * Counts the recent user logs
     *
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
     * @param CMbObject $object The object to get the value of
     * @param string    $date   The date
     * @param string    $field  Field name
     *
     * @return mixed
     * @deprecated
     * Gets the object value at a specific date
     *
     */
    static function getObjectValueAtDate(CMbObject $object, $date, $field)
    {
        $where = [
            "object_class" => "= '$object->_class'",
            "object_id"    => "= '$object->_id'",
            "type"         => "IN('store', 'merge')",
            "extra IS NOT NULL AND extra != '[]'",
        ];

        if ($date) {
            $where["date"] = ">= '$date'";
        }

        $where[] = "
      fields LIKE '$field' OR 
      fields LIKE '$field %' OR 
      fields LIKE '% $field' OR 
      fields LIKE '% $field %'";

        $user_log = new self;
        $user_log->loadObject($where, "date ASC");

        if ($user_log->_id) {
            $user_log->getOldValues();
        }

        return CValue::read($user_log->_old_values, $field, $object->$field);
    }

    /**
     * Gets old values (before the change happened)
     *
     * @return array
     */
    function getOldValues($allow_type_delete = false)
    {
        $this->_old_values = [];
        if ($this->extra && ($this->type === "store" || $this->type === "merge" || $allow_type_delete)) {
            $this->_old_values = (array)json_decode($this->extra);
            $this->_old_values = array_map("utf8_decode", $this->_old_values);
        }


        return $this->_old_values;
    }

    /**
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
      FROM `user_log`
      USE INDEX (`date`)
      WHERE `date` BETWEEN '$date_min' AND '$date_max'";

        if ($type) {
            $query .= "\nAND `type` = '$type'";
        }

        if ($user_id) {
            $query .= "\nAND `user_id` = '$user_id'";
        }

        if ($object_class) {
            $query .= "\nAND `object_class` = '$object_class'";
        }

        if ($object_id) {
            $query .= "\nAND `object_id` = '$object_id'";
        }

        $query .= "\nGROUP BY `gperiod` ORDER BY `date`";

        $that = new self;

        return $that->_spec->ds->loadHashList($query);
    }

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec              = parent::getSpec();
        $spec->loggable    = false;
        $spec->table       = 'user_log';
        $spec->key         = 'user_log_id';
        $spec->measureable = true;

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                 = parent::getProps();
        $props["object_id"]    = "ref notNull class|CStoredObject meta|object_class unlink back|user_logs";
        $props["object_class"] = "str notNull show|0"; // Ne pas mettre "class" !! (pour les CExObject)
        $props["user_id"]      = "ref notNull class|CUser back|owned_logs";
        $props["date"]         = "dateTime notNull fieldset|default";
        $props["type"]         = "enum notNull list|create|store|merge|delete fieldset|default";
        $props["fields"]       = "text show|0 fieldset|default";
        $props["ip_address"]   = "ipAddress";
        $props["extra"]        = "text show|0";

        $props["_date_min"] = "dateTime";
        $props["_date_max"] = "dateTime moreEquals|_date_min";

        return $props;
    }

    /**
     * @param null $id
     *
     * @return $this|bool|CMbObject
     * @throws Exception
     */
    function load($id = null)
    {
        if ($id >= self::USER_ACTION_START_AUTO_INCREMENT) {
            $action = new CUserAction();
            $action->load($id);
            $action->loadRefUserActionDatas();

            return $this->loadFromUserAction($action);
        }

        return parent::load($id);
    }

    /**
     * Cast CUserAction & CUserActionData to CUserLog
     *
     * @param CUserAction $user_action
     *
     * @return $this
     * @throws Exception
     */
    public function loadFromUserAction(CUserAction $user_action)
    {
        //$user_action->canUndo();

        $this->user_log_id  = $user_action->_id;
        $this->_id          = $user_action->_id;
        $this->user_id      = $user_action->user_id;
        $this->date         = $user_action->date;
        $this->type         = $user_action->type;
        $this->ip_address   = $user_action->ip_address;
        $this->object_id    = $user_action->object_id;
        $this->object_class = CObjectClass::getClass($user_action->object_class_id);
        $this->_fwd         = $user_action->_fwd;
        $this->_canUndo     = $user_action->canUndo();

        $filed  = [];
        $extras = [];

        if ($user_action_datas = $user_action->_ref_user_action_datas) {
            foreach ($user_action_datas as $_user_action_data) {
                /** @var $_user_action_data CUserActionData */
                $filed[] = $_user_action_data->field;
                $_value  = $_user_action_data->value;
                if ($_uncompress = @gzuncompress($_value ?? '')) {
                    $_value = $_uncompress;
                }
                // Encode utf8 is necessary for json_encode
                $extras[$_user_action_data->field] = is_string($_value) ? mb_convert_encoding($_value, 'UTF-8', 'ISO-8859-1') : null;
            }
        }

        $this->fields = implode(" ", $filed);
        $this->extra  = empty($extras) ? null : json_encode($extras);

        $this->updateFormFields();

        return $this;
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();
        if ($this->fields) {
            $this->_fields = explode(" ", $this->fields);
        }
    }

    /**
     * @inheritdoc
     */
    function loadLogs()
    {
        $this->_ref_logs = [];
    }

    /**
     * @inheritdoc
     */
    function updatePlainFields()
    {
        parent::updatePlainFields();
        if ($this->_fields) {
            $this->fields = implode(" ", $this->_fields);
        }
    }

    /**
     * @inheritdoc
     * @deprecated
     */
    function loadRefsFwd()
    {
        parent::loadRefsFwd();
        $this->loadTargetObject();
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
        if ($this->_id <= CUserLog::USER_ACTION_START_AUTO_INCREMENT) {
            $this->canUndo();
        }


        $this->loadTargetObject()->loadHistory();
    }

    /**
     *
     * Tells if we can undo the change
     *
     * @return bool
     */
    function canUndo()
    {
        $this->completeField("type", "extra");

        if (!$this->_id || ($this->type != "store") || ($this->extra == null) || !$this->canEdit(
            ) || !$this->_ref_module->canAdmin()) {
            return $this->_canUndo = false;
        }
        $this->completeField("object_id", "object_class");

        // from user_log
        $where = [
            "object_id"           => "= '$this->object_id'",
            "object_class"        => "= '$this->object_class'",
            "{$this->_spec->key}" => "> '$this->_id'",
        ];

        return $this->_canUndo = ($this->countList($where) == 0);
    }

    /**
     * Gets all the IDs implied in the merging
     *
     * @return string[]
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

        return parent::store();
    }

    /**
     * Undo the change
     *
     * @return null|string
     */
    function undo()
    {
        if (!$this->canUndo()) {
            return "CUserLog-undo-ko";
        }

        $object                  = $this->loadTargetObject();
        $object->_spec->loggable = false;

        $this->getOldValues();


        // Revalue fields
        foreach ($this->_old_values as $_field => $_value) {
            $object->$_field = $_value;
        }
        $object->updateFormFields();

        // Prevent disturbing checks
        $object->_merging = true;

        $msg                     = $object->store();
        $object->_spec->loggable = true;

        if ($msg) {
            return $msg;
        }

        return $this->delete();
    }

    /**
     * Replace diff opcodes in _old_values (using object history)
     * Construct diff html from previous to next value
     */
    public function undiff_old_Values()
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

        $_history         = ($this->_ref_object->_history) ?: [];
        $_history_key     = array_reverse(array_keys($_history));
        $_current_key     = array_search($this->_id, $_history_key);
        $_previous_log_id = $_current_key > 0 ? $_history_key[$_current_key - 1] : false;

        foreach ($this->_old_values as $_filed => $_value) {
            $_spec = $this->_ref_object->_specs[$_filed] ?? null;

            if (
                !($_spec instanceof CTextSpec || $_spec instanceof CHtmlSpec)
                && ($_value === '' || !isset($this->_ref_object->$_filed))
            ) {
                continue;
            }

            $_from = null;
            if (isset($_history[$_previous_log_id])) {
                $_from = $_history[$_previous_log_id][$_filed];
            }

            $_to = null;
            if (isset($_history[$this->_id])) {
                $_to = $_history[$this->_id][$_filed] ?? null;
            }

            $_spec = $this->_ref_object->_specs[$_filed] ?? null;

            // Si la propriété est inexistante on note sa dernière valeur
            if ($_spec === null) {
                $this->_diff_values[$_filed] = $_value;
                continue;
            }

            if ($_previous_log_id && ($_spec instanceof CTextSpec || $_spec instanceof CHtmlSpec)) {
                $this->_old_values[$_filed] = $_from;
                $render                     = $diff->render($_from, $_to);

                if ($_from === '') {
                    $render = $this->getDeleteIcone('warning') . $this->getDiffArrow() . $render;
                } elseif ($_to === '' || $_to === null) {
                    $render .= $this->getDiffArrow() . $this->getDeleteIcone('error');
                }

                $this->_diff_values[$_filed] = nl2br(html_entity_decode($render));
            } else {
                $_from_html = $this->getValue($_filed, $_from);
                $_to_html   = $this->getValue($_filed, $_to, true);
                if (!CMbString::isHtml($_from_html) && !CMbString::isHtml($_to)) {
                    $render = $diff->render($_from_html, $_to_html);

                    if ($_from === null) {
                        $render = $this->getDeleteIcone('warning') . $this->getDiffArrow()
                            . $diff->render($_from, $_to_html);
                    }

                    $this->_diff_values[$_filed] = nl2br(html_entity_decode($render));
                }
            }
        }
    }

    private function getDeleteIcone(string $type): string
    {
        return "<i class=\"me-icon cancel me-{$type}\"></i>";
    }

    private function getDiffArrow(): string
    {
        return '<span style="padding-left: 2px; padding-right: 2px;">&#8594;</span>';
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
     * @return null|string font awsone ico
     */
    public function getTypeIco()
    {
        $ico = null;
        switch ($this->type) {
            case 'create':
                $ico = "<i class=\"fas fa-fw fa-plus\"></i>";
                break;
            case 'store':
                $ico = "<i class=\"fas fa-fw fa-pencil-alt\"></i>";
                break;
            case 'merge':
                $ico = "<i class=\"fas fa-fw fa-check\"></i>";
                break;
            case 'delete':
                $ico = "<i class=\"fas fa-fw fa-trash\"></i>";
                break;
        }

        return $ico;
    }


    /**
     * @param int   $limit
     * @param array $where
     *
     * @return array
     * @throws Exception
     */
    public function migrationLogToAction($limit = 100, $where = null, $verbose = false)
    {
        $time_start        = microtime(true);
        $ds                = $this->getDS();
        $count_query_start = $ds->chrono->nbSteps;

        $lock = new CMbMutex("CUserLog-migrationLogToAction");

        if (CSessionHandler::getEngine() instanceof CRedisSessionHandler && !$lock->getDriver(
                ) instanceof CMbRedisMutex) {
            CApp::log('Invalid mutex driver configuration on redis session', null, LoggerLevels::LEVEL_ALERT);
            $lock->release();

            return;
        }

        if (!$lock->lock(60)) {
            return;
        }

        $log = new CUserLog();

        CView::enforceSlave();
        $list_log = $log->loadList($where, "user_log_id ASC", "0, $limit");
        CView::disableSlave();

        if (empty($list_log)) {
            // Disable migration, must use $allow_all_user because most of users are not admin
            CAppUI::setConf('activer_migration_log_to_action', '0', true);

            $lock->release();

            return;
        }

        // check integrity (fix query error out of transaction)
        $_log    = reset($list_log);
        $_action = new CUserAction();
        $_action->load($_log->_id);
        if ($_action->_id) {
            // delete partial migration
            $user_action_datas = $_action->loadRefUserActionDatas(false);
            /** @var CUserActionData $_user_action_data */
            foreach ($user_action_datas as $_user_action_data) {
                $_user_action_data->delete();
            }
            $_action->delete();
            $lock->release();

            return;
        }

        $retour = [];

        foreach ($list_log as $_log) {
            $retour[$_log->_id]['log']    = $_log;
            $retour[$_log->_id]['action'] = null;
            $retour[$_log->_id]['datas']  = [];

            // Store action
            $_action                  = new CUserAction();
            $_action->user_action_id  = $_log->user_log_id;
            $_action->user_id         = $_log->user_id;
            $_action->date            = $_log->date;
            $_action->type            = $_log->type;
            $_action->ip_address      = $_log->ip_address;
            $_action->object_id       = $_log->object_id;
            $_action->object_class_id = CObjectClass::getID($_log->object_class, true);
            $_action->_datas          = $_log->_old_values;

            // Warning _id is referenced necessary unset for rawStore
            unset($_action->_id);
            $_action->_id = null;

            if (!$_action->rawStore()) {
                $lock->release();
                CAppUI::stepAjax('Error in action raw store', UI_MSG_ERROR);
            }
            $retour[$_log->_id]['action'] = $_action;

            // Prepare data
            $_datas_to_store            = [];
            $retour[$_log->_id]['type'] = $_log->type;

            switch ($_log->type):
                case 'create':
                    // no data
                    break;
                case 'delete':
                    $_datas_to_store['_view'] = $_log->extra;
                    break;
                case 'store':
                case 'merge':
                    $_extra  = (array)json_decode($_log->extra);
                    $_extra  = array_map("utf8_decode", $_extra);
                    $_fields = explode(' ', $_log->fields);
                    foreach ($_fields as $_field) {
                        $_datas_to_store[$_field] = isset($_extra[$_field]) && $_extra[$_field] != '' ? $_extra[$_field] : null;
                    }
                    break;
            endswitch;

            $retour[$_log->_id]['datas_to_store'] = $_datas_to_store;

            // Store data
            foreach ($_datas_to_store as $_field => $_value) {
                // Prevent runtime error
                if (!$_field) {
                    continue;
                }

                $action_data                 = new CUserActionData();
                $action_data->user_action_id = $_action->user_action_id;
                $action_data->field          = $_field;
                $action_data->value          = $_value;
                $msg_data                    = $action_data->store();
                if ($msg_data) {
                    $lock->release();
                    CAppUI::stepAjax($msg_data, UI_MSG_ERROR);
                }
                $retour[$_log->_id]['datas'][] = $action_data;
            }

            // Delete log
            $_log->delete();
        }

        $lock->release();


        // Mediboard.log
        $count_user_log         = count($retour);
        $count_user_action      = 0;
        $count_user_action_data = 0;
        $count_query            = $ds->chrono->nbSteps - $count_query_start;
        $time                   = (microtime(true) - $time_start) * 1000;
        $time                   = round($time, 2);

        foreach ($retour as $_log) {
            if ($_log['action'] instanceof CUserAction) {
                $count_user_action++;
            }
            $count_user_action_data += count($_log['datas']);
        }


        $log = sprintf(
            '%s user_log migrés en %s user_action et %s user_action_data (%s requêtes / %sms).',
            $count_user_log,
            $count_user_action,
            $count_user_action_data,
            $count_query,
            $time
        );
        if ($verbose) {
            CAppUI::setMsg($log);
        }
        CApp::log($log);

        return $retour;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function statMigrationLogToAction(): string
    {
        $retour = null;
        $ds     = $this->_spec->ds;

        // progression
        $query = new CRequest();
        $query->addSelect('MIN(user_action.user_action_id)');
        $query->addTable('user_action ');
        $query->addWhere(['user_action.user_action_id' => '< 1000000000']);
        $min_ua_id = $ds->loadResult($query->makeSelect());

        $query = new CRequest();
        $query->addSelect('MAX(user_action.user_action_id)');
        $query->addTable('user_action ');
        $query->addWhere(['user_action.user_action_id' => '< 1000000000']);
        $max_ua_id = $ds->loadResult($query->makeSelect());

        $nb_log_migrated     = ($max_ua_id - $min_ua_id);
        $nb_log_not_migrated = $this->countList();

        $total = $nb_log_migrated + $nb_log_not_migrated;

        if ($total > 0) {
            $retour .= round(($nb_log_migrated / $total) * 100, 0) . '% effectué';
        }
        $retour .= " ({$nb_log_migrated}/$total).<br>";

        // duration
        if ($nb_log_not_migrated > 0) {
            $user_action      = new CUserAction();
            $where            = [
                'date' => ' >= date_sub(now(),INTERVAL 1 WEEK)',
            ];
            $nb_log_last_week = $user_action->countList($where);
            $nb_log_daily     = round($nb_log_last_week / 7, 0);
            // Estimation only when instance is alive
            if ($nb_log_daily > 0) {
                $nbr_by_step = CAppUI::conf("migration_log_to_action_nbr");
                $nb_step     = $nb_log_not_migrated / $nbr_by_step;
                $nb_step     = $nb_step < 1 ? 1 : round($nb_step, 0);

                $probably         = CAppUI::conf("migration_log_to_action_probably");
                $nb_log_necessary = $nb_step * $probably;
                $nb_day_estimated = round($nb_log_necessary / $nb_log_daily, 0);
                $retour           .= $nb_day_estimated . ' jours estimés (' . $nb_log_daily . ' actions/jour).';
            }
        }

        return $retour;
    }


    /**
     * @param CStoredObject $object
     *
     * @return void
     * @todo redefine meta raf
     * @deprecated
     */
    public function setObject(CStoredObject $object)
    {
        CMbMetaObjectPolyfill::setObject($this, $object);
    }

    /**
     * @param bool $cache
     *
     * @return bool|CStoredObject|CExObject|null
     * @throws Exception
     * @deprecated
     * @todo redefine meta raf
     */
    public function loadTargetObject($cache = true)
    {
        return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
    }


    /**
     * @throws ApiException
     */
    public function getResourceUser(): Item
    {
        $user = $this->loadRefUser();

        return new Item($user);
    }

    /**
     * @return string|null
     */
    public function getApiLink(RouterInterface $router): ?string
    {
        $object_class  = $this->object_class;
        $resource_type = $object_class::RESOURCE_TYPE;
        $parameters    = [
            'resource_type' => $resource_type,
            'resource_id'   => $this->object_id,
            'history_id'    => $this->_id,
        ];

        return $router->generate('system_history_show', $parameters);
    }

    /**
     * @return Link|null
     */
    public function getApiHistoryLink(RouterInterface $router): ?string
    {
        return null;
    }
}
