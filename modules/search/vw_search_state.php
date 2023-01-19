<?php
/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CFileParser;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Search\CSearch;
use Ox\Mediboard\Search\CSearchIndexing;

CCanDo::checkAdmin();

// Client
$search = new CSearch();
try {
  $search->state();
}
catch (Throwable $e) {
  CAppUI::stepAjax("mod-search-indisponible", UI_MSG_ERROR);
}

$stats = $search->getStats();

$searchIndexing = new CSearchIndexing();
$ds             = $searchIndexing->getDS();

$query           = "SELECT MIN(`date`) AS oldest_datetime FROM `search_indexing`";
$oldest_datetime = $ds->loadResult($query);

$where           = array("processed" => " = '0' ");
$nbr_doc_attente = $searchIndexing->countList($where);

$where          = array("processed" => " = '1' ");
$nbr_doc_erreur = $searchIndexing->countList($where);

$infos_index               = array();
$infos_index['name_index'] = $search->_index;
$infos_index['connexion']  = $search->state() ? "1" : "0";
$infos_index['status']     = $stats['cluster']['status'];
$infos_index['stats']      = array(
  'shards'  => array(
    'successful' => 0,
    'total'      => 0,
    'failed'     => 0,
  ),
  'cluster' => array(
    'nbIndex'     => $stats['cluster']['indices']['count'],
    'nbDocsTotal' => $stats['cluster']['indices']['docs']['count'],
    'size'        => $stats['cluster']['indices']['store']['size_in_bytes']
  )
);
$infos_index['tampon']     = array(
  'oldest_datetime' => $oldest_datetime,
  'nbr_doc_attente' => $nbr_doc_attente,
  'nbr_doc_erreur'  => $nbr_doc_erreur
);

foreach ($stats['index'] as $key => $index) {
  $name            = ucfirst($key);
  $nbDocs_indexed  = $index['_all']['total']['docs']['count'];
  $nbDocs_to_index = $index['_all']['total']['indexing']['index_total'];
  $search_nbr      = $index['_all']['total']['search']['query_total'];
  $query_time      = $index['_all']['total']['search']['query_time_in_millis'];
  $search_avg      = $search_nbr > 0 && $query_time > 0 ? round($search_nbr / $query_time, 2) : 0;

  $infos_index['index'][$name] = array(
    'nbDocs_indexed'  => $nbDocs_indexed,
    'nbDocs_to_index' => $nbDocs_to_index,
    'search_nbr'      => $search_nbr,
    'search_avg'      => $search_avg,
  );
}

// TIKA
try {
  $parser     = new CFileParser();
  $infos_tika = '1';
  $parsers    = $parser->client->getAvailableParsers();
  $infos_ocr  = false === strpos($parsers, 'TesseractOCRParser') ? '0' : '1';
}
catch (Throwable $e) {
  $infos_tika = '0';
  $infos_ocr  = '0';
}


$smarty = new CSmartyDP();
$smarty->assign('infos_index', $infos_index);
$smarty->assign("infos_tika", $infos_tika);
$smarty->assign("infos_ocr", $infos_ocr);
$smarty->display("vw_etat_systeme.tpl");


