<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

use Ox\Core\CMbDT;

/**
 * Description
 */
class LogDiff
{
    /** @var DataAuditTargetLogs */
    private $first_logs;

    /** @var DataAuditTargetLogs */
    private $second_logs;

    /** @var array */
    private $diff = [];

    /** @var array */
    private $report = [];

    /**
     * LogDiff constructor.
     *
     * @param DataAuditTargetLogs $first_logs
     * @param DataAuditTargetLogs $second_logs
     */
    public function __construct(DataAuditTargetLogs $first_logs, DataAuditTargetLogs $second_logs)
    {
        $this->first_logs  = $first_logs;
        $this->second_logs = $second_logs;

        $this->computeDiff();
    }

    /**
     * @return DataAuditTargetLogs
     */
    public function getFirstLogs(): DataAuditTargetLogs
    {
        return $this->first_logs;
    }

    /**
     * @return DataAuditTargetLogs
     */
    public function getSecondLogs(): DataAuditTargetLogs
    {
        return $this->second_logs;
    }

    /**
     * Compute the diff. between the two hosts
     *
     * @return void
     */
    private function computeDiff()
    {
        $diff = [
            'user_log'    => [
                'presency'  => [
                    'first_but_not_second' => [],
                    'second_but_not_first' => [],
                ],
                'integrity' => [],
            ],
            'user_action' => [
                'presency'  => [
                    'first_but_not_second' => [],
                    'second_but_not_first' => [],
                ],
                'integrity' => [],
            ],
        ];

        $diff['user_log']['presency']['first_but_not_second'] = array_diff(
            $this->first_logs->getUserLogsIDs(),
            $this->second_logs->getUserLogsIDs()
        );

        $diff['user_log']['presency']['second_but_not_first'] = array_diff(
            $this->second_logs->getUserLogsIDs(),
            $this->first_logs->getUserLogsIDs()
        );

        $diff['user_action']['presency']['first_but_not_second'] = array_diff(
            $this->first_logs->getUserActionsIDs(),
            $this->second_logs->getUserActionsIDs()
        );

        $diff['user_action']['presency']['second_but_not_first'] = array_diff(
            $this->second_logs->getUserActionsIDs(),
            $this->first_logs->getUserActionsIDs()
        );

        $this->checkLogIntegrityFromTo($diff, $this->first_logs, $this->second_logs);
        $this->checkLogIntegrityFromTo($diff, $this->second_logs, $this->first_logs);

        $this->diff = $diff;

        $this->computeErrors();
    }

    /**
     * @param array               $diff
     * @param DataAuditTargetLogs $first_logs
     * @param DataAuditTargetLogs $second_logs
     *
     * @return void
     */
    private function checkLogIntegrityFromTo(
        array &$diff,
        DataAuditTargetLogs $first_logs,
        DataAuditTargetLogs $second_logs
    ) {
        foreach ($first_logs->getUserLogsIDs() as $_log_id) {
            // Log already treated (probably in first_logs and so we are in second logs)
            if (isset($diff['user_log']['integrity'][$_log_id])) {
                continue;
            }

            // If log exists on second logs
            if ($_second_log = $second_logs->getUserLog($_log_id)) {
                $_first_log = $first_logs->getUserLog($_log_id);

                if ($_first_log !== $_second_log) {
                    $diff['user_log']['integrity'][$_log_id] = null;
                }
            }
        }

        foreach ($first_logs->getUserActionsIDs() as $_log_id) {
            // Log already treated (probably in first_logs and so we are in second logs)
            if (isset($diff['user_action']['integrity'][$_log_id])) {
                continue;
            }

            // If log exists on second logs
            if ($_second_log = $second_logs->getUserAction($_log_id)) {
                $_first_log = $first_logs->getUserAction($_log_id);

                if ($_first_log !== $_second_log) {
                    $diff['user_action']['integrity'][$_log_id] = null;
                }
            }
        }
    }

    /**
     * Compute the diff. errors between the two hosts
     *
     * @return void
     */
    private function computeErrors()
    {
        $report = [
            'days'  => [],
            'sigma' => [
                'days'    => [],
                'classes' => [],
            ],
        ];

        $this->groupLogPresencyByDay($report, $this->first_logs, 'user_log', true);
        $this->groupLogPresencyByDay($report, $this->second_logs, 'user_log', false);
        $this->groupLogPresencyByDay($report, $this->first_logs, 'user_action', true);
        $this->groupLogPresencyByDay($report, $this->second_logs, 'user_action', false);

        $this->computeIntegrityReportByDay($report, 'user_log');
        $this->computeIntegrityReportByDay($report, 'user_action');

        ksort($report['days']);

        foreach ($report['days'] as $_day => &$_times) {
            ksort($_times);
        }

        $this->computeSigmaByClass($report, $this->first_logs, 'user_log', true);
        $this->computeSigmaByClass($report, $this->second_logs, 'user_log', false);
        $this->computeSigmaByClass($report, $this->first_logs, 'user_action', true);
        $this->computeSigmaByClass($report, $this->second_logs, 'user_action', false);

        ksort($report['sigma']['days']);

        $this->report = $report;
    }

