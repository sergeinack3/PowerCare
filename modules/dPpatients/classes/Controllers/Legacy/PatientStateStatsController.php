<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Controllers\Legacy;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\PatientStateStatsService;

/**
 * Patient Stats controller
 */
class PatientStateStatsController extends CLegacyController
{
    /**
     * Show statistics
     *
     * @return void
     */
    public function viewStats(): void
    {
        $this->renderSmarty(
            'patient_state/inc_tabs_stats'
        );
    }

    /**
     * Access to Patient State statistics
     *
     * @return void
     * @throws Exception
     */
    public function viewStatsPatientState(): void
    {
        $this->checkPermRead();

        if (!CAppUI::pref("allowed_modify_identity_status")) {
            CAppUI::accessDenied();
        }

        $merge_patient = CView::get("_merge_patient", "bool default|0");
        $number_day    = CView::get("_number_day", "num default|8");
        $date_end      = CView::get("_date_end", "date default|now");

        CView::checkin();

        $this->renderSmarty(
            "patient_state/inc_stats_patient_state",
            [
                "_number_day"    => $number_day,
                "_date_end"      => $date_end,
                "_merge_patient" => $merge_patient
            ]
        );
    }

    /**
     * Show statistics patient state
     *
     * @return void
     * @throws Exception
     */
    public function loadListStatsPatientState(): void
    {
        $this->checkPermRead();

        if (!CAppUI::pref("allowed_modify_identity_status")) {
            CAppUI::accessDenied();
        }

        $merge_patient = CView::get("_merge_patient", "bool");
        $number_day    = CView::get("_number_day", "num");
        $date_end      = CView::get("_date_end", "date");

        CView::checkin();

        if ($number_day > 31) {
            $number_day = 31;
        } elseif ($number_day < 0) {
            $number_day = 0;
        }

        $patient    = new CPatient();
        $date_start = CMbDT::date("-$number_day DAYS", $date_end);

        $patient_state_stats = new PatientStateStatsService();

        $this->renderSmarty(
            'patient_state/inc_list_stats_patient_state',
            [
                "state_graph"    => $patient_state_stats->generateStatsTotal(),
                "identity_graph" => $patient_state_stats->generateStatsPerDay(
                    $merge_patient,
                    $number_day,
                    $date_start,
                    $date_end
                ),
                "total_patient"  => $patient->countList(),
                "_number_day"    => $number_day,
                "_date_end"      => $date_end,
                "_merge_patient" => $merge_patient,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function exportStatsPatientState(): void
    {
        $this->checkPermRead();

        if (!CAppUI::pref("allowed_modify_identity_status")) {
            CAppUI::accessDenied();
        }

        $number_day    = CView::get("_number_day", "num default|8");
        $now           = CView::get("_date_end", "date default|now");
        $merge_patient = CView::get("_merge_patient", "bool");

        CView::checkin();

        $file                = new CCSVFile();
        $patient_state_stats = new PatientStateStatsService();
        $before              = CMbDT::date("-$number_day DAY", $now);

        // Generate state graph export
        $state_graph = $patient_state_stats->generateStatsTotal();
        $labels      = [];
        $values      = [];

        foreach ($state_graph["datum"] as $_serie) {
            $labels[] = $_serie["label"];
            $values[] = $_serie["data"];
        }

        $file->writeLine($labels);
        $file->writeLine($values);
        $file->writeLine([]);

        // Generate identity graph export
        $identity_graph = $patient_state_stats->generateStatsPerDay($merge_patient, $number_day, $before, $now);
        $lines          = [];

        foreach ($identity_graph["datum"] as $_serie) {
            foreach ($_serie["data"] as $_data) {
                $lines[$_data["day"]][$_serie["label"]] = $_data[1];
            }
        }

        if ($merge_patient) {
            $file->writeLine(
                [
                    CAppUI::tr("common-day"),
                    CAppUI::tr("CPatientState-_merge_patient|pl"),
                ]
            );
        } else {
            $file->writeLine(
                [
                    CAppUI::tr("common-day"),
                    CAppUI::tr("CPatientState.state.DOUT"),
                    CAppUI::tr("CPatientState.state.FICTI")
                ]
            );
        }

        foreach ($lines as $_date => $_line) {
            $line = array_merge([$_date], array_values($_line));
            $file->writeLine($line);
        }

        $file->stream("export_stats_patients_" . CMbDT::transform("", CMbDT::date(), "%d_%m_%Y"));
    }
}
