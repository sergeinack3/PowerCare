<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkAdmin();

$limit             = CView::get('limit', 'num default|10');
$reset             = CView::get('reset', 'num');
$blank             = CView::get('blank', 'str');
$start_date        = CView::get('start_date', 'str');
$end_date          = CView::get('end_date', 'str');
$name              = CView::get('name', 'str default|Labo_REPORTPDF');
$continue          = CView::get('continue', "str");
CView::checkin();

$mediuser = CMediusers::get();
if (!$mediuser || $mediuser->_user_type != 1) {
    CAppUI::stepAjax('Vous n\'avez pas les droits pour effectuer cette action', UI_MSG_ERROR);
}

if (preg_match("#%#", $name)) {
    CAppUI::stepAjax('Nom de fichier non supporté', UI_MSG_ERROR);
}

$values = null;
$offset = $count_delete_step = 0;

$cache = new Cache('purge', 'purge_params', Cache::INNER_OUTER);
if ($cache->exists()) {
    $values = $cache->get();

    if (CMbArray::get($values, 'offset')) {
        $offset = CMbArray::get($values, 'offset');
    }

    if (CMbArray::get($values, 'count_delete_step')) {
        $count_delete_step = CMbArray::get($values, 'count_delete_step');
    }
}

CApp::log('cache', $values);

if ($reset) {
    $cache->put([
                    'offset' => 0,
                    'count_delete_step' => 0
                ]);
    CAppUI::stepAjax("Compteur en session réinitialisé");
    CApp::rip();
} else {
    $count_delete_step_cache = CMbArray::get($values, 'count_delete_step') ?: 0;
    $cache->put(['offset' => $offset + $limit, 'count_delete_step' => $count_delete_step_cache ]);
}

if (!$start_date) {
    $start_date = '2021-03-17 00:00:00';
}

if (!$end_date) {
    $end_date = '2021-03-18 23:59:59';
}

$ds = CSQLDataSource::get("std");

if ($blank) {
    dump('Début de l\'essai à blanc');
}

CView::enforceSlave();
// Compte le nombre de fichiers possiblement impactés
$file_count = new CFile();
$where = array();
$where['file_name'] = $ds->prepareLike("%$name%");
$where['file_date'] = $ds->prepare("BETWEEN ?1 AND ?2", $start_date, $end_date);
$count_files_found = $file_count->countList($where);

dump($count_files_found . ' fichiers retrouvés avec ces critères de recherche');

// Récupération des fichiers sur l'intervalle de dates + matching sur le nom
$file_found = new CFile();
$files_found = $file_found->loadList($where, null, "$offset, $limit");

dump("Limite SQL : $offset, $limit");
if ($offset > $count_files_found) {
    CAppUI::stepAjax('Limite SQL plus grande que le nombre de fichiers', UI_MSG_ERROR);
}

$count_delete = 0;
foreach ($files_found as $_file_found) {
    CView::enforceSlave();

    // File peut être déjé supprimé ?
    $file_exit = new CFile();
    $file_exit->load($_file_found->_id);
    if (!$file_exit->_id) {
        continue;
    }

    $file_search = new CFile();
    $where = array();
    $where['file_id']      = $ds->prepare("!= ?", $_file_found->_id);
    $where['file_name']    = $ds->prepareLike("%$name%");
    $where['doc_size']     = $ds->prepare("= ?", $_file_found->doc_size);
    $where['object_id']    = $ds->prepare("= ?", $_file_found->object_id);
    $where['object_class'] = $ds->prepare("= ?", $_file_found->object_class);
    $where['file_date']    = $ds->prepare("BETWEEN ?1 AND ?2", $start_date, $end_date);

    $files_search_ids = $file_search->loadIds($where);

    if ($blank) {
        $count_delete = $count_delete + count($files_search_ids);
        continue;
    }

    CView::disableSlave();
    $file_search->deleteAll($files_search_ids);
}

if ($blank) {
    dump($count_delete . ' fichiers vont être supprimés pour cet offset');
    dump($count_delete + $count_delete_step . ' fichiers vont être supprimés au total');
} else {
    dump($count_delete . ' fichiers supprimés pour cet offset');
    dump($count_delete + $count_delete_step . ' fichiers supprimés au total');
}

if ($blank) {
    dump('Fin de l\'essai à blanc');
}

$cache->put(['offset' => $offset + $limit, 'count_delete_step' => $count_delete + $count_delete_step]);
if ($continue && $offset < $count_files_found) {
    CAppUI::js("automatic_purge_files()");
}
