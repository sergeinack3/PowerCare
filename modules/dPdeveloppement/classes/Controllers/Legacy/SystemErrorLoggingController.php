<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement\Controllers\Legacy;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\Elastic\ElasticObjectManager;
use Ox\Core\Elastic\Exceptions\ElasticClientException;
use Ox\Core\Logger\LoggerLevels;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CErrorLog;
use Ox\Mediboard\System\CErrorLogData;
use Ox\Mediboard\System\CErrorLogWhiteList;
use Ox\Mediboard\System\Elastic\ErrorLog;
use Ox\Mediboard\System\Elastic\ErrorLogRepository;

class SystemErrorLoggingController extends CLegacyController
{
    private function getParamsForListingErrorLogs(): array
    {
        $data                  = [];
        $data["start"]         = CView::get("start", "num default|0");
        $data["error_type"]    = CView::get("error_type", "str");
        $data["text"]          = CView::get("text", "str");
        $data["server_ip"]     = CView::get("server_ip", "str");
        $data["datetime_min"]  = CView::get("_datetime_min", "str");
        $data["datetime_max"]  = CView::get("_datetime_max", "str");
        $data["order_by"]      = CView::get("order_by", "str");
        $data["group_similar"] = CView::get("group_similar", "str default|similar");
        $data["user_id"]       = CView::get("user_id", "str");
        $data["human"]         = CView::get("human", "bool default|0");
        $data["robot"]         = CView::get("robot", "bool default|0");
        $data["request_uid"]   = CView::get("request_uid", "str");

        return $data;
    }

    private function getRobotsUsers(bool $human, bool $robot, CSQLDataSource $ds): array
    {
        $robots = [];
        if (($human || $robot) && !($human && $robot)) {
            $tag = CMediusers::getTagSoftware();

            if ($tag) {
                $query = "
                    SELECT users.user_id
                    FROM users
                    LEFT JOIN id_sante400 ON users.user_id = id_sante400.object_id
                    WHERE (id_sante400.object_class = 'CMediusers'
                    AND id_sante400.tag = ?)
                    OR users.is_robot = '1'
                    GROUP BY users.user_id
                ";

                $query = $ds->prepare($query, $tag);
            } else {
                $query = "
                    SELECT users.user_id
                    FROM users
                    WHERE users.is_robot = '1'
                ";
            }

            $robots = $ds->loadColumn($query);
        }

        return $robots;
    }

    private function prepareWhere(array $data, array $robots, CSQLDataSource $ds): array
    {
        $text  = $data["text"];
        $where = [];

        if ($data["human"] && !$data["robot"]) {
            if (count($robots)) {
                $where["user_id"] = $ds->prepareNotIn($robots);
            }
        }

        if ($data["robot"] && !$data["human"]) {
            if (count($robots)) {
                $where["user_id"] = $ds->prepareIn($robots);
            }
        }

        if (!empty($data["error_type"])) {
            $error_type          = array_keys($data["error_type"]);
            $where["error_type"] = $ds->prepareIn($error_type);
        }

        if ($data["user_id"]) {
            $where["user_id"] = $ds->prepareLike($data["user_id"]);
        }

        if ($data["server_ip"]) {
            $where["server_ip"] = $ds->prepareLike($data["server_ip"]);
        }

        if ($text) {
            $where["text"] = $ds->prepareLike("%$text%");
        }

        if ($data["datetime_min"]) {
            $where[] = $ds->prepare("datetime >= %", $data["datetime_min"]);
        }

        if ($data["datetime_max"]) {
            $where[] = $ds->prepare("datetime <= %", $data["datetime_max"]);
        }

        if ($data["request_uid"]) {
            $where[] = $ds->prepare("request_uid = %", $data["request_uid"]);
        }

        return $where;
    }

    private function prepareOrder(string $order_by, string $group_similar, ?string $key): array
    {
        $order = [];
        if ($order_by == "quantity" && ($group_similar && $group_similar !== 'no')) {
            $order[] = "similar_count DESC";
        }
        $order[] = "datetime DESC";
        $order[] = "$key DESC";

        return $order;
    }

