<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Redis;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\Chronometer;
use Ox\Core\CMbArray;
use Ox\Core\CMbString;
use Ox\Components\Yampee\Redis\Client;

/**
 * Redis client
 */
class CRedisClient extends Client
{
    /** @var Chronometer */
    static $chrono = null;

    /** @var bool */
    static $log = false;

    /** @var array[] */
    static $log_entries = [];

    /** @var array[] */
    static $report_data = [
        'totals'  => [
            'count'    => 0,
            'duration' => 0,
        ],
        'queries' => [],
    ];

    /**
     * @inheritdoc
     */
    function __construct($host = 'localhost', $port = 6379)
    {
        if (!static::$chrono) {
            static::$chrono = new Chronometer();
        }

        parent::__construct($host, $port);
    }

    /**
     * Connect (or reconnect) to Redis with given parameters
     *
     * @param float|int $timeout Timeout
     *
     * @return $this
     */
    public function connect(float $timeout = 5): Client
    {
        static::$chrono->start();
        $this->connection = new CRedisConnection($this->host, $this->port, $timeout);
        static::$chrono->stop();

        // Query log, durations in micro-seconds
        if (static::$log) {
            static::$log_entries[] = ['CONNECT', round(static::$chrono->latestStep * 1000000)];
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    function execute(array $arguments)
    {
        static::$chrono->start();
        $return = parent::execute($arguments);
        static::$chrono->stop();

        // Query log, durations in micro-seconds
        if (static::$log) {
            static::$log_entries[] = [implode(' ', $arguments), round(static::$chrono->latestStep * 1000000)];
        }

        return $return;
    }

    /**
     * Parse a multi line response
     *
     * @param string $response    Response
     * @param bool   $with_titles With titles or not
     *
     * @return array
     */
    function parseMultiLine($response, $with_titles = false)
    {
        $lines = preg_split('/[\r\n]+/', trim($response));

        $values = [];

        if ($with_titles) {
            $_current_group = [];
            $_current_title = null;
            foreach ($lines as $_line) {
                if ($_line[0] === "#") {
                    if ($_current_title && !empty($_current_group)) {
                        $values[$_current_title] = $_current_group;
                    }

                    $_current_title = trim(substr($_line, 1));
                    $_current_group = [];
                    continue;
                }

                [$_key, $_value] = explode(":", $_line, 2);
                $_current_group[$_key] = $_value;
            }

            if ($_current_title && !empty($_current_group)) {
                $values[$_current_title] = $_current_group;
            }
        } else {
            $lines = array_filter(
                $lines,
                function ($v) {
                    return strpos($v, "#") !== 0;
                }
            );

            foreach ($lines as $_line) {
                [$_key, $_value] = explode(":", $_line, 2);
                $values[$_key] = $_value;
            }
        }

        return $values;
    }

    /**
     * Makes a hash from the query
     *
     * @param string $query The query to hash
     *
     * @return string The hash
     */
    static function hashQuery($query)
    {
        // Remove SHM prefix
        $query = str_replace(CApp::getAppIdentifier(), '', $query);

        $_query  = explode(' ', $query);
        $command = (isset($_query[0])) ? $_query[0] : '';
        $key     = (isset($_query[1])) ? $_query[1] : '';

        // Remove numbers before calculating the hash
        $key = preg_replace('/\d+/', " %", $key);

        // Remove hexadecimal suffix at the end
        $key = preg_replace('/\-([\da-f]+)$/i', '<hexa>', $key);

        return md5("{$command} {$key}");
    }

    /**
     * Makes a sample from the query
     *
     * @param string $query The query
     *
     * @return string The sample
     */
    static function sampleQuery($query)
    {
        // Remove SHM prefix
        $query = str_replace(CApp::getAppIdentifier(), '', $query);

        // Handle scans which are "SCAN 0 MATCH -aaaa* COUNT 1000
        if (preg_match('/^(?<command>SCAN \d+ MATCH) (?<key>\S+) (?<values>.*)$/', $query, $matches)) {
            $command = $matches['command'] ?? '';
            $key     = $matches['key'] ?? '';
            $values  = $matches['values'] ?? '';
        } else {
            $_query  = explode(' ', $query);
            $command = (isset($_query[0])) ? $_query[0] : '';
            $key     = (isset($_query[1])) ? $_query[1] : '';
            $values  = (isset($_query[2])) ? mb_strimwidth(implode(' ', array_slice($_query, 2)), 0, 25, '[...]') : '';
        }

        return trim("{$command} {$key} {$values}");
    }

    /**
     * Build the report
     *
     * @param null $limit Limit/truncate the number of queries signatures, starting from the most time consuming
     *
     * @return void
     */
    static function buildReport($limit = null)
    {
        $queries =& static::$report_data['queries'];
        $totals  =& static::$report_data['totals'];

        // Compute queries data
        foreach (static::$log_entries as $_entry) {
            [$sample, $duration] = $_entry;
            $hash = static::hashQuery($sample);

            if (!isset($queries[$hash])) {
                $queries[$hash] = [
                    'sample'       => static::sampleQuery($sample),
                    'duration'     => 0,
                    'distribution' => [],
                ];
            }

            $query             =& $queries[$hash];
            $query['duration'] += $duration;

            $level = (int)floor(log10($duration) + .5);
            if (!isset($query['distribution'][$level])) {
                $query['distribution'][$level] = 0;
            }
            $query['distribution'][$level]++;

            // Update totals
            $totals['count']++;;
            $totals['duration'] += $duration;
        }

        // SORT_DESC with SORT_NUMERIC won't work so array is reversed in a second pass
        array_multisort($queries, SORT_ASC, SORT_NUMERIC, CMbArray::pluck($queries, 'duration'));
        $queries = array_values(array_reverse($queries));

        if ($limit) {
            $queries = array_slice($queries, 0, $limit);
        }
    }

    /**
     * Displays the report
     *
     * @param array $report_data Report data as input instead of current view report data
     *
     * @return void
     */
    static function displayReport($report_data = null)
    {
        $current_report_data = static::$report_data;

        if ($report_data) {
            static::$report_data = $report_data;
        }

        $queries =& static::$report_data["queries"];
        $totals  =& static::$report_data["totals"];

        // Get counts per query
        foreach ($queries as &$_query) {
            $_query["count"] = array_sum($_query["distribution"]);
        }

        // Report might be truncated versus all query log
        $report_totals = [
            "count"    => array_sum(CMbArray::pluck($queries, "count")),
            "duration" => array_sum(CMbArray::pluck($queries, "duration")),
        ];

        echo '<h2>NoSQL</h2>';

        if (($report_totals["count"] != $totals["count"])) {
            CAppUI::stepMessage(
                UI_MSG_WARNING,
                "Report is truncated to the %d most time consuming query signatures (%d%% of queries count, %d%% of queries duration).",
                count($queries),
                $report_totals["count"] * 100 / $totals["count"],
                $report_totals["duration"] * 100 / $totals["duration"]
            );
        }
        // Unset to prevent second foreach confusion
        unset($_query);

        // Print the report
        foreach ($queries as $_index => $_query) {
            $_dist = $_query["distribution"];
            ksort($_dist);
            $ticks = [
                "  1&micro; ",
                " 10&micro; ",
                "100&micro; ",
                "  1ms ",
                " 10ms ",
                "100ms ",
                "  1s ",
                " 10s ",
                "100s ",
            ];
            $lines = [];
            foreach ($_dist as $_level => $_count) {
                $line = $ticks[$_level];
                $max  = 100;
                while ($_count > $max) {
                    $line   .= str_pad("", $max, "#") . "\n      ";
                    $_count -= $max;
                }
                $line    .= str_pad("", $_count, "#");
                $lines[] = $line;
            }
            $distribution = "<pre>" . implode("\n", $lines) . "</pre>";

            CAppUI::stepMessage(
                UI_MSG_OK,
                "Query %d: was called %d times [%d%%] for %01.3fms [%d%%]",
                $_index + 1,
                $_query["count"],
                $_query["count"] * 100 / $totals["count"],
                $_query["duration"] / 1000,
                $_query["duration"] * 100 / $totals["duration"]
            );

            echo utf8_decode(CMbString::highlightCode('http', $_query["sample"], false, "white-space: pre-wrap;"));


            echo $distribution;
        }

        static::$report_data = $current_report_data;
    }
}
