<?php
/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\AccessLog;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbDT;
use Ox\Core\Config\Conf;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\Elastic\ElasticClient;
use Ox\Core\Redis\CRedisClient;
use Ox\Core\Sessions\CSessionHandler;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CModuleAction;
use Symfony\Component\HttpFoundation\Request;

/**
 * Construct, Support, Hydrate and bufferize access_log per hit
 */
class AccessLogManager
{

    private Conf        $conf;
    private CAccessLog $access_log;
    private ?string    $module;
    private ?string    $action;

    private function __construct(?string $module = null, ?string $action = null)
    {
        $this->module     = $module;
        $this->action     = $action;
        $this->access_log = new CAccessLog();
        $this->conf       = new Conf();
    }

    public function log(): void
    {
        if (!$this->supports()) {
            CApp::log('not supported');

            return;
        }

        $this->hydrate();

        $this->bufferize();
    }

    public function supports(): bool
    {
        if ($this->module === null || $this->action === null) {
            return false;
        }

        if (CApp::isReadonly() || !$this->conf->get("log_access")) {
            return false;
        }

        $ds = $this->access_log->getDS();
        if (!$ds->hasTable('module_action')) {
            return false;
        }

        return true;
    }

    /**
     * moved from includes/access_log.php
     * @return void
     * @throws Exception
     */
    private function hydrate(): void
    {
        $this->access_log->module_action_id = CModuleAction::getID($this->module, $this->action);

        // 10-minutes period aggregation
        // Don't CMbDT::datetime() to get rid of CAppUI::conf("system_date") if ever used
        $period                   = CMbDT::strftime("%Y-%m-%d %H:%M:00");
        $period[15]               = "0";
        $this->access_log->period = $period;

        // 10 minutes granularity
        $this->access_log->aggregate = 10;

        // Session duration
        [$acquire_duration, $read_duration] = CSessionHandler::getDurations();
        $this->access_log->session_read = round($read_duration, 3);
        $this->access_log->session_wait = round($acquire_duration, 3);

        // One hit
        $this->access_log->hits++;

        // Keep the scalar conversion
        // TODO [public] Revert user check when restoring public
        $this->access_log->bot = (CApp::getInstance()->isPublic() || !CAppUI::$user) ? 2 : (CApp::$is_robot ? 1 : 0);

        // Stop chrono if not already done
        $chrono = CApp::$chrono;
        if ($chrono->step > 0) {
            $chrono->stop();
        }
        $this->access_log->duration = round((float)$chrono->total, 3);

        // Redis stats
        $redis_chrono = CRedisClient::$chrono;
        if ($redis_chrono) {
            $this->access_log->nosql_time     = round((float)$redis_chrono->total, 3);
            $this->access_log->nosql_requests = $redis_chrono->nbSteps;
        }

        // Elasticsearch Stats
        $elastic_chrono = ElasticClient::getChrono();
        if ($elastic_chrono) {
            $this->access_log->nosql_time     = round((float)$elastic_chrono->total, 3);
            $this->access_log->nosql_requests = $elastic_chrono->nbSteps;
        }

        // System probes
        $rusage                        = getrusage();
        $this->access_log->processus   = round(
            (float)$rusage["ru_utime.tv_usec"] / 1000000 + $rusage["ru_utime.tv_sec"],
            3
        );
        $this->access_log->processor   = round(
            (float)$rusage["ru_stime.tv_usec"] / 1000000 + $rusage["ru_stime.tv_sec"],
            3
        );
        $this->access_log->peak_memory = memory_get_peak_usage();

        // SQL stats
        foreach (CSQLDataSource::$dataSources as $_datasource) {
            if ($_datasource) {
                $this->access_log->request     += round((float)$_datasource->chrono->total, 3);
                $this->access_log->nb_requests += $_datasource->chrono->nbSteps;
            }
        }

        // Transport tiers
        foreach (CExchangeSource::$call_traces as $_chrono) {
            $this->access_log->transport_tiers_nb   += $_chrono->nbSteps;
            $this->access_log->transport_tiers_time += $_chrono->total;
        }

        // Bandwidth
        $this->access_log->size = CApp::getOuputBandwidth();

        // Error log stats
        $this->access_log->errors   = CApp::$performance["error"];
        $this->access_log->warnings = CApp::$performance["warning"];
        $this->access_log->notices  = CApp::$performance["notice"];
    }

    private function bufferize(): void
    {
        CAccessLog::$_current = $this->access_log; // todo rm when ref long_request_log to poo
        CAccessLog::bufferize([$this->access_log]);
    }


    /**
     * legacy mode
     * @return AccessLogManager
     */
    public static function createFromGlobals(): AccessLogManager
    {
        global $m, $a, $action, $dosql;
        $_action = CValue::first($dosql, $action, $a);

        return new self($m, $_action);
    }

    /**
     * api/gui mode
     *
     * @param Request $request
     *
     * @return void
     * @throws Exception
     */
    public static function createFromRequest(Request $request): AccessLogManager
    {
        $request_controller = $request->attributes->get('_controller');

        if ($request_controller === null) {
            // Direct response (or exception handled)
            return new self();
        }

        [$controller, $action] = explode('::', $request_controller);

        $map = CClassMap::getInstance()->getClassMap($controller);

        $module = $map->module;
        $action = $map->short_name . '::' . $action;

        return new self($module, $action);
    }

    /**
     * @return string
     */
    public function getModule(): string
    {
        return $this->module;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return CAccessLog
     */
    public function getAccessLog(): CAccessLog
    {
        return $this->access_log;
    }

    /**
     * @param Conf $conf
     */
    public function setConf(Conf $conf): void
    {
        $this->conf = $conf;
    }


}
