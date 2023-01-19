<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Search;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Mediboard\Mediusers\CMediusers;

class SearchService
{
    /** @var CSearchQueryFilter */
    private $query_builder;

    /** @var CSearch */
    private $search_client;

    /** @var int */
    private $stop = 0;

    public function __construct(CSearchQueryFilter $query_filter, CSearch $client)
    {
        $this->query_builder = $query_filter;
        $this->search_client = $client;
    }

    public function getResults(int $start, bool $aggregate): ?SearchResults
    {
        $body   = $this->query_builder->getBodyToElastic();
        $params = [
            "index" => $this->search_client->_index,
            "type"  => $this->search_client->_type,
            "body"  => $body,
        ];

        try {
            // Request
            $response = $this->search_client->_client->search($params);
        } catch (Exception $e) {
            CAppUI::displayAjaxMsg("mod-search-bad-request", UI_MSG_ERROR);
            CApp::log($e->getMessage());

            return new SearchResults(0, 0);
        }

        $stop = $start + CSearch::REQUEST_SIZE;

        if ($aggregate) {
            $results    = SearchResultsFactory::fromAggregate($response);
            $this->stop = ($stop > $results->getTotal()) ? $results->getTotal() : $stop;

            return $results;
        }

        $results    = SearchResultsFactory::fromResponse($response);
        $this->stop = $results->getTotal();

        return $results;
    }

    public function getStop(): int
    {
        return $this->stop;
    }

    public function makeHistory(CMediusers $mediusers, SearchResults $results): CSearchHistory {
        // Journalisation
        $names_types = $this->query_builder->getNamesTypes();

        $search_history          = new CSearchHistory();
        $search_history->user_id = $mediusers->_id;
        //        $search_history->contexte   = $contexte;
        $search_history->entry      = $this->query_builder->getWords();
        $search_history->agregation = $this->query_builder->getAggregation();
        $search_history->date       = 'now';
        $search_history->fuzzy      = true;
        $search_history->types      = is_array($names_types) ? implode("|", $names_types) : $names_types;
        $search_history->hits       = $results->getTotal();

        return $search_history;
    }

    public function purgeProbability(float $denominator): void
    {
        CApp::doProbably(
            $denominator,
            function (): void {
                CSearchHistory::purgeProbably();
            }
        );
    }
}