    public function listErrorLogs(): void
    {
        $this->checkPermRead();

        $data = $this->getParamsForListingErrorLogs();

        CView::checkin();

        CView::enforceSlave();

        $error_log = new CErrorLog();
        $spec      = $error_log->_spec;
        $ds        = $error_log->getDS();

        $robots = $this->getRobotsUsers($data["human"], $data["robot"], $ds);
        $where  = $this->prepareWhere($data, $robots, $ds);
        $order  = $this->prepareOrder($data["order_by"], $data["group_similar"], $spec->key);
        $start  = $data["start"];
        $limit  = "$start, 30";

        $resource   = $this->createErrorLogCollection(
            $where,
            $order,
            $limit,
            $data["group_similar"],
        );
        $error_logs = $resource["errors"];

        // Get all data
        CStoredObject::massLoadFwdRef($error_logs, "stacktrace_id");
        CStoredObject::massLoadFwdRef($error_logs, "param_GET_id");
        CStoredObject::massLoadFwdRef($error_logs, "param_POST_id");
        CStoredObject::massLoadFwdRef($error_logs, "session_data_id");
        foreach ($error_logs as $_error_log) {
            $_error_log->loadComplete();
        }

        // Error (whitelist)
        $error_log_whitelist       = new CErrorLogWhiteList();
        $count_error_log_whitelist = $error_log_whitelist->countList();
        $whitelist_hash            = $error_log_whitelist->loadColumn('hash');

        // Création du template
        $this->renderSmarty(
            "inc_list_error_logs",
            [
                "error_logs"                => $error_logs,
                "list_ids"                  => $resource["list_ids"],
                "total"                     => $resource["total"],
                "start"                     => $start,
                "users"                     => $resource["user_ids"],
                "group_similar"             => $data["group_similar"],
                "applicationVersion"        => CApp::getVersion()->toArray(),
                "whitelist_hash"            => $whitelist_hash,
                "count_error_log_whitelist" => $count_error_log_whitelist,
                "is_elastic_log"            => false,
            ]
        );
    }

    /**
     * @param array|null  $where
     * @param array|null  $order
     * @param string|null $limit
     * @param string      $group_type
     *
     * @return array
     * @throws Exception
     */
    public function createErrorLogCollection(
        ?array $where,
        ?array $order,
        ?string $limit,
        string $group_type
    ): array {
        // Gather CErrorLog datasource
        $error_log = new CErrorLog();
        $ds        = $error_log->getDS();

        $user_ids = [];
        $list_ids = [];
        // If there is a grouping type -> Then add fields to the error
        // [similar_ids, similar_count, similar_users_ids, similar_server_ips]
        if ($group_type && $group_type !== 'no') {
            $request = $this->buildRequestWhenGrouping($group_type, $where ?? [], $order ?? [], $limit ?? "");

            // Gather similar error logs
            $error_logs_similar = $ds->loadList(
                $request->makeSelectCount($error_log, $this->getFieldWhenGrouped())
            );

            // Count the total of similar error logs
            $request->setLimit(null);
            $req = new CRequest();
            $req->addTable(
                "(" . $request->makeSelectCount($error_log, $this->getFieldWhenGrouped()) . ") AS error_counting"
            );
            $total = $ds->loadResult($req->makeSelectCount());

            // For each error log gather data about it
            $error_logs = [];
            foreach ($error_logs_similar as $_info) {
                $similar_ids = explode(",", $_info["similar_ids"]);

                $error_log = new CErrorLog();
                $error_log->load(reset($similar_ids));
                $error_log->_similar_ids        = $similar_ids;
                $error_log->_similar_count      = $_info["similar_count"];
                $error_log->_datetime_min       = $_info["datetime_min"];
                $error_log->_datetime_max       = $_info["datetime_max"];
                $error_log->_similar_user_ids   = array_unique(explode(",", $_info["similar_user_ids"]));
                $error_log->_similar_server_ips = array_unique(explode(",", $_info["similar_server_ips"]));
                $error_logs[]                   = $error_log;

                $user_ids = array_merge($user_ids, $error_log->_similar_user_ids);
                $list_ids = array_merge($list_ids, $error_log->_similar_ids);
            }
        } else {
            // Gather error logs
            $total      = $error_log->countList($where);
            $error_logs = $error_log->loadList($where, $order, $limit);

            $list_ids = CMbArray::pluck($error_logs, "_id");
            $user_ids = CStoredObject::massLoadFwdRef($error_logs, "user_id");
        }

        return [
            "errors"   => $error_logs,
            "total"    => $total,
            "user_ids" => $user_ids,
            "list_ids" => $list_ids,
        ];
    }

