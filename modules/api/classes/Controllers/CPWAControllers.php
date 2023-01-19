<?php

/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Api\Controllers;

use Exception;
use Ox\Api\CMobileLog;
use Ox\Api\CSyncLog;
use Ox\AppFine\Server\CAppFineSyncHistory;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CAppUI;
use Ox\Core\CController;
use Ox\Core\CMbArray;
use Ox\Core\CSQLDataSource;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CUser;

class CPWAControllers extends CController
{
    use CPWAControllerTrait;

    /**
     * Add logs
     *
     * @param RequestApi $request_api request
     *
     * @return Item
     * @throws Exception
     */
    public function addLog(RequestApi $request_api): Item
    {
        $data = CMbArray::get($request_api->getContent(true, 'ISO-8859-1'), "data");
        $logs = CMbArray::get($data, "logs");
        if (!$logs) {
            throw $this->missingParameterError("logs");
        }

        foreach ($logs as $_log) {
            $params = [
                "url"                      => trim(CMbArray::get($_log, 'url')),
                "input"                    => isset($_log['input']) ? serialize(CMbArray::get($_log, 'input')) : null,
                "output"                   => isset($_log['output']) ? serialize($_log['output']) : null,
                "device_uuid"              => trim(CMbArray::get($_log, "device_uuid")),
                "device_platform"          => trim(CMbArray::get($_log, "device_platform")),
                "device_platform_version"  => trim(CMbArray::get($_log, "device_platform_version")),
                "device_model"             => trim(CMbArray::get($_log, "device_model")),
                "level"                    => trim(CMbArray::get($_log, "level")),
                "description"              => trim(CMbArray::get($_log, "description")),
                "log_datetime"             => trim(CMbArray::get($_log, "log_datetime")),
                "origin"                   => trim(CMbArray::get($_log, "origin")),
                "object"                   => isset($_log['object']) ? serialize($_log['object']) : null,
                "code"                     => trim(CMbArray::get($_log, "code")),
                "internet_connection_type" => trim(CMbArray::get($_log, "internet_connection_type")),
                "execution_time"           => trim(CMbArray::get($_log, "execution_time")),
                "application_name"         => trim(CMbArray::get($_log, "application_name")),
            ];

            $mobile_log = new CMobileLog();
            $mobile_log->bind($params);

            try {
                $mobile_log->store();
            } catch (Exception $e) {
                $this->invalidStoredObject("Fail to store a mobile log");
            }
        }

        return new Item(['response' => 'Log stored']);
    }

    /**
     * Get list of logs
     *
     * @param RequestApi                  $request_api
     * @param CAppFineSyncHistory|CSyncLog $logger
     *
     * @return Collection
     * @throws Exception
     * @api
     */
    public function getLogs(RequestApi $request_api, $logger): Collection
    {
        $start_id   = $request_api->getRequest()->query->get('start_id', null);
        $start_date = $request_api->getRequest()->query->get('start_date', null);

        $user        = CUser::get();
        $ds          = CSQLDataSource::get('std');

        $table      = $logger->getSpec()->table;
        $primary_id = $logger->getPrimaryKey();

        $where = [];
        if ($start_id) {
            $where["$table.$primary_id"] = $ds->prepare('> ?', $start_id);
        }

        if ($start_date && preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $start_date)) {
            $where["$table.datetime"] = $ds->prepare('>= ?', "{$start_date}");
        } elseif ($start_date && preg_match('/\d{4}-\d{2}-\d{2}/', $start_date)) {
            $where["$table.datetime"] = $ds->prepare('>= ?', "{$start_date} 00:00:00");
        }

        $where_owner = [
            $ds->prepare(
                "($table.owner_id = ?1 AND $table.owner_class = ?2)",
                $user->_id,
                $user->_class
            ),
        ];

        // AppFine Logger
        if (get_class($logger) === CAppFineSyncHistory::class) {
            if ($patient_ids = $user->loadPatientIds()) {
                $where_owner[] = "(appfine_sync_history.owner_class = 'CPatient' AND appfine_sync_history.owner_id "
                    . $ds->prepareIn($patient_ids) . ')';
            }
        }

        // tamm Logger
        if (get_class($logger) === CSyncLog::class) {
            if ($function_ids = CSyncLog::getFunctionIDs()) {
                $in            = $ds->prepareIn($function_ids);
                $where_owner[] = "(sync_log.owner_class = 'CFunctions' AND sync_log.owner_id " . $in . ')';
            }
        }

        $where[] = implode(' OR ', $where_owner);

        $limit    = $request_api->getLimitAsSql();
        $order_by = $request_api->getSortAsSql("`$primary_id` ASC");

        $logs       = $logger->loadList($where, $order_by, $limit);
        $count_logs = $logger->countList($where);

        // create items
        /** @var Collection $items */
        $items = Collection::createFromRequest($request_api, $logs);
        $items->createLinksPagination($request_api->getOffset(), $request_api->getLimit(), $count_logs);

        // Adding meta attributes
        if (CModule::getActive('appFine')) {
            $items->addMeta('version', CAppUI::conf('appFine pwa version', 'static'));
        }

        return $items;
    }
}
