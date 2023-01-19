<?php

/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CApp;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Stock\CProductStockLocation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Bloc\CBlocOperatoire;

CApp::setTimeLimit(300);
CApp::setMemoryLimit('2048M');
$file = CValue::files("formfile");

$processed_lines = $data_found = 0;
$pending_lines   = [
    'CService'        => [],
    'CBlocOperatoire' => [],
    'total'           => 0,
];
$types           = [
    'Pharmacie' => 'CGroups',
    'Bloc'      => 'CBlocOperatoire',
    'Service'   => 'CService',
];
$errors          = [
    'nofile' => [],
    'nodata' => [],
    'ext'    => [],
    'lines'  => [
        'count'   => [],
        'type'    => [],
        'libelle' => [],
        'actif'   => [],
    ],
];
$group_id        = CGroups::loadCurrent()->_id;
$ds               = CSQLDataSource::get("std");
$smarty          = new CSmartyDP();

if (!$file || !$file['tmp_name']) {
    $errors['nofile'] = true;
    $smarty->assign('errors', $errors);
    $smarty->assign('pending_lines', $pending_lines);
    $smarty->display('vw_import_product_location');
    CApp::rip();
} else {
    foreach ($file['name'] as $key => $_file) {
        if (strtolower(pathinfo($file['name'][$key], PATHINFO_EXTENSION)) === 'csv') {
            // On compte les données correctes pour l'importation
            $data_found = 0;

            $csv = new CCSVFile($file['tmp_name'][$key], CCSVFile::PROFILE_EXCEL);
            $csv->jumpLine(1);

            $empl_services = $empl_bloc = [];
            while ($data = $csv->readLine()) {
                if (count($data) === 1) {
                    //Pour éviter les lignes vide
                    continue;
                } elseif (count($data) !== 5) {
                    // Il faut 5 éléments dans les lignes d'import
                    $errors['lines']['count'][] = implode(';', $data);
                    continue;
                } else {
                    [
                        $name,
                        $type,
                        $position,
                        $desc,
                        $actif,
                    ] = $data;
                    if (!array_key_exists($type, $types)) {
                        // Type d'emplacement non reconnu
                        $errors['lines']['type'][] = implode(';', $data);
                        continue;
                    } elseif ($name === "") {
                        // Le libellé est obligatoire
                        $errors['lines']['libelle'][] = implode(';', $data);
                        continue;
                    } elseif (!in_array($actif, [0, 1])) {
                        $errors['lines']['actif'][] = implode(';', $data);
                        continue;
                    } elseif ($type === 'Pharmacie') {
                        $data_found++;
                        // Si emplacement de pharmacie, on stocke directement (object_id = group_id)
                        $pslocation               = new CProductStockLocation();
                        // On utilise pas loadMatchingObjectEsc car le updatePlainFields calcule la position
                        $where = [
                            "name"         => $ds->prepare("= ?", $name),
                            "object_class" => $ds->prepare("= ?", $types[$type]),
                            "object_id"    => $ds->prepare("= ?", $group_id),
                        ];
                        $pslocation->loadObject($where);
                        $pslocation->name         = $name;
                        $pslocation->desc         = $desc !== "" ? $desc : null;
                        $pslocation->position     = intval($position);
                        $pslocation->actif        = $actif;
                        $pslocation->object_class = $types[$type];
                        $pslocation->group_id     = $group_id;
                        $pslocation->object_id    = $group_id;
                        $processed_lines++;
                        if ($msg = $pslocation->store()) {
                            CAppUI::stepAjax($msg, UI_MSG_WARNING);
                            continue;
                        }
                    } else {
                        $data_found++;
                        // $type === 'Bloc' ou 'Service'
                        $location                       = [
                            "name"         => $name,
                            "object_class" => $types[$type],
                            "position"     => $position,
                            "desc"         => $desc,
                            "actif"        => $actif,
                        ];
                        $pending_lines[$types[$type]][] = $location;
                        $pending_lines['total']++;
                    }
                }
            }
            if ($data_found === 0) {
                // Aucun élément n'a été trouvé dans les fichiers importés
                array_push($errors['nodata'], $file['name'][$key]);
            }
        } else {
            // Fichier autre que CSV
            array_push($errors['ext'], $file['name'][$key]);
        }
    }
}
$errors_lines       = $errors['lines'];
$total_errors_lines =
    count($errors_lines['type']) + count($errors_lines['count']) + count($errors_lines['libelle']) + count(
        $errors_lines['actif']
    );

$service           = new CService();
$bloc              = new CBlocOperatoire();
$where             = [
    "group_id" => $service->getDS()->prepare(" = ?", $group_id),
];
$empl_services     = $service->loadList($where);
$where["group_id"] = $bloc->getDS()->prepare(" = ?", $group_id);
$empl_bloc         = $bloc->loadList($where);

$smarty->assign('location_services', $empl_services);
$smarty->assign('location_blocs', $empl_bloc);

$smarty->assign('processed_lines', $processed_lines);
$smarty->assign('pending_lines', $pending_lines);
$smarty->assign('errors', $errors);
$smarty->assign('total_errors_lines', $total_errors_lines);

// Si il y a des lignes en attente (Type bloc ou service) on peut passe à l'étape 2
if ($pending_lines['total'] === 0) {
    if (!count($errors['nodata']) && !count($errors['ext']) && $total_errors_lines === 0) {
        // Import d'emplacements pharmacies uniquement: Pas besoin de l'étape 2
        CAppUI::js("Control.Modal.close();refreshTab('vw_idx_stock_location');");
    } else {
        // Si aucune ligne en attente et des erreurs
        $smarty->display('vw_import_product_location');
        CApp::rip();
    }
}
$smarty->display('inc_import_product_stock_location');