    /**
     * @param array               $report
     * @param DataAuditTargetLogs $logs
     * @param string              $type
     * @param bool                $first
     *
     * @return void
     */
    private function computeSigmaByClass(array &$report, DataAuditTargetLogs $logs, string $type, bool $first)
    {
        if ($type !== 'user_log' && $type !== 'user_action') {
            return;
        }

        $presency_key = 'first_but_not_second';
        $target_key   = 'second';

        if (!$first) {
            $presency_key = 'second_but_not_first';
            $target_key   = 'first';
        }

        foreach ($this->diff[$type]['presency'][$presency_key] as $_log_id) {
            $_class = $logs->getLogObjectClassFromType($type, $_log_id);
            $_date  = $logs->getLogDateFromType($type, $_log_id);
            $_day   = CMbDT::format($_date, CMbDT::ISO_DATE);

            if (!isset($report['sigma']['classes'][$_class])) {
                $report['sigma']['classes'][$_class] = [
                    'first'  => 0,
                    'second' => 0,
                ];
            }

            if (!isset($report['sigma']['days'][$_day])) {
                $report['sigma']['days'][$_day] = 0;
            }

            $report['sigma']['days'][$_day]++;

            $report['sigma']['classes'][$_class][$target_key]++;
        }

        foreach ($this->diff[$type]['integrity'] as $_log_id => $_null) {
            $_class = $logs->getLogObjectClassFromType($type, $_log_id);
            $_date  = $logs->getLogDateFromType($type, $_log_id);
            $_day   = CMbDT::format($_date, CMbDT::ISO_DATE);

            if (!isset($report['sigma']['classes'][$_class])) {
                $report['sigma']['classes'][$_class] = [
                    'first'  => 0,
                    'second' => 0,
                ];
            }

            if (!isset($report['sigma']['days'][$_day])) {
                $report['sigma']['days'][$_day] = 0;
            }

            $report['sigma']['days'][$_day]++;

            $report['sigma']['classes'][$_class][$target_key]++;
        }
    }

    /**
     * @param array               $report
     * @param DataAuditTargetLogs $logs
     * @param string              $type
     * @param bool                $first
     *
     * @return void
     */
    private function groupLogPresencyByDay(array &$report, DataAuditTargetLogs $logs, string $type, bool $first)
    {
        if ($type !== 'user_log' && $type !== 'user_action') {
            return;
        }

        $presency_key = 'first_but_not_second';
        $target_key   = 'first';

        if (!$first) {
            $presency_key = 'second_but_not_first';
            $target_key   = 'second';
        }

        foreach ($this->diff[$type]['presency'][$presency_key] as $_log_id) {
            $_log_date = $logs->getLogDateFromType($type, $_log_id);

            $_log_day  = CMbDT::format($_log_date, CMbDT::ISO_DATE);
            $_log_time = CMbDT::format($_log_date, '%H');

            if (!isset($report['days'][$_log_day])) {
                $report['days'][$_log_day] = [];
            }

            if (!isset($report['days'][$_log_day][$_log_time])) {
                $report['days'][$_log_day][$_log_time] = [
                    'user_log'    => [
                        'first'  => [],
                        'second' => [],
                        'diff'   => [],
                    ],
                    'user_action' => [
                        'first'  => [],
                        'second' => [],
                        'diff'   => [],
                    ],
                ];
            }

            $report['days'][$_log_day][$_log_time][$type][$target_key][] = $_log_id;
        }
    }

    /**
     * @param array  $report
     * @param string $type
     *
     * @return void
     */
    private function computeIntegrityReportByDay(array &$report, string $type)
    {
        if ($type !== 'user_log' && $type !== 'user_action') {
            return;
        }

        foreach ($this->diff[$type]['integrity'] as $_log_id => $_null) {
            $_log_date = $this->first_logs->getLogDateFromType($type, $_log_id);

            $_log_day  = CMbDT::format($_log_date, CMbDT::ISO_DATE);
            $_log_time = CMbDT::format($_log_date, '%H');

            if (!isset($report['days'][$_log_day])) {
                $report['days'][$_log_day] = [];
            }

            if (!isset($report['days'][$_log_day][$_log_time])) {
                $report['days'][$_log_day][$_log_time] = [
                    'user_log'    => [
                        'first'  => [],
                        'second' => [],
                        'diff'   => [],
                    ],
                    'user_action' => [
                        'first'  => [],
                        'second' => [],
                        'diff'   => [],
                    ],
                ];
            }

            $report['days'][$_log_day][$_log_time][$type]['diff'][] = $_log_id;
        }
    }

    /**
     * @param string $day
     *
     * @return array
     */
    public function getDiffByHourForDay(string $day): array
    {
        return ($this->report['days'][$day]) ?? [];
    }

    /**
     * @return array
     */
    public function getDiffDays(): array
    {
        return array_keys($this->report['days']);
    }

    /**
     * @param string $day
     *
     * @return int
     */
    public function getSigmaForDay(string $day): int
    {
        return ($this->report['sigma']['days'][$day]) ?? 0;
    }

    public function getSigmaByClass(): array
    {
        return $this->report['sigma']['classes'];
    }
}
