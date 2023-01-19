<?php

/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbPath;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkRead();

$root_dir = CAppUI::conf("importTools export root_path", CGroups::loadCurrent()->_guid) ?: CAppUI::conf(
    "importTools import root_path",
    "global"
);

if (!$root_dir) {
    CAppUI::commonError("importTools-root_path.none");
}

$paths    = [
    'root'     => $root_dir,
    'sub_dirs' => [],
];
$iterator = new DirectoryIterator($root_dir);

$all_date_min           = "";
$all_date_max           = "";
$all_patients           = 0;
$all_export_patients    = 0;
$all_export_duration    = 0;
$all_size               = 0;
$all_import_patients    = 0;
$all_import_duration    = 0;
$all_export_last_update = "";
$all_import_last_update = "";

while ($dir = $iterator->getFilename()) {
    if ($iterator->isDot() || $iterator->isFile()) {
        $iterator->next();
        continue;
    }

    $paths['sub_dirs'][$dir] = [
        "total" => [],
        "infos" => [],
    ];

    $borne_date_min     = "";
    $borne_date_max     = "";
    $total_patients     = 0;
    $export_patients    = 0;
    $export_duration    = 0;
    $total_size         = 0;
    $import_patients    = 0;
    $import_duration    = 0;
    $export_last_update = "";
    $import_last_update = "";

    try {
        $sub_iterator = new DirectoryIterator($iterator->getPathname());
    } catch (Throwable $e) {
        continue;
    }

    while ($sub_dir = $sub_iterator->getFilename()) {
        if ($sub_iterator->isDot() || $sub_iterator->isFile()) {
            $sub_iterator->next();
            continue;
        }

        preg_match("/(\d{4}\-\d{2}\-\d{2})_(\d{4}\-\d{2}\-\d{2})/", $sub_dir, $dates);
        $date_min = (isset($dates[1])) ? CMbDT::date($dates[1]) : "";
        $date_max = (isset($dates[2])) ? CMbDT::date($dates[2]) : "";

        if (!$borne_date_min || ($date_min && $borne_date_min > $date_min)) {
            $borne_date_min = $date_min;
        }

        if (!$borne_date_max || ($date_max && $borne_date_max < $date_max)) {
            $borne_date_max = $date_max;
        }

        $path   = $sub_iterator->getPathname();
        $export = [];
        $import = [];
        if (file_exists($path . '/import.status')) {
            $import             = json_decode(CMbPath::tailCustom($path . '/import.status'), true);
            $import_patients    += ($import['patient_current'] > $import['patient_total'])
                ? $import['patient_total'] : $import['patient_current'];
            $import_duration    += $import['duration'];
            $import_last_update =
                ($import['last_update'] > $import_last_update) ? $import['last_update'] : $import_last_update;
        }

        if (file_exists($path . '/export.status')) {
            $export             = json_decode(CMbPath::tailCustom($path . '/export.status'), true);
            $total_patients     += $export['patient_total'];
            $export_patients    += ($export['patient_current'] > $export['patient_total'])
                ? $export['patient_total'] : $export['patient_current'];
            $export_duration    += $export['duration'];
            $total_size         += $export['size'];
            $export_last_update =
                ($export['last_update'] > $export_last_update) ? $export['last_update'] : $export_last_update;

            if ($import) {
                $import['size'] = $export['size'];
            }
        }

        $paths['sub_dirs'][$dir]['infos'][$sub_dir] = [
            "date_min" => $date_min,
            "date_max" => $date_max,
            "export"   => $export,
            "import"   => $import,
        ];

        $sub_iterator->next();
    }

    $paths['sub_dirs'][$dir]['total'] = [
        "date_min"           => $borne_date_min,
        "date_max"           => $borne_date_max,
        "total_patients"     => $total_patients,
        "export_patients"    => $export_patients,
        "export_duration"    => round($export_duration / 60),
        "total_size"         => $total_size,
        "import_patients"    => $import_patients,
        "import_duration"    => round($import_duration / 60),
        "import_last_update" => $import_last_update,
        "export_last_update" => $export_last_update,
    ];

    if (!$all_date_min || ($borne_date_min && $borne_date_min < $all_date_min)) {
        $all_date_min = $borne_date_min;
    }

    if (!$all_date_max || ($borne_date_max && $borne_date_max > $all_date_max)) {
        $all_date_max = $borne_date_max;
    }

    if (!$all_export_last_update || ($export_last_update && $export_last_update > $all_export_last_update)) {
        $all_export_last_update = $export_last_update;
    }

    if (!$all_import_last_update || ($import_last_update && $import_last_update > $all_import_last_update)) {
        $all_import_last_update = $import_last_update;
    }

    $all_patients        += $total_patients;
    $all_size            += $total_size;
    $all_export_duration += $export_duration;
    $all_export_patients += $export_patients;
    $all_import_duration += $import_duration;
    $all_import_patients += $import_patients;

    $iterator->next();
}

$paths['total'] = [
    "date_min"           => $all_date_min,
    "date_max"           => $all_date_max,
    "total_patients"     => $all_patients,
    "total_size"         => $all_size,
    "export_duration"    => round($all_export_duration / 60),
    "export_patients"    => $all_export_patients,
    "import_duration"    => round($all_import_duration / 60),
    "import_patients"    => $all_import_patients,
    "import_last_update" => $all_import_last_update,
    "export_last_update" => $all_export_last_update,
];

$smarty = new CSmartyDP();
$smarty->assign("paths", $paths);
$smarty->display("vw_migration");
