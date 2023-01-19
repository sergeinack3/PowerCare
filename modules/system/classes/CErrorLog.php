<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CError;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\Logger\ErrorTypes;

/**
 * Error log
 */
class CErrorLog extends CStoredObject
{

    /** @var string */
    public const RESOURCE_NAME = 'errorLog';

    /** @var string */
    public const FIELDSET_PARAMS = "params";

    /** @var string */
    public const FIELDSET_SIMILAR_DATA = "similar_data";

    /** @var string */
    public const RELATION_STACKTRACE = 'stacktrace';

    /** @var string */
    public const RELATION_PARAM_GET = 'paramGet';

    /** @var string */
    public const RELATION_PARAM_POST = 'paramPost';

    /** @var string */
    public const RELATION_SESSION_DATA = 'sessionData';

    public $error_log_id;

    public $user_id;
    public $server_ip;
    public $datetime;
    public $request_uid;
    public $error_type;
    public $text;
    public $file_name;
    public $line_number;

    public $stacktrace_id;
    public $param_GET_id;
    public $param_POST_id;
    public $session_data_id;

    public $signature_hash;
    public $count;

    public $debug;

    public $_stacktrace;
    public $_stacktrace_output;
    public $_param_GET;
    public $_param_POST;
    public $_session_data;

    public $_category;
    public $_url;
    public $_datetime_min;
    public $_datetime_max;

    public $_similar_count;
    public $_similar_ids        = [];
    public $_similar_user_ids   = [];
    public $_similar_server_ips = [];

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->table    = "error_log";
        $spec->key      = "error_log_id";
        $spec->loggable = false;

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                = parent::getProps();
        $props["user_id"]     = "ref class|CUser unlink back|error_logs fieldset|default";
        $props["server_ip"]   = "str fieldset|default";
        $props["datetime"]    = "dateTime notNull fieldset|default";
        $props["request_uid"] = "str fieldset|default";
        $props["error_type"]  = "enum list|" . implode("|", ErrorTypes::TYPES). " fieldset|default";
        $props["text"]        = "text fieldset|default";
        $props["file_name"]   = "str fieldset|default";
        $props["line_number"] = "num fieldset|default";

        $props["stacktrace_id"]   = "ref class|CErrorLogData back|stacktraces fieldset|params";
        $props["param_GET_id"]    = "ref class|CErrorLogData back|params_GET fieldset|params";
        $props["param_POST_id"]   = "ref class|CErrorLogData back|params_POST fieldset|params";
        $props["session_data_id"] = "ref class|CErrorLogData back|session_datas fieldset|params";

        $props["signature_hash"] = "str fieldset|default";
        $props["count"]          = "num notNull default|1 fieldset|default";

        $props["debug"] = "bool default|0";

        $props["_datetime_min"] = "dateTime";
        $props["_datetime_max"] = "dateTime";

        $props["_similar_count"] = "num fieldset|similar_data";
        $props["_similar_ids"] = "str fieldset|similar_data";
        $props["_similar_user_ids"] = "str fieldset|similar_data";
        $props["_similar_server_ips"] = "str fieldset|similar_data";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $this->completeField("error_type");

        $_types = array_flip(ErrorTypes::TYPES);