    private function buildRequestWhenGrouping(
        string $group_similar,
        array $where,
        array $order,
        string $limit
    ): CRequest {
        $group_by = "";
        if ($group_similar === 'signature') {
            $group_by = "signature_hash";
        }
        if ($group_similar === 'similar') {
            $group_by = "text, stacktrace_id, param_GET_id, param_POST_id";
        }

        $request = new CRequest();
        $request->addWhere($where);
        $request->addOrder($order);
        if ($group_by !== "") {
            $request->addGroup($group_by);
        }
        $request->setLimit($limit);

        return $request;
    }

    private function getFieldWhenGrouped(): array
    {
        return [
            "GROUP_CONCAT(error_log_id) AS similar_ids",
            "GROUP_CONCAT(user_id)      AS similar_user_ids",
            "GROUP_CONCAT(server_ip)    AS similar_server_ips",
            "SUM(COUNT)                 AS similar_count",
            "MIN(datetime) AS datetime_min",
            "MAX(datetime) AS datetime_max",
        ];
    }

    public function listErrorLogsElastic(): void
    {
        $this->checkPermRead();

        $data = $this->getParamsForListingErrorLogs();

        CView::checkin();

        CView::enforceSlave();

        $error_log  = new CErrorLog();
        $ds         = $error_log->getDS();
        $repository = new ErrorLogRepository();

        $robots     = $this->getRobotsUsers($data["human"], $data["robot"], $ds);
        $start      = $data["start"];
        $group_type = $data["group_similar"];

        $query      = $repository->buildQueryFromFormData($data, $robots, $start, 30);
        $error_logs = [];
        $list_ids   = [];
        $user_ids   = [];
        $errors     = [];
        $total      = 0;

        if ($group_type && $group_type !== 'no') {
            $_query = $repository->addAggregation($data["group_similar"], $query);
            try {
                $result = $repository->execQueryToResult($_query);
            } catch (ElasticClientException $e) {
                CApp::log(
                    CAppUI::tr("ElasticIndexManager-error-Connection failed"),
                    ["message" => $e->getMessage()],
                    LoggerLevels::LEVEL_ERROR
                );
                CAppUI::stepAjax("ElasticIndexManager-error-Connection failed", UI_MSG_ERROR);
            }
            if (array_key_exists("aggregations", $result)) {
                $total = $result["aggregations"]["total"]["value"];
                foreach ($result["aggregations"]["signature_hash"]["buckets"] as $_log_agg) {
                    /** @var ErrorLog $error */
                    $error = $repository->loadDataFromElastic(reset($_log_agg["log"]["hits"]["hits"]));
                    $error->setCount($_log_agg["total_count"]["value"]);
                    $error->setDateMin($_log_agg["date_min"]["value_as_string"], $error->getDate()->getTimezone());
                    $error->setDateMax($_log_agg["date_max"]["value_as_string"], $error->getDate()->getTimezone());
                    foreach ($_log_agg["similar_ids"]["buckets"] as $_similar_id) {
                        $error->addSimilarId($_similar_id["key"]);
                    }
                    foreach ($_log_agg["similar_user_ids"]["buckets"] as $_similar_user_id) {
                        $error->addSimilarUserId($_similar_user_id["key"]);
                    }
                    foreach ($_log_agg["similar_server_ips"]["buckets"] as $_similar_server_ip) {
                        $error->addSimilarServerIp($_similar_server_ip["key"]);
                    }
                    $errors[] = $error;
                    $list_ids = array_merge($list_ids, $error->getSimilarIds());
                    $user_ids = array_merge($user_ids, $error->getSimilarUserIds());
                }
                foreach ($errors as $_error) {
                    $error_logs[] = $_error->toCErrorLog();
                }
            }
        } else {
            try {
                $errors = $repository->execQuery($query);
            } catch (ElasticClientException $e) {
                CApp::log(
                    CAppUI::tr("ElasticIndexManager-error-Connection failed"),
                    ["message" => $e->getMessage()],
                    LoggerLevels::LEVEL_ERROR
                );
                CAppUI::stepAjax("ElasticIndexManager-error-Connection failed", UI_MSG_ERROR);
            }
            $total = $repository->countFromQuery($query);


            /** @var ErrorLog $_error */
            foreach ($errors as $_error) {
                $error_logs[] = $_error->toCErrorLog();
            }
            $list_ids = CMbArray::pluck($error_logs, "_id");
            $user_ids = CStoredObject::massLoadFwdRef($error_logs, "user_id");
        }

        // Error (whitelist)
        $error_log_whitelist       = new CErrorLogWhiteList();
        $count_error_log_whitelist = $error_log_whitelist->countList();
        $whitelist_hash            = $error_log_whitelist->loadColumn('hash');

        $this->renderSmarty(
            "inc_list_error_logs",
            [
                "error_logs"                => $error_logs,
                "list_ids"                  => $list_ids,
                "total"                     => $total,
                "start"                     => $start,
                "users"                     => $user_ids,
                "group_similar"             => $data["group_similar"],
                "applicationVersion"        => CApp::getVersion()->toArray(),
                "whitelist_hash"            => $whitelist_hash,
                "count_error_log_whitelist" => $count_error_log_whitelist,
                "is_elastic_log"            => true,
            ]
        );
    }

