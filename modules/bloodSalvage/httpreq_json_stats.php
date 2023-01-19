<?php

/**
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CFlotrGraph;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Mediboard\BloodSalvage\CBloodSalvage;
use Ox\Mediboard\BloodSalvage\CCellSaver;
use Ox\Mediboard\BloodSalvage\Services\BloodSalvageService;

CCanDo::checkRead();

$mean_fields = [
    "wash_volume",
    "saved_volume",
    "transfused_volume",
    "hgb_pocket",
    "hgb_patient",
];

$possible_filters = array_merge(
    ['age', 'chir_id', 'anesth_id', 'codes_ccam', 'code_asa', 'cell_saver_id'],
    $mean_fields
);

$filters          = CValue::get('filters', []);
$months_count     = CValue::get('months_count', 12);
$months_relative  = CValue::get('months_relative', 0);
$comparison       = CValue::get('comparison', $possible_filters);
$comparison_left  = CValue::get('comparison_left');
$comparison_right = CValue::get('comparison_right');
$mode             = CValue::get('mode');

foreach ($possible_filters as $n) {
    if (!isset($filters[$n])) {
        $filters[$n] = null;
    }
}

// Dates
$dates     = [];
$first_day = CMbDT::format(null, "%Y-%m-01");
for ($i = $months_count - 1; $i >= 0; --$i) {
    $mr           = $months_relative + $i;
    $sample_end   = CMbDT::transform("-$mr MONTHS", $first_day, "%Y-%m-31 23:59:59");
    $sample_start = CMbDT::transform("-$mr MONTHS", $first_day, "%Y-%m-01 00:00:00");

    $dates[$sample_start] = [
        'start' => $sample_start,
        'end'   => $sample_end,
    ];
}

$ljoin = [
    'operations'          => 'blood_salvage.operation_id = operations.operation_id',
    'consultation_anesth' => 'operations.operation_id = consultation_anesth.operation_id',
    'sejour'              => 'operations.sejour_id = sejour.sejour_id',
    'patients'            => 'sejour.patient_id = patients.patient_id',
];

$where = [];
if ($filters['anesth_id']) {
    $where['operations.anesth_id'] = " = '{$filters['anesth_id']}'";
}

if ($filters['chir_id']) {
    $where['plagesop.chir_id'] = " = '{$filters['chir_id']}'";
}

if ($filters['code_asa']) {
    $where['operations.ASA'] = " = '{$filters['code_asa']}'";
}

if ($filters['cell_saver_id']) {
    $where['blood_salvage.cell_saver_id'] = " = '{$filters['cell_saver_id']}'";
}

$bs   = new CBloodSalvage();
$data = [];

// Par tranche d'age
$data['age'] = [
    'options' => [
        'title' => 'Par tranche d\'âge',
    ],
    'series'  => [],
];
$series      = &$data['age']['series'];
$age_areas   = [0, 20, 40, 50, 60, 70, 80];
foreach ($age_areas as $key => $age) {
    $limits = [$age, CValue::read($age_areas, $key + 1)];
    $label  = $limits[1] ? ("$limits[0] - " . ($limits[1] - 1)) : ">= $limits[0]";

    $date_min = CMbDT::date("-{$limits[1]} YEARS");
    $date_max = CMbDT::date("-{$limits[0]} YEARS");

    // Age calculation
    $where[] = "patients.naissance <= '$date_max' " .
        ($limits[1] != null ? " AND patients.naissance > '$date_min'" : "");

    $series[$key] = ['data' => [], 'label' => "$label ans"];
    BloodSalvageService::fillData($where, $ljoin, $series[$key], $dates);
}

// > 6h ou pas
$data['6h'] = [
    'options' => [
        'title' => 'Durée règlementaire',
    ],
    'series'  => [],
];
$series     = &$data['6h']['series'];
$areas      = ["< 6", ">= 6", "IS NULL"];
foreach ($areas as $key => $area) {
    $where[]      = "HOUR(TIMEDIFF(blood_salvage.transfusion_end, blood_salvage.recuperation_start)) $area";
    $series[$key] = ['data' => [], 'label' => (($area == 'IS NULL') ? CAppUI::tr("Unknown") : $area . 'h')];
    BloodSalvageService::fillData($where, $ljoin, $series[$key], $dates);
}

// H/F
$data['sexe'] = [
    'options' => [
        'title' => 'Par sexe',
    ],
    'series'  => [],
];
$series       = &$data['sexe']['series'];
$areas        = ["= 'm'", "= 'f'"];
$areas_labels = ["= 'm'" => "Homme", "= 'f'" => "Femme"];
foreach ($areas as $key => $area) {
    $where[]      = "patients.sexe $area";
    $series[$key] = ['data' => [], 'label' => $areas_labels[$area]];
    BloodSalvageService::fillData($where, $ljoin, $series[$key], $dates);
}

// Codes CCAM
if ($filters['codes_ccam']) {
    $list_codes_ccam = explode('|', $filters['codes_ccam']);

    $data['ccam'] = [
        'options' => [
            'title' => 'Par code CCAM',
        ],
        'data'    => [],
    ];
    $series       = &$data['ccam']['series'];
    foreach ($list_codes_ccam as $key => $ccam) {
        $where[]      = "operations.codes_ccam LIKE '%$ccam%'";
        $series[$key] = ['data' => [], 'label' => $ccam];
        BloodSalvageService::fillData($where, $ljoin, $series[$key], $dates);
    }
}

// Volume de lavage
$mean_props = [
    "wash_volume"       => "ml",
    "saved_volume"      => "ml",
    "transfused_volume" => "ml",
    "hgb_pocket"        => "",
    "hgb_patient"       => "",
];

foreach ($mean_props as $_prop => $_unit) {
    $data[$_prop] = [
        'options' => [
            'title' => CAppUI::tr("CBloodSalvage-$_prop") . ($_unit ? " ($_unit)" : ""),
        ],
        'data'    => [],
    ];
    $series       = &$data[$_prop]['series'];
    $series[]     = ['data' => []];
    BloodSalvageService::computeMeanValue($where, $ljoin, $series[count($series) - 1], $dates, $_prop);
}

// Cell savers
$cell_saver = new CCellSaver();
$list_cell_savers = $cell_saver->loadMatchingList("marque, modele");

if (count($list_cell_savers) == 0) {
    $list_cell_savers[] = null;
}

$data['cell_saver_id'] = [
    'options' => [
        'title' => 'Cell saver',
    ],
    'data'    => [],
];
$series                = &$data['cell_saver_id']['series'];

// array_values() to have contiguous keys
foreach (array_values($list_cell_savers) as $key => $_cell_saver) {
    if ($_cell_saver && $_cell_saver->_id) {
        $where[] = "blood_salvage.cell_saver_id = $_cell_saver->_id";
    } else {
        $where[] = "blood_salvage.cell_saver_id IS NULL || blood_salvage.cell_saver_id = ''";
    }

    $series[$key] = ['data' => [], 'label' => $_cell_saver->modele ?: CAppUI::tr("Unknown")];
    BloodSalvageService::fillData($where, $ljoin, $series[$key], $dates);
}

if ($mode === "comparison") {
    $data_left  = $data[$comparison_left];
    $data_right = ($comparison_left == $comparison_right) ? ["series" => []] : $data[$comparison_right];

    $title = $data_left["options"]["title"] . " / " . $data_right["options"]["title"];

    foreach ($data_right["series"] as &$_serie) {
        $_serie["yaxis"] = 2;
        $_serie["lines"] = [
            "show" => true,
        ];
        $_serie["mouse"] = [
            "track" => true,
        ];
        $_serie["bars"]  = [
            "show" => false,
        ];
    }

    $data = [
        "comp" => [
            "series"  => array_merge($data_left["series"], $data_right["series"]),
            "options" => $data_left["options"],
        ],
    ];
}

// Ticks
$i     = 0;
$ticks = [];
foreach ($dates as $month => $date) {
    $ticks[$i] = [$i, CMbDT::format($month, '%m/%y')];
    $i++;
}

foreach ($data as &$_data) {
    $ticks[] = [count($ticks), "Total"];

    $_data["options"] = CFlotrGraph::merge("bars", $_data["options"]);
    $_data["options"] = CFlotrGraph::merge(
        $_data["options"],
        [
            'xaxis' => ['ticks' => $ticks, 'labelsAngle' => 45],
            'bars'  => ['stacked' => true],
        ]
    );

    CFlotrGraph::computeTotals($_data["series"], $_data["options"]);
}

CApp::json($data, "text/plain");