        if (isset($_types[$this->error_type])) {
            $_num_type       = $_types[$this->error_type];
            $this->_category = ErrorTypes::CATEGORIES[$_num_type];
        }
    }

    /**
     * Completely load the object for display
     *
     * @return void
     * @see CMbObject::loadComplete
     */
    function loadComplete()
    {
        $this->completeField("stacktrace_id", "param_GET_id", "param_POST_id", "session_data_id");

        if ($this->stacktrace_id) {
            $this->_stacktrace        = $this->getDataValue("stacktrace_id");
            $this->_stacktrace_output = [];

            if (!is_array($this->_stacktrace)) {
                $this->_stacktrace = [];
            }

            foreach ($this->_stacktrace as $_trace) {
                $function = isset($_trace["class"]) ? $_trace["class"] . ":" : "";
                $function .= $_trace["function"] . "()";

                $_output             = [
                    "function" => null,
                    "file"     => null,
                    "line"     => null,
                ];
                $_output["function"] = $function;

                if (isset($_trace["file"])) {
                    $_output["file"] = $_trace["file"];
                }

                if (isset($_trace["line"])) {
                    $_output["line"] = $_trace["line"];
                }

                $this->_stacktrace_output[] = $_output;
            }
        }

        if ($this->param_GET_id) {
            $this->_param_GET = $this->getDataValue("param_GET_id");
            if (is_array($this->_param_GET) || is_object($this->_param_GET)) {
                $this->_url = "?" . http_build_query($this->_param_GET, true, "&");
            }
        }

        if ($this->param_POST_id) {
            $this->_param_POST = $this->getDataValue("param_POST_id");
        }

        if ($this->session_data_id) {
            $this->_session_data = $this->getDataValue("session_data_id");
        }
    }

    /**
     * Get data decoded value
     *
     * @param string $field Field
     *
     * @return array
     */
    function getDataValue($field)
    {
        return json_decode($this->getDataObject($field)->value, true);
    }

    /**
     * Get data object
     *
     * @param string $field Field name
     *
     * @return CErrorLogData
     */
    function getDataObject($field)
    {
        return $this->loadFwdRef($field, true);
    }

    /**
     * Inserts an error log into database
     *
     * @param int    $user_id     User ID
     * @param string $server_ip   Server IP
     * @param string $datetime    Datetime
     * @param string $request_uid Request unique ID
     * @param string $error_type  Error type
     * @param string $text        Error message
     * @param string $file_name   File name
     * @param int    $line_number Line number
     * @param        $signature_hash
     * @param array  $data        Data (stacktrace, GET, POST and session)
     *
     *
     * @throws Exception
     */
    static function insert(
        $user_id,
        $server_ip,
        $datetime,
        $request_uid,
        $error_type,
        $text,
        $file_name,
        $line_number,
        $signature_hash,
        $count,
        $data
    ) {
        global $m, $action, $dosql;

        static $table_loaded = null;

        if (empty($action) && isset($dosql)) {
            $action = $dosql;
        }

        // Don't use CSQLDataSource::get() to prevent error log enslaving
        $ds = @CSQLDataSource::$dataSources["std"];

        if (!$ds) {
            throw new Exception("No datasource available");
        }

        if ($table_loaded === null) {
            $table_loaded = $ds->loadTable("error_log");
        }

        if (!$table_loaded) {
            throw new Exception("Error log table does not exist");
        }

        if (is_array($data)) {
            foreach ($data as $_field => $_value) {
                if (empty($_value)) {
                    $data[$_field] = null;
                    continue;
                }

                $data[$_field] = CErrorLogData::insert(json_encode($_value));
            }
        }

        $query = "INSERT INTO" . " `error_log` (
      `user_id`, `server_ip`,
      `datetime`, `request_uid`, `error_type`, `text`,
      `file_name`, `line_number`,
      `signature_hash`, `count`,
      `stacktrace_id`, `param_GET_id`, `param_POST_id`, `session_data_id`
    ) VALUES (?1, ?2, ?3, ?4, ?5, ?6, ?7, ?8, ?9, ?10, %s, %s, %s, %s)";

        $query = sprintf(
            $query,
            $data["stacktrace"] ?: "NULL",
            $data["param_GET"] ?: "NULL",
            $data["param_POST"] ?: "NULL",
            $data["session_data"] ?: "NULL"
        );

        $query = $ds->prepare(
            $query,
            $user_id,
            $server_ip,
            $datetime,
            $request_uid,
            $error_type,
            $text,
            $file_name,
            $line_number,
            $signature_hash,
            $count,
            $data["stacktrace"],
            $data["param_GET"],
            $data["param_POST"],
            $data["session_data"]
        );

        $result                = @$ds->exec($query);

        if (!$result) {
            throw new Exception("Exec failed");
        }
    }

    /**
     * Cleanup orphan log data entries
     *
     * @return void
     */
    function cleanupLogData()
    {
        $ds = $this->getDS();

        // A little cleanup ....
        $ds->exec(
            "DELETE `error_log_data`
       FROM `error_log_data`
       LEFT JOIN error_log ON (
         error_log_data.error_log_data_id = error_log.stacktrace_id OR
         error_log_data.error_log_data_id = error_log.param_GET_id OR
         error_log_data.error_log_data_id = error_log.param_POST_id OR
         error_log_data.error_log_data_id = error_log.session_data_id
       )
       WHERE error_log_id IS NULL"
        );
    }

    /**
     * Delete multiple error logs
     *
     * @param int[] $ids List of error log IDs
     *
     * @return int Number of deleted rows
     */
    function deleteMulti($ids)
    {
        $ids = array_map("intval", $ids);

        $spec = $this->_spec;
        $ds   = $this->getDS();

        $query  = "DELETE FROM $spec->table WHERE $spec->key ";
        $result = $ds->exec($query . $ds->prepareIn($ids));

        if (!$result) {
            return 0;
        }

        return $ds->affectedRows();
    }

    /**
     * @return Item|null
     * @throws Exception
     */
    public function getResourceStacktrace(): ?Item
    {
        $stacktrace = $this->loadFwdRef('stacktrace_id');
        if (!$stacktrace) {
            return null;
        }

        $res = new Item($stacktrace);
        $res->setName("stacktrace");

        return $res;
    }

    /**
     * @return Item|null
     * @throws Exception
     */
    public function getResourceParamGet(): ?Item
    {
        $param_get = $this->loadFwdRef('param_GET_id');
        if (!$param_get) {
            return null;
        }

        $res = new Item($param_get);
        $res->setName("param_get");

        return $res;
    }

    /**
     * @return Item|null
     * @throws Exception
     */
    public function getResourceParamPost(): ?Item
    {
        $param_post = $this->loadFwdRef('param_POST_id');
        if (!$param_post) {
            return null;
        }

        $res = new Item($param_post);
        $res->setName("param_post");

        return $res;
    }

    /**
     * @return Item|null
     * @throws Exception
     */
    public function getResourceSessionData(): ?Item
    {
        $session_data = $this->loadFwdRef('session_data_id');
        if (!$session_data) {
            return null;
        }

        $res = new Item($session_data);
        $res->setName("session_data");

        return $res;
    }
}
