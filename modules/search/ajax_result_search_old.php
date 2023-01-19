<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// TODO Remove this file when all has been transfered to legacy controllers
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Search\CSearch;
use Ox\Mediboard\Search\CSearchHistory;
use Ox\Mediboard\Search\CSearchQueryFilter;

CCanDo::checkRead();

// Param
$words         = CView::get("words", "str");
//$_min_date     = CView::get("_min_date", "str default|*");
//$_max_date     = CView::get("_max_date", "str default|*");
//$specific_user = CView::get("user_id", "str");
$start         = CView::get("start", "num default|0");
//$names_types   = (CView::get("names_types", "str")) ?: [];
$aggregate     = CView::get("aggregate", "bool default|0");
$fuzzy_search  = CView::get("fuzzy", "bool default|0");
//$sejour_id     = (CView::get("sejour_id", "str")) ?: null;
//$contexte      = CView::get("contexte", "str");
//$patient_id    = CView::get("patient_id", "str");
//$reference     = CView::get("reference", "str") ?: null;
$export_csv    = CView::get("export_csv", "bool default|0") ?: null;

CView::checkin();

CAccessMedicalData::logAccess("CSejour-$sejour_id");

// Group
$group = CGroups::loadCurrent();
$user  = CMediusers::get();

// Client
$search = new CSearch();
try {
    $search->state();
} catch (Throwable $e) {
    CAppUI::stepAjax("mod-search-indisponible", UI_MSG_ERROR);
}

// Controle du start
if ($start >= CSearch::REQUEST_LIMIT_FROM) {
    CAppUI::stepAjax("mod-search-limit-pagination", UI_MSG_ERROR);
}

// queryBuilder
$searchQueryFilter = new CSearchQueryFilter();
$searchQueryFilter->setWords($words);
$searchQueryFilter->setStart($start);
$searchQueryFilter->setNamesTypes($names_types);
$searchQueryFilter->setAggregation($aggregate);
$searchQueryFilter->setSejourId($sejour_id);
$searchQueryFilter->setSpecificUser($specific_user);
$searchQueryFilter->setDateMax($_max_date);
$searchQueryFilter->setDateMin($_min_date);
$searchQueryFilter->setFuzzySearch($fuzzy_search);
$searchQueryFilter->setPatientId($patient_id);
$searchQueryFilter->setReference($reference);

$body   = $searchQueryFilter->getBodyToElastic();
$params = [
    "index" => $search->_index,
    "type"  => $search->_type,
    "body"  => $body,
];

try {
    // Request
    $response = $search->_client->search($params);

    // Journalisation
    if ($start === 0 && CAppUI::conf("search history active_search_history", $group)) {
        $search_history             = new CSearchHistory();
        $search_history->user_id    = $user->_id;
        $search_history->contexte   = $contexte;
        $search_history->entry      = $words;
        $search_history->agregation = $aggregate;
        $search_history->date       = 'now';
        $search_history->fuzzy      = $fuzzy_search;
        $search_history->types      = is_array($names_types) ? implode("|", $names_types) : $names_types;
        $search_history->hits       = $response['hits']['total'];
        $search_history->store();

        // Purge probability
        $denominator = CAppUI::conf("search history_purge_probability");
        CApp::doProbably(
            $denominator,
            function () {
                CSearchHistory::purgeProbably();
            }
        );
    }
} catch (Exception $e) {
    CAppUI::displayAjaxMsg("mod-search-bad-request", UI_MSG_ERROR);
    CApp::log($e->getMessage());
}

// Pagination
$time     = $response['took'];
$nbresult = $response['hits']['total'];
$stop     = $start + CSearch::REQUEST_SIZE;

// Results
if ($aggregate === '0') {
    $results = $search->formatResults($response);
    $stop    = $stop > $nbresult ? $nbresult : $stop;
} else {
    $results = $search->formatAggregates($response);
    $stop    = $nbresult;
}

// Obfuscation du body
if (CAppUI::conf("search obfuscation_body")) {
    foreach ($results as $key => $_result) {
        $str = $_result['body'];
        preg_match_all("/<b>(.*)<\/b>/imU", $str, $matches, PREG_OFFSET_CAPTURE);
        $str = CMbString::removeAccents($str);
        $str = preg_replace("/[A-Za-z0-9]/i", "X", $str);

        foreach ($matches[0] as $_match) {
            $replace = $_match[0];
            $start   = $_match[1];
            $len     = strlen($replace);
            $str     = substr_replace($str, $replace, $start, $len);
        }

        $results[$key]['body'] = $str;
    }
}

if ($export_csv) {
    ob_clean();
    $date = CMbDT::dateTime();
    header("Content-type: text/csv");
    header('Content-Type: text/html;charset=ISO-8859-1');
    header("Content-disposition: attachment; filename='vw_search_'.$date.'.csv'");

    $fp           = fopen("php://output", "w");
    $csv_writer   = new CCSVFile($fp); // Use PROFILE_EXCEL as default
    $column_names = [
        "Id",
        "Date",
        "Type",
        "Titre",
        "Patient",
        "Author",
        "Document",
    ];
    $csv_writer->setColumnNames($column_names);
    $csv_writer->writeLine($column_names);

    foreach ($results as $_result) {
        $line             = [];
        $line["Id"]       = $_result['guid'];
        $line["Date"]     = $_result['date'];
        $line["Type"]     = $_result['type'];
        $line["Titre"]    = $_result['title'] ?: '';
        $line["Patient"]  = $_result['patient_id'] ? $_result['patient'] : '';
        $line["Author"]   = $_result['author_id'] ? $_result['author'] : '';
        $line["Document"] = $_result['body'] ? CMbString::purifyHTML($_result['body']) : '';
        $csv_writer->writeLine($line);
    }
    CApp::rip();
}

// Tpl
$smarty = new CSmartyDP();
$smarty->assign("start", $start);
$smarty->assign("stop", $stop);
$smarty->assign("time", $time);
$smarty->assign("nbresult", $nbresult);
$smarty->assign("results", $results);
$smarty->assign("words", $words);
$smarty->assign("contexte", $contexte);
$smarty->assign("pagination", true);
$smarty->assign("aggregate", $aggregate);
$smarty->assign("sejour_id", $sejour_id);
$smarty->assign("fuzzy_search", $fuzzy_search);
$smarty->assign("patient_id", $patient_id);
$smarty->display("inc_results_search.tpl");
