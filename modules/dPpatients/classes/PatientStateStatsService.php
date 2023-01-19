<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use DateTimeImmutable;
use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbDT;
use Ox\Mediboard\System\Repositories\MergeLogDAO;

/**
 * Description
 */
class PatientStateStatsService implements IShortNameAutoloadable
{
    /**
     * Generate GraphPie for total distribution
     *
     * @return array
     * @throws Exception
     */
    public function generateStatsTotal(): array
    {
        $count_status   = $this->countPatientStatus();
        $count_status[] = [
            "total"  => (new CPatientLink())->countList(),
            "status" => "DPOT",
        ];

        $series = CPatientStateTools::createGraphPie($count_status);
        foreach ($series["datum"] as $_k => $_serie) {
            $series["datum"][$_k]["percent"] = ($series["count"] > 0)
                ? round($_serie["data"] / $series["count"] * 100)
                : 0;
        }

        return $series;
    }

    /**
     * Generate GraphBar per day
     *
     * @param string $merge_patient
     * @param int    $number_day
     * @param string $date_start
     * @param string $date_end
     *
     * @return array
     * @throws Exception
     */
    public function generateStatsPerDay(
        bool   $merge_patient,
        int    $number_day,
        string $date_start,
        string $date_end
    ): array {
        $values = [];

        if ($merge_patient) {
            $results = CPatientStateTools::getPatientMergeByDate(
                new MergeLogDAO(),
                new DateTimeImmutable($date_start),
                new DateTimeImmutable($date_end)
            );

            for ($i = $number_day; $i >= 0; $i--) {
                $values["merged"][CMbDT::date("-$i DAYS", $date_end)] = 0;
            }

            foreach ($results as $_result) {
                $count_patients                     = count(explode("-", $_result['ids']));
                $values["merged"][$_result["date"]] = ['count' => $count_patients, 'ids' => $_result['ids']];
            }
        } else {
            $results = CPatientStateTools::getPatientStateByDate($date_start, $date_end);

            foreach ($results as $_result) {
                for ($i = $number_day; $i >= 0; $i--) {
                    $date = CMbDT::date("-$i DAYS", $date_end);
                    $values[$_result["state"]][CMbDT::date("-$i DAYS", $date_end)] =
                        ($date == $_result["date"])
                            ? $_result["total"]
                            : 0;
                }
            }
        }

        return CPatientStateTools::createGraphBar($values, $number_day, $merge_patient);
    }

    /**
     * Count the number of patients per status
     *
     * @return array|null
     * @throws Exception
     */
    private function countPatientStatus(): ?array
    {
        $where = [
            "status" => "IS NOT NULL",
        ];

        return (new CPatient())->countMultipleList($where, null, "status", null, ["status"]);
    }
}
