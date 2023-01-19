<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Controllers\Legacy;

use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\System\Cron\CCronJob;
use Ox\Mediboard\System\Cron\CCronJobLog;

/**
 * Legacy Controller to manage DataSources
 */
class CCronJobLegacyController extends CLegacyController
{
    private const MAX_CRONJOB = 15;

    public function vw_cronjob(): void
    {
        $this->checkPermAdmin();

        $log_cron            = new CCronJobLog();
        $log_cron->_date_min = CMbDT::dateTime("-7 DAY");
        $log_cron->_date_max = CMbDT::dateTime("+1 DAY");

        $log_purge            = new CCronJobLog();
        $log_purge->_date_max = CMbDT::date('last day of last month') . ' 23:59:59';

        $params              = [];
        $params["log_cron"]  = $log_cron;
        $params["log_purge"] = $log_purge;

        $this->renderSmarty("vw_cronjob", $params);
    }

    public function ajax_list_cronjobs(): void
    {
        $cronjob      = new CCronJob();
        $list_filters = $this->filtersCronJob($cronjob);
        $list_page    = (int)CValue::get("page", 0);

        CView::checkin();

        $list_cronjobs = $this->loadCronJob($cronjob, $list_filters);

        CStoredObject::massLoadFwdRef($list_cronjobs, 'token_id');

        foreach ($list_cronjobs as $_cronjob) {
            $_cronjob->loadRefToken();
            $_cronjob->getNextDate();
            $_cronjob->loadLastsStatus();
        }

        $list_total_exchanges = $this->countCronJob(
            $cronjob,
            $list_filters
        );

        $params                    = [];
        $params["cronjobs"]        = $list_cronjobs;
        $params["total_exchanges"] = $list_total_exchanges;
        $params["page_cronjob"]    = $list_page;

        $this->renderSmarty("inc_list_cronjobs", $params);
    }

    public function ajax_cronjobs_logs(): void
    {
        $log          = new CCronJobLog();
        $log_filters  = $this->filtersCronJob($log);
        $log_cronjobs = $this->loadCronJob($log, $log_filters);

        CCronJobLog::massLoadFwdRef($log_cronjobs, "cronjob_id");
        foreach ($log_cronjobs as $_log) {
            $_log->loadRefCronJob();
        }

        $log_total_exchanges = $this->countCronJob(
            $log,
            $log_filters
        );

        $log_page = (int)CValue::get("page", 0);

        $params             = [];
        $params["logs"]     = $log_cronjobs;
        $params["nb_log"]   = $log_total_exchanges;
        $params["page_log"] = $log_page;


        $this->renderSmarty("inc_cronjobs_logs", $params);
    }

    private function loadCronJob(object $object, array $filters): array
    {
        $list_cronjob = $object->loadList(
            CMbArray::get($filters, "where"),
            CMbArray::get($filters, "order"),
            CMbArray::get($filters, "limit")
        );

        return $list_cronjob;
    }

    private function filtersCronJob(CStoredObject $object): array
    {
        $cronjob_id = CValue::get("cronjob_id");
        $page       = (int)CValue::get("page", 0);
        $where      = null;
        $filters    = [];

        if ($object instanceof CCronJob) {
            $active = CValue::get("active_filter");

            if ($active == "0" or $active == "1") {
                $where["active"] = " = '$active'";
            }

            if ($cronjob_id) {
                $where["cronjob_id"] = "= '$cronjob_id'";
            }

            $filters["order"] = "cronjob_id DESC";

            $filters["limit"] = $page . "," . self::MAX_CRONJOB;
        } elseif ($object instanceof CCronJobLog) {
            $status   = CValue::get("status");
            $severity = CValue::get("severity");
            $date_min = CValue::get("_date_min");
            $date_max = CValue::get("_date_max");
            $where    = [];

            if ($status) {
                $where["status"] = "= '$status'";
            }

            if ($severity) {
                $where["severity"] = "= '$severity'";
            }

            if ($cronjob_id) {
                $where["cronjob_id"] = "= '$cronjob_id'";
            }

            if ($date_min) {
                $where["start_datetime"] = ">= '$date_min'";
            }

            if ($date_max) {
                $where["start_datetime"] = $date_min ? $where["start_datetime"] . "AND start_datetime <= '$date_max'" : "<= '$date_max'";
            }
            $filters["order"] = "start_datetime DESC";
        }

        $filters["where"] = $where;

        $filters["limit"] = $page . "," . self::MAX_CRONJOB;

        return $filters;
    }

    private function countCronJob(object $object, array $filters): int
    {
        return $object->countList(CMbArray::get($filters, "where"));
    }

    public function ajax_edit_cronjob()
    {
        $identifiant = CValue::get("identifiant");
        $list_ip     = trim(CAppUI::conf("servers_ip"));
        $address     = [];

        if ($list_ip) {
            $address = preg_split("/\s*,\s*/", $list_ip, -1, PREG_SPLIT_NO_EMPTY);
        }

        $cronjob = new CCronJob();
        $cronjob->load($identifiant);

        $cronjob->loadRefToken();

        $params            = [];
        $params["cronjob"] = $cronjob;
        $params["address"] = $address;

        $this->renderSmarty("inc_edit_cronjob", $params);
    }
}
