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
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\Import\CMbObjectExport;
use Ox\Core\Logger\LoggerLevels;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\System\CConfiguration;

CCanDo::checkAdmin();

$export_dir      = CView::get('root_path', 'str default|' . CAppUI::gconf("importTools export root_path"));
$date_min        = CView::get('date_min', 'str default|' . CAppUI::gconf("importTools export date_min"));
$date_max        = CView::get('date_max', 'str default|' . CAppUI::gconf("importTools export date_max"));
$praticiens_ids  = CView::get('praticiens', 'str default|' . CAppUI::gconf("importTools export praticiens"));
$tag             = CView::get('tag', 'str default|' . CAppUI::gconf("importTools export tag"));
$mode            = CView::get('mode', 'enum list|all|patients|prescriptions default|all');
$ignored_classes = CView::get('ignored_classes', 'str');
$patient_id      = CView::get('patient_id', 'ref class|CPatient');
$zip_files       = CView::get('zip_files', 'bool default|0');
$pdf_previews    = CView::get('pdf_previews', 'bool default|0');

CView::checkin();
CView::enforceSlave();

if (!CAppUI::gconf('importTools export actif')) {
    CApp::rip();
}

if (!is_dir($export_dir)) {
    if ($msg = CConfiguration::setConfig("importTools export actif", 0, CGroups::loadCurrent())) {
        CApp::log($msg);
    }

    CApp::log(CAppUI::tr('importTools-export-root_path is not a directory'));
    CApp::rip();
}

if (!$tag) {
    CApp::log(CAppUI::tr('importTools-export-tag is mandatory'));
    CApp::rip();
}

$start_time = microtime(true);

// Praticiens ids to use
if ($praticiens_ids) {
    $praticiens_ids = explode('|', $praticiens_ids);
} else {
    $mediuser       = new CMediusers();
    $praticiens_ids = $mediuser->getGroupIds();
}

// Set the subdirectory to use to get infos from export.status and to store export
$sub_dir = "all";
if ($date_min && $date_max) {
    $sub_dir = "{$date_min}_{$date_max}";
} elseif ($date_min) {
    $sub_dir = "after-{$date_min}";
} elseif ($date_max) {
    $sub_dir = "before-{$date_max}";
}

$export_dir = rtrim($export_dir, "\\/") . "/{$tag}/{$sub_dir}/";
$exp_file   = $export_dir . 'export.status';

if (file_exists($exp_file)) {
    $last_status   = json_decode(CMbPath::tailCustom($exp_file), true);
    $start         = (isset($last_status['patient_current'])) ? $last_status['patient_current'] : 0;
    $step          = (isset($last_status['patient_count'])) ? $last_status['patient_count'] : 1;
    $last_duration = (isset($last_status['last_duration'])) ? $last_status['last_duration'] : 0;
    $duration      = (isset($last_status['duration'])) ? $last_status['duration'] : 0;
    $total_size    = (isset($last_status['size'])) ? $last_status['size'] : 0;

    $step = ($last_duration < 30) ? $step * 2 : (($last_duration > 59) ? round($step / 2) : $step);
} else {
    $start      = 0;
    $step       = 1;
    $duration   = 0;
    $total_size = 0;
}

$order = [
    "patients.nom",
    "patients.nom_jeune_fille",
    "patients.prenom",
    "patients.naissance",
    "patients.patient_id",
];

if ($patient_id) {
    $patient = new CPatient();
    $patient->load($patient_id);

    if (!$patient->_id) {
        trigger_error("importTools-msg-Patient does not exists");
        exit();
    }

    $patients      = [$patient];
    $patient_total = 1;
} else {
    // Get the patients to export
    [$patients, $patient_total] = CMbObjectExport::getPatientsToExport(
        $praticiens_ids,
        $date_min,
        $date_max,
        $start,
        $step,
        $order,
        ($mode == 'prescriptions') ? 'sejour' : null
    );
}

// Callback used to filter if objects have to be exported or not
$filter_callback = function (CStoredObject $object) use ($praticiens_ids, $date_min, $date_max, $ignored_classes) {
    $ignored_classes = ($ignored_classes) ? explode('|', $ignored_classes) : [];

    return CMbObjectExport::exportFilterCallback($object, $date_min, $date_max, $praticiens_ids, $ignored_classes);
};

switch ($mode) {
    case 'patients':
        $back_refs = CMbObjectExport::MINIMIZED_BACKREFS_TREE;
        $fw_refs   = CMbObjectExport::MINIMIZED_FWREFS_TREE;
        break;
    case 'prescriptions':
        $back_refs = CMbObjectExport::PRESCRIPTION_BACKREFS_TREE;
        $fw_refs   = [];
        break;
    case 'all':
    default:
        $back_refs = CMbObjectExport::DEFAULT_BACKREFS_TREE;
        $fw_refs   = CMbObjectExport::DEFAULT_FWREFS_TREE;
}

// Export each patient from the list
foreach ($patients as $_pat) {
    try {
        $dir = "$export_dir/{$_pat->_guid}";

        // Auto replace the files for cron
        //if (file_exists($dir . '/export.xml')) {
        //  continue;
        //}

        CMbPath::forceDir($dir);

        $export = new CMbObjectExport($_pat, $back_refs);
        $export->setForwardRefsTree($fw_refs);

        // Define callback for each CPatient because the $dir change for each
        $callback = function (CStoredObject $object) use (&$total_size, $dir, $zip_files, $pdf_previews, $mode) {
            $total_size += CMbObjectExport::exportCallBack(
                $object,
                $dir,
                $pdf_previews,
                false,
                ($mode != 'prescriptions'),
                $zip_files
            );
        };

        $export->empty_values = false;
        $export->setObjectCallback($callback);

        if ($praticiens_ids) {
            $export->setFilterCallback($filter_callback);
        }

        $xml = $export->toDOM()->saveXML();
        file_put_contents("$dir/export.xml", $xml);
        $total_size += filesize("$dir/export.xml");
    } catch (Throwable $e) {
        CApp::log($e->getMessage(), null, LoggerLevels::LEVEL_ERROR);
    }
}

$end_time         = microtime(true);
$current_duration = $end_time - $start_time;

if ($patients && !$patient_id) {
    $export_status = [
        'patient_total'   => $patient_total,
        'patient_count'   => $step,
        'patient_current' => $start + $step,
        'size'            => $total_size,
        'duration'        => $duration + $current_duration,
        'last_duration'   => $current_duration,
        'last_update'     => CMbDT::dateTime(),
    ];

    file_put_contents($exp_file, json_encode($export_status) . "\n", FILE_APPEND);
}
