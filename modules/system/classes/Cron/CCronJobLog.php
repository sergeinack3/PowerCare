<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Cron;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;

/**
 * Cronjob log
 */
class CCronJobLog extends CMbObject
{
    public const SEVERITY_NONE    = 0;
    public const SEVERITY_INFO    = 1;
    public const SEVERITY_WARNING = 2;
    public const SEVERITY_ERROR   = 3;

    public static $error_map = [
        self::SEVERITY_NONE    => '',
        self::SEVERITY_INFO    => 'INFO',
        self::SEVERITY_WARNING => 'WARNING',
        self::SEVERITY_ERROR   => 'ERROR',
    ];

    private static ?int $current_cron_job_log_id = null;

    /** @var int Primary key */
    public $cronjob_log_id;

    public $cronjob_id;

    public $status;
    public $log;
    public $severity;

    public $start_datetime;
    public $end_datetime;
    public $duration;
    public $server_address;
    public $request_uid;

    /** @var CCronJob */
    public $_ref_cronjob;

    public $_duration;

    public $_date_min;
    public $_date_max;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec           = parent::getSpec();
        $spec->table    = "cronjob_log";
        $spec->key      = "cronjob_log_id";
        $spec->loggable = false;

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();

        // Keep the old duration for now
        if (!$this->duration && $this->end_datetime && $this->start_datetime) {
            $this->_duration = CMbDT::timeRelative($this->start_datetime, $this->end_datetime);
        }
    }

    /**
     * @inheritdoc
     */
    public function store(): ?string
    {
        /* Possible purge when creating a CCronJobLog */
        if (!$this->_id) {
            CApp::doProbably(CAppUI::conf('CCronJobLog_purge_probability'), ['CCronJobLog', 'purgeSome']);
        }

        return parent::store();
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props["status"]         = "str notNull";
        $props["log"]            = "text";
        $props["severity"]       = "enum notNull list|0|1|2|3 default|0";
        $props["cronjob_id"]     = "ref class|CCronJob notNull autocomplete|name back|cron_logs cascade";
        $props["start_datetime"] = "dateTime notNull";
        $props["end_datetime"]   = "dateTime";
        $props["server_address"] = "str";
        $props["duration"]       = "num";
        $props["request_uid"]    = "str";

        //filter
        $props["_date_min"] = "dateTime";
        $props["_date_max"] = "dateTime";

        $props["_duration"] = "str";

        return $props;
    }

    /**
     * Load the cronjob
     *
     * @return CCronJob|null
     * @throws Exception
     */
    public function loadRefCronJob(): ?CCronJob
    {
        return $this->_ref_cronjob = $this->loadFwdRef("cronjob_id");
    }

    /**
     * Purge the CCronJobLog older than the configured delay
     *
     * @return bool|resource|void
     * @throws Exception
     */
    public static function purgeSome()
    {
        if (!$delay = CAppUI::conf('CCronJobLog_purge_delay')) {
            return null;
        }

        $date  = CMbDT::dateTime("- {$delay} days");
        $limit = (CAppUI::conf('CCronJobLog_purge_probability') ?: 1000) * 10;

        $ds = CSQLDataSource::get('std');

        $query = new CRequest();
        $query->addTable('cronjob_log');
        $query->addWhere(
            [
                'end_datetime' => $ds->prepare('IS NOT NULL AND `end_datetime` < ?', $date),
            ]
        );
        $query->setLimit($limit);

        return $ds->exec($query->makeDelete());
    }

    public static function getCronJobLogId(): ?int
    {
        return static::$current_cron_job_log_id;
    }

    public static function setCronJobLogId(int $cron_job_log_id): void
    {
        static::$current_cron_job_log_id = $cron_job_log_id;

        $cronjob_log = CCronJobLog::find($cron_job_log_id);
        if ($cronjob_log->_id) {
            $cronjob_log->request_uid = CApp::getRequestUID();
            $cronjob_log->store();
        }
    }
}
