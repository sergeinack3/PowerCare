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
use Ox\Core\Chronometer;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\Logger\LoggerLevels;
use Ox\Mediboard\System\CModuleAction;

/**
 * Access Log
 */
class CAccessLog extends CStoredObject
{
    protected const HUMAN_LEVEL  = 0;
    protected const BOT_LEVEL    = 1;
    protected const PUBLIC_LEVEL = 2;

    protected const LEVELS = [
        self::HUMAN_LEVEL,
        self::BOT_LEVEL,
        self::PUBLIC_LEVEL,
    ];

    public $accesslog_id;
    // DB Fields

    // log unique logical key fields (signature)
    public $module_action_id;
    public $period;

    public $bot;
    public $aggregate;

    // Log data fields
    public $hits;
    public $duration;
    public $request;
    public $nb_requests;
    public $nosql_time     = 0;
    public $nosql_requests = 0;
    public $io_time;
    public $io_requests;
    public $peak_memory;
    public $size;
    public $errors;
    public $warnings;
    public $notices;
    public $session_wait;
    public $session_read;
    public $transport_tiers_nb;
    public $transport_tiers_time;

    // todo Remove those useless fields
    public $processus;
    public $processor;

    // Derived fields
    public $_module;
    public $_action;
    public $_php_duration;

    // Average fields
    public $_average_hits;
    public $_average_duration;
    public $_average_request;
    public $_average_nb_requests;
    public $_average_peak_memory;
    public $_average_size;
    public $_average_errors;
    public $_average_warnings;
    public $_average_notices;
    public $_average_session_wait;
    public $_average_session_read;
    public $_average_processus;
    public $_average_processor;
    public $_average_php_duration;
    public $_average_nosql_time;
    public $_average_nosql_requests;
    public $_average_transport_tiers_nb;
    public $_average_transport_tiers_time;


    static $left_modes = [
        "duration_mode",
        "error_mode",
        "memory_mode",
        "data_mode",
        "session_mode",
    ];

    static $right_modes = [
        "hits",
        "size",
    ];

    /** @var self */
    static $_current;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->loggable = false;
        $spec->table    = 'access_log';
        $spec->key      = 'accesslog_id';

        $spec->iodkus[] = ['module_action_id', 'period', 'aggregate', 'bot'];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                     = parent::getProps();
        $props["module_action_id"] = "ref class|CModuleAction notNull back|access_logs";
        $props["period"]           = "dateTime notNull";
        $props["hits"]             = "num pos notNull";
        $props["duration"]         = "float notNull";
        $props["session_wait"]     = "float";
        $props["session_read"]     = "float";
        $props["request"]          = "float notNull";
        $props["nb_requests"]      = "num";
        $props["nosql_time"]       = "float";
        $props["nosql_requests"]   = "num";
        $props["io_time"]          = "float";
        $props["io_requests"]      = "num";
        $props["processus"]        = "float";
        $props["processor"]        = "float";
        $props["peak_memory"]      = "num min|0 max|4294967296"; // 2^32 > 2^32 - 1 (INT), so CNumSpec guesses BIGINT.
        $props["size"]             = "num min|0";
        $props["errors"]           = "num min|0";
        $props["warnings"]         = "num min|0";
        $props["notices"]          = "num min|0";
        $props["aggregate"]        = "num min|0 default|10";
        $props["bot"]              = "enum list|" . implode('|', self::LEVELS) . " default|" . self::HUMAN_LEVEL;

        $props["transport_tiers_nb"]   = "num min|0";
        $props["transport_tiers_time"] = "float";

        $props["_module"]       = "str";
        $props["_action"]       = "str";
        $props["_php_duration"] = "float notNull";

        $props["_average_duration"]             = "num min|0";
        $props["_average_session_wait"]         = "num min|0";
        $props["_average_session_read"]         = "num min|0";
        $props["_average_request"]              = "num min|0";
        $props["_average_peak_memory"]          = "num min|0";
        $props["_average_nb_requests"]          = "num min|0";
        $props["_average_nosql_time"]           = "num min|0";
        $props["_average_nosql_requests"]       = "num min|0";
        $props["_average_transport_tiers_nb"]   = "num min|0";
        $props["_average_transport_tiers_time"] = "num min|0";

