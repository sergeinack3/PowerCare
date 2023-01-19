<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;

class CLongRequestLogGraph implements IShortNameAutoloadable
{
    static function getDurationSeries(
        &$logs,
        &$module_actions,
        $threshold,
        $module = null,
        $from_date = null,
        $to_date = null
    ) {
        $series = [
            'title'   => ($module) ? CAppUI::tr("module-{$module}-court") . "<br />" . CAppUI::tr(
                    'CLongRequestLog-duration'
                ) : CAppUI::tr('CLongRequestLog-duration'),
            'dates'   => ($from_date && $to_date) ? "$from_date - $to_date" : null,
            'data'    => [],
            'total'   => 0,
            'options' => [
                'series' => [
                    'pie' => [
                        'show'  => true,
                        'label' => [
                            'show'      => true,
                            'threshold' => $threshold,
                        ],
                    ],
                ],
                'legend' => [
                    'show' => true,
                ],
                'grid'   => [
                    'hoverable' => true,
                ],
            ],
        ];

        $total = 0;
        foreach ($logs as $_log) {
            $total += $_log['duration'];

            $_serie_label = 'N/A';
            if (isset($_log['module_action_id'])) {
                if ($module) {
                    $_serie_label = "{$module_actions[$_log['module_action_id']]->action}";
                } else {
                    $_serie_label = CAppUI::tr(
                            "module-{$module_actions[$_log['module_action_id']]->module}-court"
                        ) . " - {$module_actions[$_log['module_action_id']]->action}";
                }
            }

            $series['data'][] = [
                'label' => $_serie_label,
                'data'  => round($_log['duration'], 2),
            ];
        }

        $series['total'] = round($total, 2);

        // Convert data to percent
        foreach ($series['data'] as $key => $serie) {
            $series['data'][$key]['data'] = round(($serie['data'] / $series['total']) * 100, 2);
        }

        return $series;
    }

    static function getTotalDurationSeries(&$logs, $threshold)
    {
        $series = [
            'title'   => CAppUI::tr('common-Total'),
            'dates'   => null,
            'data'    => [],
            'total'   => 0,
            'options' => [
                'series' => [
                    'pie' => [
                        'show'  => true,
                        'label' => [
                            'show'      => true,
                            'threshold' => $threshold,
                        ],
                    ],
                ],
                'legend' => [
                    'show' => true,
                ],
                'grid'   => [
                    'hoverable' => true,
                ],
            ],
        ];

        $total = 0;
        foreach ($logs as $_module => $_duration) {
            if (!$_duration) {
                continue;
            }

            $total += $_duration;

            $series['data'][] = [
                'label' => ($_module != 'null') ? CAppUI::tr("module-{$_module}-court") : 'N/A',
                'data'  => round($_duration, 2),
            ];
        }

        $series['total'] = round($total, 2);

        // Convert data to percent
        foreach ($series['data'] as $key => $serie) {
            $series['data'][$key]['data'] = round(($serie['data'] / $series['total']) * 100, 2);
        }

        return $series;
    }

    static function getDurationByModule(CLongRequestLog $log, $where, $limit = null)
    {
        // Module name according to module_action_id
        $modules         = [];
        $stats_by_module = [];

        /** @var CLongRequestLog[] $total_logs */
        $total_logs = $log->loadList($where);

        // Getting all logs for global ranking (by module)
        if ($total_logs) {
            foreach ($total_logs as $_log) {
                // No module, put it into 'null' key
                if (!$_log->module_action_id) {
                    if (!isset($stats_by_module['null'])) {
                        $stats_by_module['null'] = 0;
                    }

                    $stats_by_module['null'] += $_log->duration;
                    continue;
                }

                // Module set
                $_module_action = new CModuleAction();
                $_module_action->load($_log->module_action_id);

                if (!isset($modules[$_module_action->_id])) {
                    $modules[$_module_action->_id] = $_module_action->module;
                }
            }

            // Gets durations by module
            $stats_by_module = array_merge(array_fill_keys(array_values(array_unique($modules)), 0), $stats_by_module);
            foreach ($total_logs as $_log) {
                // Only logs where module is set, cause 'null' already set
                if ($_log->module_action_id) {
                    $stats_by_module[$modules[$_log->module_action_id]] += $_log->duration;
                }
            }

            // Sorting by duration
            array_multisort($stats_by_module, SORT_DESC);

            // Only $limit first items
            if ($limit) {
                $stats_by_module = array_slice($stats_by_module, 0, $limit);
            }
        }

        return $stats_by_module;
    }
}
