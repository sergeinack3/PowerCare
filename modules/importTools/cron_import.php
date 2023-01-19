<?php

/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbPath;
use Ox\Core\Module\CModule;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatientXMLImport;
use Ox\Mediboard\System\CConfiguration;

CCanDo::checkAdmin();

$import_dir                = CView::get('root_path', 'str default|' . CAppUI::gconf("importTools import root_path"));
$date_min                  = CView::get('date_min', 'date default|' . CAppUI::gconf("importTools import date_min"));
$date_max                  = CView::get('date_max', 'date default|' . CAppUI::gconf("importTools import date_max"));
$tag                       = CView::get('tag', 'str default|' . CAppUI::gconf("importTools import tag"));
$ipp_tag                   = CView::get('ipp_tag', 'str');
$no_update_patients_exists = CView::get("no_update_patients_exists", "str default|0");
$import_presc              = CView::get("import_presc", "str default|0");
$exclude_duplicate         = CView::get("exclude_duplicate", "str default|0");
$additional_pat_matching   = CView::get("additionnal_pat_matching", 'str');
$updates                   = CView::get("update", 'str default|0');
$forced_step               = CView::get("step", "num");
$patient_id                = CView::get("patient_id", "num");
$ignore_classes            = CView::get('ignore_classes', 'str');

CView::checkin();

if (!CAppUI::gconf('importTools import actif')) {
    CApp::rip();
}

if (!is_dir($import_dir)) {
    if ($msg = CConfiguration::setConfig("importTools import actif", 0, CGroups::loadCurrent())) {
        CApp::log($msg);
    }

    CApp::log(CAppUI::tr('importTools-import-root_path is not a directory'));
    CApp::rip();
}

if (!$tag) {
    CApp::log(CAppUI::tr('importTools-import-tag is mandatory'));
    CApp::rip();
}

if (!$ipp_tag) {
    $ipp_tag = CAppUI::gconf("importTools import source_ipp_tag");
}

CApp::disableCacheAndHandlers();

$start_time = microtime(true);

// Set the subdirectory to use to get infos from export.status and to store export
$sub_dir = "all";
if ($date_min && $date_max) {
    $sub_dir = "{$date_min}_{$date_max}";
} elseif ($date_min) {
    $sub_dir = "after-{$date_min}";
} elseif ($date_max) {
    $sub_dir = "before-{$date_max}";
}

$import_dir = rtrim($import_dir, "\\/") . "/{$tag}/{$sub_dir}/";

if (!is_dir($import_dir)) {
    CApp::log(CAppUI::tr('importTools-import-directory %s does not exists', $import_dir));
    CApp::rip();
}

$imp_file = $import_dir . 'import.status';
if (file_exists($imp_file)) {
    $last_status   = json_decode(CMbPath::tailCustom($imp_file), true);
    $start         = (isset($last_status['patient_current'])) ? $last_status['patient_current'] : 0;
    $step          = (isset($last_status['patient_count'])) ? $last_status['patient_count'] : 1;
    $last_duration = (isset($last_status['last_duration'])) ? $last_status['last_duration'] : 0;
    $duration      = (isset($last_status['duration'])) ? $last_status['duration'] : 0;
    $duration      = (isset($last_status['duration'])) ? $last_status['duration'] : 0;

    $step = ($last_duration < 30) ? $step * 2 : (($last_duration > 59) ? round($step / 2) : $step);
} else {
    $start    = 0;
    $step     = 1;
    $duration = 0;
}

$step = $forced_step ?: $step;

$log_file = rtrim($import_dir, "\\/") . '/import.log';

if (!is_file($log_file)) {
    touch($log_file);
}

if (!CModule::getActive("dPprescription") || !$import_presc) {
    CPatientXMLImport::$_ignored_classes = array_merge(
        CPatientXMLImport::$_ignored_classes,
        CPatientXMLImport::$_prescription_classes
    );
}

if ($ignore_classes) {
    $ignore_classes                      = explode('|', $ignore_classes);
    CPatientXMLImport::$_ignored_classes = array_merge(CPatientXMLImport::$_ignored_classes, $ignore_classes);
}

$options = [
    "log_file"                  => $log_file,
    "date_min"                  => $date_min,
    "date_max"                  => $date_max,
    "link_file_to_op"           => false,
    "correct_file"              => false,
    "uf_replace"                => false,
    "no_update_patients_exists" => $no_update_patients_exists,
    "ipp_tag"                   => $ipp_tag,
    "exclude_duplicate"         => $exclude_duplicate,
    "additionnal_pat_matching"  => $additional_pat_matching ? explode("|", $additional_pat_matching) : [],
];

if ($patient_id) {
    $xmlfile = rtrim($import_dir, "\\/") . "/CPatient-{$patient_id}/export.xml";
    if (file_exists($xmlfile)) {
        try {
            $importer = new CPatientXMLImport($xmlfile);
            $importer->setUpdateData((bool)$updates, (bool)$updates);
            $importer->setDirectory(rtrim($import_dir, "\\/") . "/CPatient-{$patient_id}/");

            $importer->import([], $options);
        } catch (Exception $e) {
            CApp::log($e->getMessage());
        }
    }
} else {
    $iterator   = new DirectoryIterator($import_dir);
    $count_dirs = 0;

    $i           = 0;
    $error_count = 0;
    foreach ($iterator as $_fileinfo) {
        if ($_fileinfo->isDot()) {
            continue;
        }

        if ($_fileinfo->isDir() && strpos($_fileinfo->getFilename(), "CPatient-") === 0) {
            $i++;
            if ($i <= $start) {
                continue;
            }

            if ($i > $start + $step) {
                break;
            }

            $count_dirs++;

            $xmlfile = $_fileinfo->getRealPath() . "/export.xml";
            if (file_exists($xmlfile)) {
                try {
                    $importer = new CPatientXMLImport($xmlfile);
                    $importer->setUpdateData((bool)$updates, (bool)$updates);
                    $importer->setDirectory($_fileinfo->getRealPath(), $_fileinfo->getFilename());

                    $importer->import([], $options);
                    $error_count += $importer->getErrorCount();
                } catch (Exception $e) {
                    CApp::log($e->getMessage());
                }
            }
        }
    }

    $end_time         = microtime(true);
    $current_duration = $end_time - $start_time;

    if ($count_dirs) {
        $import_status = [
            'patient_total'   => CPatientXMLImport::countValideDirs($import_dir),
            'patient_count'   => $step,
            'patient_current' => $start + $step,
            'duration'        => $duration + $current_duration,
            'last_duration'   => $current_duration,
            'last_update'     => CMbDT::dateTime(),
            'error_count'     => $error_count,
        ];

        file_put_contents($imp_file, json_encode($import_status) . "\n", FILE_APPEND);
    }
}