        return $props;
    }

    /**
     * @inheritdoc
     */
    static function getSignatureFields()
    {
        return ["module_action_id", "period", "aggregate", "bot"];
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $this->_php_duration = $this->duration - $this->request - $this->nosql_time - $this->transport_tiers_time;
        if ($this->hits) {
            $this->_average_duration             = $this->duration / $this->hits;
            $this->_average_session_wait         = $this->session_wait / $this->hits;
            $this->_average_session_read         = $this->session_read / $this->hits;
            $this->_average_processus            = $this->processus / $this->hits;
            $this->_average_processor            = $this->processor / $this->hits;
            $this->_average_request              = $this->request / $this->hits;
            $this->_average_nb_requests          = $this->nb_requests / $this->hits;
            $this->_average_peak_memory          = $this->peak_memory / $this->hits;
            $this->_average_errors               = $this->errors / $this->hits;
            $this->_average_warnings             = $this->warnings / $this->hits;
            $this->_average_notices              = $this->notices / $this->hits;
            $this->_average_php_duration         = $this->_php_duration / $this->hits;
            $this->_average_nosql_time           = $this->nosql_time / $this->hits;
            $this->_average_nosql_requests       = $this->nosql_requests / $this->hits;
            $this->_average_transport_tiers_nb   = $this->transport_tiers_nb / $this->hits;
            $this->_average_transport_tiers_time = $this->transport_tiers_time / $this->hits;
        }

        // If time period == 1 hour
        $this->_average_hits = $this->hits / 3600; // hits per sec
        $this->_average_size = $this->size / 3600; // size per sec
    }

    /**
     * Load aggregated statistics
     *
     * @param string $start     Start date
     * @param string $end       End date
     * @param int    $groupmod  Grouping mode
     * @param null   $module    Module name
     * @param string $user_type Human/bot/public filter
     *
     * @return static[]
     * @todo A partir de cette méthode, il faut compléter les champs de session
     *
     */
    static function loadAggregation($start, $end, $groupmod = 0, $module = null, $user_type = null)
    {
        $al    = new static();
        $table = $al->_spec->table;

        switch ($groupmod) {
            case 2:
                $query = "SELECT
            $table.`accesslog_id`,
            $table.`module_action_id`,
            SUM($table.`hits`)                   AS hits,
            SUM($table.`size`)                   AS size,
            SUM($table.`duration`)               AS duration,
            SUM($table.`session_read`)           AS session_read,
            SUM($table.`session_wait`)           AS session_wait,
            SUM($table.`processus`)              AS processus,
            SUM($table.`processor`)              AS processor,
            SUM($table.`request`)                AS request,
            SUM($table.`nb_requests`)            AS nb_requests,
            SUM($table.`nosql_time`)             AS nosql_time,
            SUM($table.`nosql_requests`)         AS nosql_requests,
            SUM($table.`peak_memory`)            AS peak_memory,
            SUM($table.`errors`)                 AS errors,
            SUM($table.`warnings`)               AS warnings,
            SUM($table.`notices`)                AS notices,
            SUM($table.`transport_tiers_nb`)     AS transport_tiers_nb,
            SUM($table.`transport_tiers_time`)   AS transport_tiers_time,
            0 AS grouping
          FROM $table
          WHERE $table.`period` BETWEEN '$start' AND '$end'";
                break;

            case 0:
            case 1:
            default:
                $query = "SELECT
          $table.`accesslog_id`,
          $table.`module_action_id`,
          `module_action`.`module`           AS `_module`,
          `module_action`.`action`           AS `_action`,
          SUM($table.`hits`)                 AS `hits`,
          SUM($table.`size`)                 AS `size`,
          SUM($table.`duration`)             AS `duration`,
          SUM($table.`session_read`)         AS `session_read`,
          SUM($table.`session_wait`)         AS `session_wait`,
          SUM($table.`processus`)            AS `processus`,
          SUM($table.`processor`)            AS `processor`,
          SUM($table.`request`)              AS `request`,
          SUM($table.`nb_requests`)          AS `nb_requests`,
          SUM($table.`nosql_time`)           AS `nosql_time`,
          SUM($table.`nosql_requests`)       AS `nosql_requests`,
          SUM($table.`peak_memory`)          AS `peak_memory`,
          SUM($table.`errors`)               AS `errors`,
          SUM($table.`warnings`)             AS `warnings`,
          SUM($table.`notices`)              AS `notices`,
          SUM($table.`transport_tiers_nb`)   AS transport_tiers_nb,
          SUM($table.`transport_tiers_time`) AS transport_tiers_time,
          0 AS grouping
        FROM $table
        LEFT JOIN `module_action` ON $table.`module_action_id` = `module_action`.`module_action_id`
        WHERE $table.`period` BETWEEN '$start' AND '$end'";
        }

        // '0' means everything
        if ($user_type === '1') {
            $query .= "\nAND $table.`bot` = '0' ";
        } elseif ($user_type === '2') {
            $query .= "\nAND $table.`bot` = '1' ";
        } elseif ($user_type === '3') {
            $query .= "\nAND $table.`bot` = '2' ";
        }

        if ($module && !$groupmod) {
            $query .= "\nAND `module_action`.`module` = '$module' ";
        }

        switch ($groupmod) {
            case 2:
                $query .= "GROUP BY grouping ";
                break;
            case 1:
                $query .= "GROUP BY `module_action`.`module` ORDER BY `module_action`.`module` ";
                break;
            case 0:
                $query .= "GROUP BY `module_action`.`module`, `module_action`.`action` ORDER BY `module_action`.`module`, `module_action`.`action` ";
                break;
        }

        return $al->loadQueryList($query);
    }

    /**
     * Build aggregated stats for a period
     *
     * @param string $start         Start date time
     * @param string $end           End date time
     * @param string $period_format Period format
     * @param string $module_name   Module name
     * @param string $action_name   Action name
     * @param string $user_type     Human/bot/public filter
     *
     * @return static[]
     */
    static function loadPeriodAggregation($start, $end, $period_format, $module_name, $action_name, $user_type)
    {
        $al    = new static();
        $table = $al->_spec->table;

        // Convert date format from PHP to MySQL
        $period_format = str_replace("%M", "%i", $period_format);

        $query = "SELECT
        `accesslog_id`,
        `period`,
        SUM(`hits`)           AS `hits`,
        SUM(`size`)           AS `size`,
        SUM(`duration`)       AS `duration`,
        SUM(`session_read`)   AS `session_read`,
        SUM(`session_wait`)   AS `session_wait`,
        SUM(`processus`)      AS `processus`,
        SUM(`processor`)      AS `processor`,
        SUM(`request`)        AS `request`,
        SUM(`nb_requests`)    AS `nb_requests`,
        SUM(`nosql_time`)     AS `nosql_time`,
        SUM(`nosql_requests`) AS `nosql_requests`,
        SUM(`peak_memory`)    AS `peak_memory`,
        SUM(`errors`)         AS `errors`,
        SUM(`warnings`)       AS `warnings`,
        SUM(`notices`)        AS `notices`,       
        SUM(`transport_tiers_nb`)   AS transport_tiers_nb,
        SUM(`transport_tiers_time`) AS transport_tiers_time,
        DATE_FORMAT(`period`, '$period_format') AS `gperiod`
      FROM $table
      WHERE `period` BETWEEN '$start' AND '$end'";

        // '0' means everything
        if ($user_type === '1') {
            $query .= "\nAND bot = '0' ";
        } elseif ($user_type === '2') {
            $query .= "\nAND bot = '1' ";
        } elseif ($user_type === '3') {
            $query .= "\nAND bot = '2' ";
        }

        if ($module_name) {
            $actions = CModuleAction::getActions($module_name);
            if ($action_name) {
                $action_id = $actions[$action_name];
                $query     .= "\nAND `module_action_id` = '$action_id'";
            } else {
                $query .= "\nAND `module_action_id` " . CSQLDataSource::prepareIn(array_values($actions));
            }
        }

        $query .= "\nGROUP BY `gperiod`";

        return $al->loadQueryList($query);
    }

    /**
     * Compute Flotr graph
     *
     * @param string  $module_name Module name
     * @param string  $action_name Action name
     * @param integer $startx      Start date
     * @param integer $endx        End date
     * @param string  $interval    Interval
     * @param array   $left        Left axis
     * @param array   $right       Right axis
     * @param bool    $user_type   Human/bot/public filter
     *
     * @return array
     */
    static function graphAccessLog($module_name, $action_name, $startx, $endx, $interval, $left, $right, $user_type)
    {
        $al = new static;

        switch ($interval) {
            default:
            case "one-day":
                $step          = "+10 MINUTES";
                $period_format = "%H:%M";
                $hours         = 1 / 6;
                $ticks_modulo  = 4;
                break;
            case "one-week":
                $step          = "+1 HOUR";
                $period_format = "%a %d %Hh";
                $hours         = 1;
                $ticks_modulo  = 8;
                break;

            case "eight-weeks":
                $step          = "+1 DAY";
                $period_format = "%d/%m";
                $hours         = 24;
                $ticks_modulo  = 3;
                break;

            case "one-year":
                $step          = "+1 WEEK";
                $period_format = "%Y S%U";
                $hours         = 24 * 7;
                $ticks_modulo  = 3;
                break;

            case "four-years":
                $step          = "+1 MONTH";
                $period_format = "%m/%Y";
                $hours         = 24 * 30;
                $ticks_modulo  = 2;
                break;

            case "twenty-years":
                $step          = "+1 YEAR";
                $period_format = "%Y";
                $hours         = 24 * 30 * 12;
                $ticks_modulo  = 1;
                break;
        }

        $datax = [];
        $i     = 0;
        for ($d = $startx; $d <= $endx; $d = CMbDT::dateTime($step, $d)) {
            $datax[] = [$i, CMbDT::format($d, $period_format)];
            $i++;
        }

        $logs = $al::loadPeriodAggregation($startx, $endx, $period_format, $module_name, $action_name, $user_type);

        $duration       = [];
        $session_wait   = [];
        $session_read   = [];
        $processus      = [];
        $processor      = [];
        $request        = [];
        $nb_requests    = [];
        $nosql_time     = [];
        $nosql_requests = [];
        $peak_memory    = [];
        $errors         = [];
        $warnings       = [];
        $notices        = [];
        $_php_duration  = [];

        $transport_tiers_nb   = [];
        $transport_tiers_time = [];

        $hits = [];
        $size = [];

        $datetime_by_index = [];

        $errors_total = 0;
        foreach ($datax as $x) {
            // Needed
            $duration       [$x[0]] = [$x[0], 0];
            $session_wait   [$x[0]] = [$x[0], 0];
            $session_read   [$x[0]] = [$x[0], 0];
            $processus      [$x[0]] = [$x[0], 0];
            $processor      [$x[0]] = [$x[0], 0];
            $request        [$x[0]] = [$x[0], 0];
            $nb_requests    [$x[0]] = [$x[0], 0];
            $nosql_time     [$x[0]] = [$x[0], 0];
            $nosql_requests [$x[0]] = [$x[0], 0];
            $peak_memory    [$x[0]] = [$x[0], 0];
            $errors         [$x[0]] = [$x[0], 0];
            $warnings       [$x[0]] = [$x[0], 0];
            $notices        [$x[0]] = [$x[0], 0];
            $_php_duration  [$x[0]] = [$x[0], 0];


            $transport_tiers_nb     [$x[0]] = [$x[0], 0];
            $transport_tiers_time   [$x[0]] = [$x[0], 0];

            $hits[$x[0]] = [$x[0], 0];
            $size[$x[0]] = [$x[0], 0];


            foreach ($logs as $log) {
                if ($x[1] == CMbDT::format($log->period, $period_format)) {
                    $duration       [$x[0]] = [$x[0], $log->{($left[1] == 'mean' ? '_average_' : '') . 'duration'}];
                    $session_wait   [$x[0]] = [$x[0], $log->{($left[1] == 'mean' ? '_average_' : '') . 'session_wait'}];
                    $session_read   [$x[0]] = [$x[0], $log->{($left[1] == 'mean' ? '_average_' : '') . 'session_read'}];
                    $processus      [$x[0]] = [$x[0], $log->{($left[1] == 'mean' ? '_average_' : '') . 'processus'}];
                    $processor      [$x[0]] = [$x[0], $log->{($left[1] == 'mean' ? '_average_' : '') . 'processor'}];
                    $request        [$x[0]] = [$x[0], $log->{($left[1] == 'mean' ? '_average_' : '') . 'request'}];
                    $nb_requests    [$x[0]] = [$x[0], $log->{($left[1] == 'mean' ? '_average_' : '') . 'nb_requests'}];
                    $nosql_time     [$x[0]] = [$x[0], $log->{($left[1] == 'mean' ? '_average_' : '') . 'nosql_time'}];
                    $nosql_requests [$x[0]] = [
                        $x[0],
                        $log->{($left[1] == 'mean' ? '_average_' : '') . 'nosql_requests'},
                    ];
                    $peak_memory    [$x[0]] = [$x[0], $log->{($left[1] == 'mean' ? '_average_' : '') . 'peak_memory'}];
                    $errors         [$x[0]] = [$x[0], $log->{($left[1] == 'mean' ? '_average_' : '') . 'errors'}];
                    $warnings       [$x[0]] = [$x[0], $log->{($left[1] == 'mean' ? '_average_' : '') . 'warnings'}];
                    $notices        [$x[0]] = [$x[0], $log->{($left[1] == 'mean' ? '_average_' : '') . 'notices'}];
                    $_php_duration  [$x[0]] = [$x[0], $log->{($left[1] == 'mean' ? '_average' : '') . '_php_duration'}];

                    $transport_tiers_nb    [$x[0]] = [
                        $x[0],
                        $log->{($left[1] == 'mean' ? '_average_' : '') . 'transport_tiers_nb'},
                    ];
                    $transport_tiers_time  [$x[0]] = [
                        $x[0],
                        $log->{($left[1] == 'mean' ? '_average_' : '') . 'transport_tiers_time'},
                    ];


                    $errors_total += $log->_average_errors + $log->_average_warnings + $log->_average_notices;

                    $hits[$x[0]] = [
                        $x[0],
                        $log->{($right[1] == 'mean' ? '_average_' : '') . 'hits'} / ($right[1] == 'mean' ? $hours : 1),
                    ];
                    $size[$x[0]] = [
                        $x[0],
                        $log->{($right[1] == 'mean' ? '_average_' : '') . 'size'} / ($right[1] == 'mean' ? $hours : 1),
                    ];

                    $datetime_by_index[$x[0]] = $log->period;
                }
            }
        }

        // Removing some xaxis ticks
        foreach ($datax as $i => &$x) {
            if ($i % $ticks_modulo) {
                $x[1] = '';
            }
        }

        $title = '';
        if ($module_name) {
            $title .= CAppUI::tr("module-$module_name-court");
        }
        if ($action_name) {
            $title .= " - $action_name";
        }

        $subtitle = CMbDT::format($endx, CAppUI::conf("longdate"));

        $options = [
            'title'       => $title,
            'subtitle'    => $subtitle,
            'xaxis'       => [
                'labelsAngle' => 45,
                'ticks'       => $datax,
            ],
            'yaxis'       => [
                'min'             => 0,
                'title'           => CAppUI::tr(
                        "CAccessLog-left_modes-" . $left[0]
                    ) . " " . ($left[1] == 'mean' ? '(par hit)' : ''),
                'autoscaleMargin' => 1,
            ],
            'y2axis'      => [
                'min'             => 0,
                'title'           => CAppUI::tr(
                        "CAccessLog-right_modes-" . $right[0]
                    ) . " " . ($right[1] == 'mean' ? '(par seconde)' : ''),
                'autoscaleMargin' => 1,
            ],
            'grid'        => [
                'verticalLines' => false,
            ],
            /*'mouse' => array(
              'track' => true,
              'relative' => true
            ),*/
            'HtmlText'    => false,
            'spreadsheet' => [
                'show'             => true,
                'csvFileSeparator' => ';',
                'decimalSeparator' => ',',
            ],
        ];

        $series = [];

        // Right modes (before in order the lines to be on top)
        switch ($right[0]) {
            case 'hits':
                $series[] = [
                    'label' => CAppui::tr("CAccessLog-hits"),
                    'data'  => $hits,
                    'bars'  => [
                        'show' => true,
                    ],
                    'yaxis' => 2,
                ];
                break;

            default:
                $series[] = [
                    'label' => CAppui::tr("CAccessLog-size"),
                    'data'  => $size,
                    'bars'  => [
                        'show' => true,
                    ],
                    'yaxis' => 2,
                ];
        }

        // Left modes
        switch ($left[0]) {
            default:
            case "duration_mode":
                $series[] = [
                    'label' => CAppui::tr("CAccessLog-duration"),
                    'data'  => $duration,
                    'lines' => [
                        'show' => true,
                    ],
                ];
                $series[] = [
                    'label' => CAppui::tr("CAccessLog-_php_duration"),
                    'data'  => $_php_duration,
                    'lines' => [
                        'show' => true,
                    ],
                ];
                $series[] = [
                    'label' => CAppui::tr("CAccessLog-request"),
                    'data'  => $request,
                    'lines' => [
                        'show' => true,
                    ],
                ];
                $series[] = [
                    'label' => CAppui::tr("CAccessLog-nosql_time"),
                    'data'  => $nosql_time,
                    'lines' => [
                        'show' => true,
                    ],
                ];
                $series[] = [
                    'label' => CAppui::tr("CAccessLog-transport_tiers_time"),
                    'data'  => $transport_tiers_time,
                    'lines' => [
                        'show' => true,
                    ],
                ];
                break;
            case "error_mode":
                if ($errors_total == 0) {
                    $options['yaxis']['max'] = 1;
                }

                $series[] = [
                    'label' => CAppui::tr("CAccessLog-errors"),
                    'data'  => $errors,
                    'color' => 'red',
                    'lines' => [
                        'show' => true,
                    ],
                ];
                $series[] = [
                    'label' => CAppui::tr("CAccessLog-warnings"),
                    'data'  => $warnings,
                    'color' => 'orange',
                    'lines' => [
                        'show' => true,
                    ],
                ];
                $series[] = [
                    'label' => CAppui::tr("CAccessLog-notices"),
                    'data'  => $notices,
                    'color' => 'yellow',
                    'lines' => [
                        'show' => true,
                    ],
                ];
                break;
            case "memory_mode":
                $series[] = [
                    'label' => CAppui::tr("CAccessLog-peak_memory"),
                    'data'  => $peak_memory,
                    'lines' => [
                        'show' => true,
                    ],
                ];
                break;
            case "data_mode":
                $series[] = [
                    'label' => CAppui::tr("CAccessLog-nb_requests"),
                    'data'  => $nb_requests,
                    'lines' => [
                        'show' => true,
                    ],
                ];
                $series[] = [
                    'label' => CAppui::tr("CAccessLog-nosql_requests"),
                    'data'  => $nosql_requests,
                    'lines' => [
                        'show' => true,
                    ],
                ];
                $series[] = [
                    'label' => CAppui::tr("CAccessLog-transport_tiers_nb"),
                    'data'  => $transport_tiers_nb,
                    'lines' => [
                        'show' => true,
                    ],
                ];
                break;
            case "session_mode":
                $series[] = [
                    'label' => CAppui::tr("CAccessLog-session_wait"),
                    'data'  => $session_wait,
                    'lines' => [
                        'show' => true,
                    ],
                ];
                $series[] = [
                    'label' => CAppui::tr("CAccessLog-session_read"),
                    'data'  => $session_read,
                    'lines' => [
                        'show' => true,
                    ],
                ];
                break;
        }

        return [
            'series'            => $series,
            'options'           => $options,
            'module'            => $module_name,
            'datetime_by_index' => $datetime_by_index,
        ];
    }

    /**
     * Ugly method combining two graphs, considering rates to preserve
     *
     * @param $groupmod
     * @param $graph_1
     * @param $graph_2
     *
     * @return mixed
     */
    static function combineGraphs($groupmod, $graph_1, $graph_2, $log = null)
    {
        switch ($groupmod) {
            case 0:
                $hits            = [];
                $graphic         = [];
                $archive_graphic = [];

                // Gets the data (hits-unrelated) and hits from 1st graph
                foreach ($graph_1[$log->_module . "-" . $log->_action]["series"] as $_k1 => $_serie) {
                    $graphic[$_serie['label']] = [];

                    foreach ($_serie['data'] as $_k2 => $_data) {
                        if (!isset($graphic[$_serie['label']][$_k2])) {
                            $graphic[$_serie['label']][$_k2] = ['hits' => 0, 'data' => 0];
                        }

                        if ($_serie['label'] == 'Hits') {
                            $hits[$_k2] = $_data[1];
                        } else {
                            $graphic[$_serie['label']][$_k2]['hits'] = $hits[$_k2];
                            $graphic[$_serie['label']][$_k2]['data'] = $_data[1];
                        }
                    }
                }

                // Gets the data (hits-unrelated) and hits from 2nd graph
                foreach ($graph_2["series"] as $_k1 => $_serie) {
                    $archive_graphic[$_serie['label']] = [];

                    foreach ($_serie['data'] as $_k2 => $_data) {
                        if (!isset($archive_graphic[$_serie['label']][$_k2])) {
                            $archive_graphic[$_serie['label']][$_k2] = ['hits' => 0, 'data' => 0];
                        }

                        if ($_serie['label'] == 'Hits') {
                            $hits[$_k2] = $_data[1];
                        } else {
                            $archive_graphic[$_serie['label']][$_k2]['hits'] = $hits[$_k2];
                            $archive_graphic[$_serie['label']][$_k2]['data'] = $_data[1];
                        }
                    }
                }

                unset($graphic['Hits']);
                unset($archive_graphic['Hits']);

                // Computes combination of the two graphs
                $total = [];
                foreach ($graphic as $_label => $_point) {
                    $total[$_label] = [];

                    foreach ($_point as $_k => $_data) {
                        $total[$_label][$_k] = [
                            'hits' => $_data['hits'],
                            'data' => $_data['data'] * $_data['hits'],
                        ];
                    }
                }

                foreach ($archive_graphic as $_label => $_point) {
                    if (!isset($total[$_label])) {
                        $total[$_label] = [];
                    }

                    foreach ($_point as $_k => $_data) {
                        if ($total[$_label][$_k]['hits'] + $_data['hits'] > 0) {
                            $total[$_label][$_k]['data'] = ($total[$_label][$_k]['data'] + $_data['data'] * $_data['hits']) / ($total[$_label][$_k]['hits'] + $_data['hits']);
                        } else {
                            $total[$_label][$_k]['data'] = 0;
                        }

                        $total[$_label][$_k]['hits'] += $_data['hits'];
                    }
                }

                // Re-assembles graphic with hits and data
                foreach ($total as $_label => $_values) {
                    foreach ($graph_1[$log->_module . "-" . $log->_action]['series'] as $_k1 => $_serie) {
                        if ($_serie['label'] == 'Hits') {
                            foreach ($_serie['data'] as $_k2 => $_data) {
                                $graph_1[$log->_module . "-" . $log->_action]['series'][$_k1]['data'][$_k2][1] = $_values[$_k2]['hits'];
                            }
                        }
                    }

                    foreach ($graph_1[$log->_module . "-" . $log->_action]['series'] as $_k1 => $_serie) {
                        if ($_serie['label'] == $_label) {
                            foreach ($_serie['data'] as $_k2 => $_data) {
                                $graph_1[$log->_module . "-" . $log->_action]['series'][$_k1]['data'][$_k2][1] = $_values[$_k2]['data'];
                            }
                        }
                    }
                }
                break;

            case 1:
                $hits            = [];
                $graphic         = [];
                $archive_graphic = [];

                // Gets the data (hits-unrelated) and hits from 1st graph
                foreach ($graph_1[$log->_module]["series"] as $_k1 => $_serie) {
                    $graphic[$_serie['label']] = [];

                    foreach ($_serie['data'] as $_k2 => $_data) {
                        if (!isset($graphic[$_serie['label']][$_k2])) {
                            $graphic[$_serie['label']][$_k2] = ['hits' => 0, 'data' => 0];
                        }

                        if ($_serie['label'] == 'Hits') {
                            $hits[$_k2] = $_data[1];
                        } else {
                            $graphic[$_serie['label']][$_k2]['hits'] = $hits[$_k2];
                            $graphic[$_serie['label']][$_k2]['data'] = $_data[1];
                        }
                    }
                }

                // Gets the data (hits-unrelated) and hits from 2nd graph
                foreach ($graph_2["series"] as $_k1 => $_serie) {
                    $archive_graphic[$_serie['label']] = [];

                    foreach ($_serie['data'] as $_k2 => $_data) {
                        if (!isset($archive_graphic[$_serie['label']][$_k2])) {
                            $archive_graphic[$_serie['label']][$_k2] = ['hits' => 0, 'data' => 0];
                        }

                        if ($_serie['label'] == 'Hits') {
                            $hits[$_k2] = $_data[1];
                        } else {
                            $archive_graphic[$_serie['label']][$_k2]['hits'] = $hits[$_k2];
                            $archive_graphic[$_serie['label']][$_k2]['data'] = $_data[1];
                        }
                    }
                }

                unset($graphic['Hits']);
                unset($archive_graphic['Hits']);

                // Computes combination of the two graphs
                $total = [];
                foreach ($graphic as $_label => $_point) {
                    $total[$_label] = [];

                    foreach ($_point as $_k => $_data) {
                        $total[$_label][$_k] = [
                            'hits' => $_data['hits'],
                            'data' => $_data['data'] * $_data['hits'],
                        ];
                    }
                }

                foreach ($archive_graphic as $_label => $_point) {
                    if (!isset($total[$_label])) {
                        $total[$_label] = [];
                    }

                    foreach ($_point as $_k => $_data) {
                        if ($total[$_label][$_k]['hits'] + $_data['hits'] > 0) {
                            $total[$_label][$_k]['data'] = ($total[$_label][$_k]['data'] + $_data['data'] * $_data['hits']) / ($total[$_label][$_k]['hits'] + $_data['hits']);
                        } else {
                            $total[$_label][$_k]['data'] = 0;
                        }

                        $total[$_label][$_k]['hits'] += $_data['hits'];
                    }
                }

                // Re-assembles graphic with hits and data
                foreach ($total as $_label => $_values) {
                    foreach ($graph_1[$log->_module]['series'] as $_k1 => $_serie) {
                        if ($_serie['label'] == 'Hits') {
                            foreach ($_serie['data'] as $_k2 => $_data) {
                                $graph_1[$log->_module]['series'][$_k1]['data'][$_k2][1] = $_values[$_k2]['hits'];
                            }
                        }
                    }

                    foreach ($graph_1[$log->_module]['series'] as $_k1 => $_serie) {
                        if ($_serie['label'] == $_label) {
                            foreach ($_serie['data'] as $_k2 => $_data) {
                                $graph_1[$log->_module]['series'][$_k1]['data'][$_k2][1] = $_values[$_k2]['data'];
                            }
                        }
                    }
                }
                break;

            case 2:
                $hits            = [];
                $graphic         = [];
                $archive_graphic = [];

                // Gets the data (hits-unrelated) and hits from 1st graph
                foreach ($graph_1["series"] as $_k1 => $_serie) {
                    $graphic[$_serie['label']] = [];

                    foreach ($_serie['data'] as $_k2 => $_data) {
                        if (!isset($graphic[$_serie['label']][$_k2])) {
                            $graphic[$_serie['label']][$_k2] = ['hits' => 0, 'data' => 0];
                        }

                        if ($_serie['label'] == 'Hits') {
                            $hits[$_k2] = $_data[1];
                        } else {
                            $graphic[$_serie['label']][$_k2]['hits'] = $hits[$_k2];
                            $graphic[$_serie['label']][$_k2]['data'] = $_data[1];
                        }
                    }
                }

                // Gets the data (hits-unrelated) and hits from 2nd graph
                foreach ($graph_2["series"] as $_k1 => $_serie) {
                    $archive_graphic[$_serie['label']] = [];

                    foreach ($_serie['data'] as $_k2 => $_data) {
                        if (!isset($archive_graphic[$_serie['label']][$_k2])) {
                            $archive_graphic[$_serie['label']][$_k2] = ['hits' => 0, 'data' => 0];
                        }

                        if ($_serie['label'] == 'Hits') {
                            $hits[$_k2] = $_data[1];
                        } else {
                            $archive_graphic[$_serie['label']][$_k2]['hits'] = $hits[$_k2];
                            $archive_graphic[$_serie['label']][$_k2]['data'] = $_data[1];
                        }
                    }
                }

                unset($graphic['Hits']);
                unset($archive_graphic['Hits']);

                // Computes combination of the two graphs
                $total = [];
                foreach ($graphic as $_label => $_point) {
                    $total[$_label] = [];

                    foreach ($_point as $_k => $_data) {
                        $total[$_label][$_k] = [
                            'hits' => $_data['hits'],
                            'data' => $_data['data'] * $_data['hits'],
                        ];
                    }
                }

                foreach ($archive_graphic as $_label => $_point) {
                    if (!isset($total[$_label])) {
                        $total[$_label] = [];
                    }

                    foreach ($_point as $_k => $_data) {
                        if ($total[$_label][$_k]['hits'] + $_data['hits'] > 0) {
                            $total[$_label][$_k]['data'] = ($total[$_label][$_k]['data'] + $_data['data'] * $_data['hits']) / ($total[$_label][$_k]['hits'] + $_data['hits']);
                        } else {
                            $total[$_label][$_k]['data'] = 0;
                        }

                        $total[$_label][$_k]['hits'] += $_data['hits'];
                    }
                }

                // Re-assembles graphic with hits and data
                foreach ($total as $_label => $_values) {
                    foreach ($graph_1['series'] as $_k1 => $_serie) {
                        if ($_serie['label'] == 'Hits') {
                            foreach ($_serie['data'] as $_k2 => $_data) {
                                $graph_1['series'][$_k1]['data'][$_k2][1] = $_values[$_k2]['hits'];
                            }
                        }
                    }

                    foreach ($graph_1['series'] as $_k1 => $_serie) {
                        if ($_serie['label'] == $_label) {
                            foreach ($_serie['data'] as $_k2 => $_data) {
                                $graph_1['series'][$_k1]['data'][$_k2][1] = $_values[$_k2]['data'];
                            }
                        }
                    }
                }
                break;
        }

        return $graph_1;
    }

    /**
     * Moved from CModuleActionLog
     * Gets summable plain fields, minus signature fields and key
     *
     * @return array
     */
    static function getValueFields()
    {
        $self = new static;

        $fields = $self->getPlainFields();
        unset($fields[$self->_spec->key]);

        return array_diff(array_keys($fields), $self->getSignatureFields());
    }

    /**
     * Moved from CModuleActionLog
     * Fast store for multiple access logs using ON DUPLICATE KEY UPDATE MySQL feature
     *
     * @param self[] $logs Logs to be stored
     *
     * @return string Store-like message
     */
    static function fastMultiStore($logs, &$chrono = null)
    {
        if (!count($logs)) {
            return null;
        }

        /** @var self $self */
        $self = new static;

        // Columns to update
        $updates = [];

        $fields = $self->getPlainFields();
        unset($fields[$self->_spec->key]);
        $columns = array_keys($fields);

        foreach ($self->getValueFields() as $_name) {
            $updates[] = "$_name = $_name + VALUES($_name)";
        }

        // Values
        $values = [];
        foreach ($logs as $_log) {
            $row = [];

            foreach (array_keys($fields) as $_name) {
                $value = $_log->$_name;
                $row[] = "'$value'";
            }

            $row      = implode(", ", $row);
            $row      = "($row)";
            $values[] = $row;
        }

        $columns = implode(", ", $columns);
        $updates = implode(", ", $updates);
        $values  = implode(",\n", $values);

        $table = $self->_spec->table;
        $query = "INSERT INTO $table ($columns)
      VALUES \n$values
      ON DUPLICATE KEY UPDATE $updates";

        $ds = $self->_spec->ds;

        if (!$ds->exec($query)) {
            return $ds->error();
        }

        if (!is_null($chrono)) {
            $chrono += $ds->chrono->latestStep;
        }

        return null;
    }

    /**
     * Moved from CModuleActionLog
     * Assemble logs based on logical key fields
     *
     * @param self[] $logs Raw access log collection
     *
     * @return self[] $logs Assembled access log collection
     */
    static function assembleLogs($logs)
    {
        $signature_fields = static::getSignatureFields();
        $value_fields     = static::getValueFields();

        $assembled_logs = [];
        foreach ($logs as $_log) {
            // Signature values
            $signature_values = [];

            foreach ($signature_fields as $_field) {
                $signature_values[] = $_log->$_field;
            }

            // Make signature
            $signature = implode(",", $signature_values);

            // First log for this signature
            if (!isset($assembled_logs[$signature])) {
                $assembled_logs[$signature]      = $_log;
                $assembled_logs[$signature]->_id = null;
                continue;
            }

            // Assembling (summing) other log for the same signature
            $log = $assembled_logs[$signature];

            foreach ($value_fields as $_name) {
                $log->$_name += $_log->$_name;
            }
        }

        return $assembled_logs;
    }

    /**
     * Moved from CModuleActionLog
     * Put logs in buffer and store them.
     * Use direct storage if buffer_life time config is 0
     *
     * @param self[] $logs Log collection to put in buffer
     *
     * @return void
     * @throws Exception
     */
    static function bufferize($logs)
    {
        /** @var CAccessLog $class */
        $class = get_called_class();
        $class = CClassMap::getInstance()->getShortName($class);

        // No buffer use standard unique fast store
        $buffer_lifetime = CAppUI::conf("access_log_buffer_lifetime");
        if (!$buffer_lifetime) {
            if ($msg = static::fastMultiStore($logs)) {
                CApp::log("Could not store logs: $msg", $class, LoggerLevels::LEVEL_DEBUG);
                trigger_error($msg, E_USER_WARNING);
            }

            return;
        }

        // Buffer logs into file
        $buffer = CAppUI::getTmpPath("$class.buffer");
        foreach ($logs as $_log) {
            file_put_contents($buffer, serialize($_log) . PHP_EOL, FILE_APPEND);
        }

        // Unless lifetime is reached by random, don't unbuffer logs
        if (rand(1, $buffer_lifetime) !== 1) {
            return;
        }

        // Move to temporary buffer to prevent concurrent unbuffering
        $tmpbuffer = tempnam(dirname($buffer), basename($buffer) . "classes");
        if (!rename($buffer, $tmpbuffer)) {
            // Keep the log for a while, should not be frequent with buffer lifetime 100+
            CApp::log("Probable concurrent logs unbuffering", $class, LoggerLevels::LEVEL_DEBUG);

            return;
        }

        // Read lines from temporary buffer
        $lines         = file($tmpbuffer);
        $buffered_logs = [];
        foreach ($lines as $_line) {
            $buffered_logs[] = unserialize($_line);
        }

        $assembled_logs = static::assembleLogs($buffered_logs);
        if ($msg = static::fastMultiStore($assembled_logs)) {
            trigger_error($msg, E_USER_WARNING);

            return;
        }

        // Remove the useless temporary buffer
        unlink($tmpbuffer);

        $aggregate_lifetime = CAppUI::conf("aggregate_lifetime");
        if (!$aggregate_lifetime) {
            CApp::log("Could not aggregate logs, no buffer set", $class, LoggerLevels::LEVEL_DEBUG);

            return;
        }

        CApp::doProbably(
            $aggregate_lifetime,
            function () use ($class) {
                $class::aggregate(false, false, true);

                if (!strpos($class, 'Archive')) {
                    /** @var CAccessLogArchiveLog $archive_class */
                    $archive_class = "{$class}Archive";
                    $archive_class::aggregate(false, false, true);
                }
            }
        );
    }

    /**
     * Moved from CModuleActionLog
     * Aggregates logs according to date
     *
     * @param bool|true  $dry_run  Dry run, for testing purposes
     * @param bool|false $show_msg Do we have to display ajax message?
     * @param bool|false $report   Do we have to log report?
     *
     * @return void
     * @throws Exception
     *
     */
    static function aggregate($dry_run = true, $show_msg = false, $report = false)
    {
        $php_chrono = new Chronometer();
        $php_chrono->start();
        $sql_chrono = 0;

        $messages = [];

        $log = new static;
        $ds  = $log->getDS();

        $levels = [
            'std' => [
                'current' => 10,
                'next'    => 60,
                'limit'   => CMbDT::date('- 1 MONTH'),
                'format'  => "%Y-%m-%d %H:00:00",
            ],
            'avg' => [
                'current' => 60,
                'next'    => 1440,
                'limit'   => CMbDT::date('- 1 YEAR'),
                'format'  => "%Y-%m-%d 00:00:00",
            ],
        ];

        $buffer_lifetime = (CAppUI::conf('access_log_buffer_lifetime')) ?: 100;
        $limit           = $buffer_lifetime * 10;

        foreach ($levels as $_name => $_level) {
            $where = [
                'period'    => "<= '{$_level['limit']}'",
                'aggregate' => "= '{$_level['current']}'",
            ];

            if ($dry_run) {
                $count = $log->countList($where);

                if ($show_msg) {
                    $msg = "%d logs to aggregate from level %s older than %s";
                    CAppUI::setMsg($msg, UI_MSG_OK, $count, $_level['current'], $_level['limit']);
                }

                continue;
            }

            $logs       = $log->loadList($where, null, $limit);
            $sql_chrono += $ds->chrono->latestStep;

            $count_aggregated = count($logs);

            $log->deleteAll(array_keys($logs));
            $sql_chrono += $ds->chrono->latestStep;

            foreach ($logs as $_log) {
                $_log->period    = CMbDT::format($_log->period, $_level['format']);
                $_log->aggregate = $_level['next'];
            }

            /** @var self $class */
            $class = $log->_class;
            if (!strpos($class, 'Archive')) {
                $class .= 'Archive';
            }

            $logs            = self::assembleLogs($logs);
            $count_assembled = count($logs);

            if ($msg = $class::fastMultiStore($logs, $sql_chrono)) {
                if ($show_msg) {
                    CAppUI::setMsg($msg, UI_MSG_ERROR);
                } elseif ($report) {
                    $messages[] = $msg;
                }

                continue;
            }

            if ($show_msg) {
                $msg = "%d logs inserted to level %s older than %s";
                CAppUI::setMsg($msg, UI_MSG_OK, $count_assembled, $_level['next'], $_level['limit']);
            } elseif ($report) {
                // Because of $padding may be dynamic, no locales used here
                $padding    = strlen((string)$limit);
                $text       = "%-21s niveau %4d < %s : %{$padding}d enregistrements supprimés, %{$padding}d agrégés";
                $messages[] = sprintf(
                    $text,
                    $log->_class,
                    $_level['current'],
                    $_level['limit'],
                    $count_aggregated,
                    $count_assembled
                );
            }
        }

        $temps_total = $php_chrono->stop();

        // Print final report
        if ($report) {
            $text         = 'Agrégation des journaux en %01.2f ms (%01.2f ms PHP / %01.2f ms SQL)';
            $query_report = [
                sprintf($text, $temps_total * 1000, ($temps_total - $sql_chrono) * 1000, $sql_chrono * 1000),
            ];

            $query_report = array_merge($query_report, $messages);

            CApp::log(implode("\n", $query_report), null, LoggerLevels::LEVEL_DEBUG);
        }
    }

}
