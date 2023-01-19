<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use DateTimeInterface;
use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\System\Repositories\MergeLogDAO;

/**
 * Tools for state patient
 */
class CPatientStateTools implements IShortNameAutoloadable
{
    /** @var string[] */
    public static $color = [
        "VIDE"   => "#FFFFEE",
        "PROV"   => "#800000",
        "VALI"   => "#FF7611",
        "DPOT"   => "#9999CC",
        "ANOM"   => "#FF66FF",
        "CACH"   => "#B2B2B3",
        "merged" => "#EEA072",
        "RECUP"  => "#008",
        "QUAL"   => "#080",
        "DOUT"   => "#FF5151",
        "FICTI"  => "#B70000",
    ];

    /**
     * Set the PROV status for the patient stateless
     *
     * @param String $state patient state
     *
     * @return int
     * @throws \Exception
     */
    static function createStatus($state = "PROV")
    {
        $ds = CSQLDataSource::get("std");

        $ds->exec("UPDATE `patients` SET `status`='$state' WHERE `status` IS NULL;");

        return $ds->affectedRows();
    }

    /**
     * Get the number patient stateless
     *
     * @return int
     * @throws \Exception
     */
    static function verifyStatus()
    {
        $patient = new CPatient();
        $where   = [
            "status" => "IS NULL",
        ];

        return $patient->countList($where);
    }

    /**
     * Get the patient by date
     *
     * @param string $before before date
     * @param string $now    now date
     *
     * @return array
     * @throws \Exception
     */
    static function getPatientStateByDate($before, $now)
    {
        $ds      = CSQLDataSource::get("std");
        $request = new CRequest(false);
        $request->addSelect("DATE(datetime) AS 'date', state, count(*) as 'total'");
        $request->addTable("patient_state");
        $request->addWhere("DATE(datetime) BETWEEN '$before' AND '$now'");
        $request->addWhere("state = 'DOUT' OR state = 'FICTI'");
        $request->addGroup("DAY(datetime), state");

        return $ds->loadList($request->makeSelect());
    }

    /**
     * Get the patients merged by date.
     *
     * @param MergeLogDAO       $dao
     * @param DateTimeInterface $before
     * @param DateTimeInterface $now
     *
     * @return array
     * @throws Exception
     */
    public static function getPatientMergeByDate(
        MergeLogDAO       $dao,
        DateTimeInterface $before,
        DateTimeInterface $now
    ): array {
        $dao->reset();

        $dao
            ->where('date_end_merge', 'BETWEEN', [$before->format('Y-m-d 00:00:00'), $now->format('Y-m-d 23:59:59')])
            ->where('object_class', '=', 'CPatient');

        $logs_result = [];
        foreach ($dao->find() as $_merge_log) {
            $_date = CMbDT::date($_merge_log->date_end_merge);

            if (!isset($logs_result[$_date])) {
                $logs_result[$_date] = [
                    'date'  => $_date,
                    'total' => 0,
                    'ids'   => [],
                ];
            }

            // If base object has been merged more that once, do not count it
            if (!in_array($_merge_log->base_object_id, $logs_result[$_date]['ids'])) {
                $logs_result[$_date]['total']++;
                $logs_result[$_date]['ids'][] = $_merge_log->base_object_id;
            }
        }

        foreach ($logs_result as &$_result) {
            $_result['ids'] = implode('-', $_result['ids']);
        }

        usort(
            $logs_result,
            function ($a, $b): int {
                return $a['date'] <=> $b['date'];
            }
        );

        return $logs_result;
    }

    /**
     * Create the pie graph
     *
     * @param String[] $count_status number patient by status
     *
     * @return array
     */
    static function createGraphPie($count_status)
    {
        $series = [
            "title"   => "CPatientState.proportion",
            "count"   => null,
            "unit"    => lcfirst(CAppUI::tr("CPatient|pl")),
            "datum"   => [],
            "options" => null,
        ];

        $total = 0;
        foreach ($count_status as $_count) {
            $count             = $_count["total"];
            $status            = $_count["status"];
            $total             += $count;
            $series["datum"][] = [
                "label" => CAppUI::tr("CPatient.status.$status"),
                "data"  => $count,
                "color" => isset(self::$color[$status]) ? self::$color[$status] : self::$color['VIDE'],
            ];
        }

        $series["count"]   = $total;
        $series["options"] = [
            "series" => [
                "unit" => lcfirst(CAppUI::tr("CPatient|pl")),
                "pie"  => [
                    "innerRadius" => 0.5,
                    "show"        => true,
                    "label"       => [
                        "show"      => true,
                        "threshold" => 0.02,
                    ],
                ],
            ],
            "legend" => [
                "show" => false,
            ],
            "grid"   => [
                "hoverable" => true,
            ],
        ];

        return $series;
    }

    /**
     * Create the bar graph
     *
     * @param array $values        Number patient status by date
     * @param int   $interval      Interval between two date
     * @param bool  $merge_patient Merge patient graph
     *
     * @return array
     */
    static function createGraphBar(array $values, int $interval, bool $merge_patient)
    {
        $series2 = [
            "title"   => ($merge_patient)
                ? "CPatientState.dayproportion.merged"
                : "CPatientState.dayproportion.identity",
            "unit"    => ($merge_patient)
                ? lcfirst(CAppUI::tr("CPatientState._patient|pl"))
                : lcfirst(CAppUI::tr("CPatientState._entry|pl")),
            "count"   => 0,
            "datum"   => null,
            "options" => [
                "xaxis"  => [
                    "position" => "bottom",
                    "min"      => 0,
                    "max"      => $interval + 1,
                    "ticks"    => [],
                ],
                "yaxes"  => [
                    "0" => [
                        "position"     => "left",
                        "tickDecimals" => false,
                    ],
                    "1" => [
                        "position" => "right",
                    ],
                ],
                "legend" => [
                    "show" => true,
                ],
                "series" => [
                    "stack" => true,
                ],
                "grid"   => [
                    "hoverable" => true,
                ],
            ],
        ];

        if (array_key_exists('merged', $values)) {
            $series2['options']['grid']['clickable'] = true;
        }

        $total = 0;
        $datum = [];
        foreach ($values as $_status => $_result) {
            $abscisse = -1;
            $data     = [];

            foreach ($_result as $_day => $_count) {
                // When merged patients searched, value if count + patient IDs
                if (is_array($_count) && $_status == 'merged') {
                    $_ids   = $_count['ids'];
                    $_count = $_count['count'];
                } else {
                    $_ids = null;
                }

                $abscisse                               += 1;
                $series2["options"]["xaxis"]["ticks"][] = [$abscisse + 0.5, CMbDT::transform(null, $_day, "%d/%m")];

                $data[] = [
                    $abscisse,
                    $_count,
                    'day' => CMbDT::transform(null, $_day, CAppUI::conf("date")),
                    'ids' => $_ids,
                ];

                $total += $_count;
            }

            $datum[] = [
                "data"  => $data,
                "yaxis" => 1,
                "label" => CAppUI::tr("CPatient.status." . $_status),
                "color" => self::$color[$_status],
                "unit"  => ($merge_patient)
                    ? lcfirst(CAppUI::tr("CPatientState._patient|pl"))
                    : lcfirst(CAppUI::tr("CPatientState._entry|pl")),
                "bars"  => [
                    "show" => true,
                ],
            ];
        }

        $series2["datum"] = $datum;
        $series2['count'] = $total;

        return $series2;
    }
}
