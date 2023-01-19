<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\CMbArray;
use Ox\Core\CMbSecurity;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Global request (PHP + SQL) slow queries
 */
class CLongRequestLog extends CStoredObject
{
    /** @var int Primary Key */
    public $long_request_log_id;

    // DB fields
    public $user_id;
    public $datetime_start;
    public $datetime_end;
    public $duration;
    public $server_addr;
    public $module_action_id;
    public $session_id;

    // JSON DB fields
    public $query_params_get;
    public $query_params_post;
    public $session_data;
    public $query_performance;
    public $query_report;

    // Form fields
    public $_module;
    public $_action;
    public $_link;

    // Filter fields
    public $_datetime_start_min;
    public $_datetime_start_max;
    public $_datetime_end_min;
    public $_datetime_end_max;

    // Arrays
    public $_query_params_get;
    public $_query_params_post;
    public $_session_data;
    public $_query_performance;
    public $_query_report;

    /** @var CMediusers */
    public $_ref_user;

    /** @var CModuleAction */
    public $_ref_module_action;

    /** @var CUserAuthentication */
    public $_ref_session;

    // Unique Request ID
    public $requestUID;

    /** @var string */
    public $_user_type;

    public $_performance_ratio = 0;
    public $_transport_ratio   = 0;

    public bool $_enslaved = false;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->table    = "long_request_log";
        $spec->key      = "long_request_log_id";
        $spec->loggable = false;

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function updatePlainFields()
    {
        parent::updatePlainFields();

        // GET
        if ($this->_query_params_get) {
            $this->_query_params_get = CMbSecurity::filterInput($this->_query_params_get);
            $this->query_params_get  = CMbArray::toJSON($this->_query_params_get);
        }

        // POST
        if ($this->_query_params_post) {
            $this->_query_params_post = CMbSecurity::filterInput($this->_query_params_post);
            $this->query_params_post  = CMbArray::toJSON($this->_query_params_post);
        }

        // SESSION
        if ($this->_session_data) {
            $this->_session_data = CMbSecurity::filterInput($this->_session_data);
            $this->session_data  = CMbArray::toJSON($this->_session_data);
        }

        // Performance
        if ($this->_query_performance) {
            $this->query_performance = CMbArray::toJSON($this->_query_performance);
        }

        // SQL report
        if ($this->_query_report) {
            $this->query_report = CMbArray::toJSON($this->_query_report);
        }
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        $this->_query_params_get  = json_decode($this->query_params_get ?? '', true);
        $this->_query_params_post = json_decode($this->query_params_post ?? '', true);
        $this->_session_data      = json_decode($this->session_data ?? '', true);
        $this->_query_performance = json_decode($this->query_performance ?? '', true);
        $this->_query_report      = json_decode($this->query_report ?? '', true);
    }

    /**
     * Get module and action fields
     *
     * @return void
     */
    function getModuleAction()
    {
        if ($this->module_action_id) {
            $module_action = $this->loadRefModuleAction();

            $this->_module = $module_action->module;
            $this->_action = $module_action->action;

            return;
        }

        // @todo: Following is legacy code to be removed by 2016-01-01
        $get  = is_array($this->_query_params_get) ? $this->_query_params_get : [];
        $post = is_array($this->_query_params_post) ? $this->_query_params_post : [];

        $this->_module = CValue::first(
            CMbArray::extract($get, "m"),
            CMbArray::extract($post, "m")
        );

        $this->_action = CValue::first(
            CMbArray::extract($get, "tab"),
            CMbArray::extract($get, "a"),
            CMbArray::extract($post, "dosql")
        );
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                      = parent::getProps();
        $props["user_id"]           = "ref class|CMediusers unlink back|long_request_log";
        $props["datetime_start"]    = "dateTime notNull";
        $props["datetime_end"]      = "dateTime notNull";
        $props["duration"]          = "float notNull";
        $props["server_addr"]       = "str notNull";
        $props["module_action_id"]  = "ref class|CModuleAction back|long_request_logs";
        $props["session_id"]        = "str";
        $props["query_params_get"]  = "text show|0";
        $props["query_params_post"] = "text show|0";
        $props["session_data"]      = "text show|0";
        $props["query_performance"] = "text show|0";
        $props["query_report"]      = "text show|0";
        $props["requestUID"]        = "str";

        // Form fields
        $props["_module"]            = "str";
        $props["_action"]            = "str";
        $props["_link"]              = "str";
        $props["_query_params_get"]  = "php";
        $props["_query_params_post"] = "php";
        $props["_session_data"]      = "php";
        $props["_query_performance"] = "php";
        $props["_query_report"]      = "php";

        // Filter fields
        $props["_datetime_start_min"] = "dateTime";
        $props["_datetime_start_max"] = "dateTime";
        $props["_datetime_end_min"]   = "dateTime";
        $props["_datetime_end_max"]   = "dateTime";
        $props['_user_type']          = 'enum list|all|human|bot|public';

        $props['_performance_ratio'] = 'pct';
        $props['_transport_ratio']   = 'pct';

        $props['_enslaved'] = 'bool default|0';

        return $props;
    }

    /**
     * Generate the long request weblink
     *
     * @return void
     */
    function getLink()
    {
        if ($this->_query_params_get) {
            $this->_link = "?" . http_build_query($this->_query_params_get, null, "&");
        }
    }

    /**
     * Load the referenced user
     *
     * @return CMediusers
     */
    function loadRefUser()
    {
        return $this->_ref_user = $this->loadFwdRef("user_id");
    }

    /**
     * Load the referenced module/action
     *
     * @return CModuleAction
     */
    function loadRefModuleAction()
    {
        return $this->_ref_module_action = $this->loadFwdRef("module_action_id");
    }

    /**
     * Load the user authentication
     *
     * @return CUserAuthentication|null
     */
    function loadRefSession()
    {
        if (!$this->session_id) {
            return null;
        }

        $where = [
            "session_id" => "= '$this->session_id'",
        ];

        $user_auth = new CUserAuthentication();
        $user_auth->loadObject($where, "datetime_login DESC");

        return $this->_ref_session = $user_auth;
    }

    /**
     * Compute datasource and transport performance ratios
     *
     * @return void
     */
    public function computePerformanceRatio(): void
    {
        if (!$this->_query_performance) {
            return;
        }

        $performance = $this->_query_performance;

        $ds_time = 0;
        foreach ($performance['dataSources'] as $_dsn => $_datasource) {
            $ds_time += $_datasource['time'];
        }

        $ds_time += $performance['nosqlTime'];

        $genere = ($performance['genere']) ?: 1;

        $this->_performance_ratio = round(($ds_time / $genere) * 100, 2);

        $this->_transport_ratio = 0;
        if (array_key_exists('transportTiers', $performance) && $performance['transportTiers']) {
            $transport_time         = $performance['transportTiers']['total']['time'];
            $this->_transport_ratio = round(($transport_time / $genere) * 100, 2);
        }

        $this->_enslaved = $performance['enslaved'];
    }

    public function isPublic(): bool
    {
        return ($this->user_id === null);
    }
}
