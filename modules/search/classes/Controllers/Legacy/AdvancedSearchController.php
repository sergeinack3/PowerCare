<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Search\Controllers\Legacy;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Search\AdvancedSearchQueryBuilder;
use Ox\Mediboard\Search\CSearch;
use Ox\Mediboard\Search\CSearchQueryFilter;
use Ox\Mediboard\Search\SearchService;
use Throwable;

class AdvancedSearchController extends CLegacyController
{
    public function showAdvancedSearch(): void
    {
        $types = null;
        if ($handled = CAppUI::gconf("search active_handler active_handler_search_types")) {
            $types = explode("|", $handled);
        }

        $this->renderSmarty("inc_advanced_search", ["types" => $types]);
    }

    public function do_buildQuery(): void
    {
        CCanDo::checkRead();

        $words         = CView::post("words", "str");
        $without_words = CView::post("without_words", "str");
        $policy        = CView::post("policy", "num notNull");
        $types         = CView::post('types', 'str') ?? [];
        $user_id       = CView::post("user_id", "ref class|CMediusers");
        $patient_id    = CView::post("patient_id", "ref class|CPatient");
        $cim_code      = CView::post("cim_code", "str");
        $atc_code      = CView::post("atc_code", "str");
        $ccam_code     = CView::post("ccam_code", "str");
        $date_min      = CView::post("date_min", "date");
        $date_max      = CView::post("date_max", "date");

        CView::setSession('types', $types);
        CView::setSession('patient_id', $patient_id);
        CView::setSession('user_id', $user_id);
        CView::setSession('date_min', $date_min);
        CView::setSession('date_max', $date_max);
        CView::checkin();

        if (!$words && !$without_words) {
            throw new CMbException('AdvancedSearchController-Missing words or without words');
        }

        $advanced_search_builder = new AdvancedSearchQueryBuilder($words, $policy);
        if ($without_words) {
            $advanced_search_builder->setWithoutWords($without_words);
        }
        if ($user_id) {
            $advanced_search_builder->setAuthor(CMediusers::findOrFail($user_id));
        }
        if ($patient_id) {
            $advanced_search_builder->setPatient(CPatient::findOrFail($patient_id));
        }
        if ($atc_code) {
            $advanced_search_builder->setAtc($atc_code);
        }
        if ($cim_code) {
            $advanced_search_builder->setCim($cim_code);
        }
        if ($ccam_code) {
            $advanced_search_builder->setCcam($ccam_code);
        }

        $this->renderJson(['expression' => $advanced_search_builder->getExpression()]);
    }

    public function resultSearch(): void
    {
        CCanDo::checkRead();
        $words      = CView::get("words", "str");
        $start      = CView::get("start", "num default|0 max|1000");
        $aggregate  = CView::get("aggregate", "bool default|0");
        $types      = CView::get("types", "str", true);
        $user_id    = CView::get("user_id", "ref class|CMediusers", true);
        $patient_id = CView::get("patient_id", "ref class|CPatient", true);
        $date_min   = CView::get("date_min", "date", true);
        $date_max   = CView::get("date_max", "date", true);

        // Reset date filters
        CView::setSession('types');
        CView::setSession('user_id');
        CView::setSession('patient_id');
        CView::setSession('date_min');
        CView::setSession('date_max');
        CView::checkin();

        // Client
        $search = new CSearch();
        try {
            $search->state();
        } catch (Throwable $e) {
            CAppUI::stepAjax("mod-search-indisponible", UI_MSG_ERROR);
        }

        $search_builder = new CSearchQueryFilter();
        $search_builder->setWords($words)->setStart($start)->setAggregation($aggregate)->setFuzzySearch(true);
        if ($patient_id) {
            $search_builder->setPatientId($patient_id);
        }
        if ($user_id) {
            $search_builder->setSpecificUser($user_id);
        }
        if ($types) {
            $search_builder->setNamesTypes($types);
        }
        if ($date_min) {
            $search_builder->setDateMin($date_min);
        }
        if ($date_max) {
            $search_builder->setDateMax($date_max);
        }

        $search_client = new CSearch();

        $service = new SearchService($search_builder, $search_client);
        $results = $service->getResults($start, $aggregate);
        if ($start === 0 && CAppUI::gconf("search history active_search_history")) {
            $search_history = $service->makeHistory(CMediusers::get(), $results);
            $search_history->store();

            // Purge probability
            $service->purgeProbability(CAppUI::conf("search history_purge_probability"));
        }
        $stop = $service->getStop();

        $vars = [
            // Query
            'patient'    => CPatient::find($patient_id),
            'user'       => CMediusers::find($user_id),
            'date_min'   => $date_min,
            'date_max'   => $date_max,
            // Other
            "start"      => $start,
            "stop"       => $stop,
            "time"       => $results->getTime(),
            "nbresult"   => $results->getTotal(),
            "results"    => $results,
            "words"      => ($words) ? stripslashes($words) : '-',
            "pagination" => true,
            "aggregate"  => $aggregate,
            "obfuscate"  => CAppUI::conf("search obfuscation_body"),
        ];
        $this->renderSmarty("inc_results_search", $vars);
    }

    public function resultExportCsv(): void
    {
        CCanDo::checkRead();
        $words      = CView::get("words", "str");
        $start      = CView::get("start", "num default|0 max|1000");
        $aggregate  = CView::get("aggregate", "bool default|0");
        $types      = CView::get("types", "str", true);
        $patient_id = CView::get("patient_id", "ref class|CPatient", true);
        $date_min   = CView::get("date_min", "date", true);
        $date_max   = CView::get("date_max", "date", true);

        // Reset date filters
        CView::setSession('types');
        CView::setSession('patient_id');
        CView::setSession('date_min');
        CView::setSession('date_max');
        CView::checkin();

        // Client
        $search = new CSearch();
        try {
            $search->state();
        } catch (Throwable $e) {
            CAppUI::stepAjax("mod-search-indisponible", UI_MSG_ERROR);
        }

        $search_builder = new CSearchQueryFilter($words);
        $search_builder->setStart($start)->setAggregation($aggregate)->setFuzzySearch(true);
        if ($patient_id) {
            $search_builder->setPatientId($patient_id);
        }
        if ($types) {
            $search_builder->setNamesTypes($types);
        }
        if ($date_min) {
            $search_builder->setDateMin($date_min);
        }
        if ($date_max) {
            $search_builder->setDateMax($date_max);
        }

        $search_client = new CSearch();

        $service = new SearchService($search_builder, $search_client);
        $results = $service->getResults($start, $aggregate);
        if ($start === 0 && CAppUI::gconf("search history active_search_history")) {
            $search_history = $service->makeHistory(CMediusers::get(), $results);
            $search_history->store();

            // Purge probability
            $service->purgeProbability(CAppUI::conf("search history_purge_probability"));
        }

        ob_clean();
        $date = CMbDT::dateTime();
        header("Content-type: text/csv");
        header('Content-Type: text/html;charset=ISO-8859-1');
        header("Content-disposition: attachment; filename='vw_search_'.$date.'.csv'");

        $fp = fopen("php://output", "w");
        $results->asCsv($fp, false);

        CApp::rip();
    }
}