    public function do_error_log_multi_delete(): void
    {
        $this->checkPermAdmin();

        $ids            = CView::post("log_ids", "str");
        $is_elastic_log = CView::post("is_elastic_log", "bool");

        CView::checkin();

        if ($ids) {
            $ids = explode(",", $ids);

            if ($is_elastic_log) {
                $result = ElasticObjectManager::getInstance()->deleteListIds($ids, new ErrorLog());
                $docs   = $result["deleted"];
                CAppUI::stepAjax("ErrorLog-msg-%s documents deleted from Elasticsearch", UI_MSG_OK, $docs);
            } else {
                $error_log = new CErrorLog();
                $rows      = $error_log->deleteMulti($ids);
                CAppUI::stepAjax("CError-msg-%s rows deleted", UI_MSG_OK, $rows);
            }
        }
    }

    public function purgeErrorLog(): void
    {
        $this->checkPermAdmin();

        $is_elastic_log = CView::post("is_elastic_log", "bool");

        CView::checkin();

        if ($is_elastic_log) {
            $obj = new ErrorLog();
            ElasticObjectManager::getInstance()->deleteIndex($obj);
            ElasticObjectManager::createFirstIndex($obj);
            CAppUI::stepAjax("ErrorLog-msg-emptied errors logs");
        } else {
            $error_log = new CErrorLog();
            $ds        = $error_log->getDS();
            $query     = "TRUNCATE {$error_log->_spec->table}";
            $ds->exec($query);

            $error_log_data = new CErrorLogData();
            $ds             = $error_log->getDS();
            $query          = "TRUNCATE {$error_log_data->_spec->table}";
            $ds->exec($query);

            CAppUI::stepAjax("CErrorLog-msg-emptied errors logs");
        }
    }
}
