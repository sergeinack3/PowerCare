<?php
/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Search;

use Elasticsearch\Client;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CStoredObject;
use Ox\Core\Elastic\ElasticIndexManager;
use Ox\Core\Elastic\ElasticObjectManager;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\PlanningOp\CSejour;
use Throwable;

/**
 * Class CSearch
 */
class CSearch implements IShortNameAutoloadable
{
    const INDEXING_INTERVAL  = 60;
    const INDEXING_STEP_MIN  = 10;
    const INDEXING_STEP_MAX  = 5000;
    const REQUEST_LIMIT_FROM = 1000;
    const REQUEST_SIZE       = 15;
    const REQUEST_AGG_SIZE   = 25;

    static $settings_default = [
        'number_of_shards'   => 5,
        'number_of_replicas' => 1,
    ];
    /** @var  array */
    static $contextes = ["generique", "pharmacie", "pmsi", "prescription", "classique"];
    /** @var  Client */
    public $_client;
    /** @var  string */
    public $_index;
    /** @var  string */
    public $_type;

    private ElasticIndexManager $datasource;

    /**
     * CSearch constructor.
     */
    public function __construct()
    {
        $this->createClient();
        $this->_index = $this->datasource->getIndexName();
        $this->_type  = '_doc';
    }

    /**
     * Create client for indexing
     *
     * @return void
     */
    protected function createClient()
    {
        $this->datasource = ElasticObjectManager::getDsn(new Search());

        $this->_client = $this->datasource->getClient();
    }

    /**
     * Get the list of server addresses
     *
     * @return array
     */
    public function getServerAddresses()
    {
        return $this->datasource->getConfig()->getConnectionParams();
    }

    /**
     * Contrôle l'état du service
     *
     * @return array
     * @throws Throwable
     */
    public function state()
    {
        return $this->_client->cluster()->state();
    }

    /**
     * @param int $count Number of elements handled
     * @param int $time  Time to handle the elements
     *
     * @return void
     */
    static function adaptStep($count, $time)
    {
        $step = self::getIndexingStep();

        // We don't adapt the step if nothing was handled
        if ($count < $step || $count == 0) {
            return;
        }

        $new_step = null;

        // Took too much time
        if ($time > self::INDEXING_INTERVAL) {
            $new_step = max(self::INDEXING_STEP_MIN, round($step / 2));
        }

        // Took too short
        if ($time < self::INDEXING_INTERVAL / 2) {
            $new_step = min(self::INDEXING_STEP_MAX, $step * 2);
        }

        if ($new_step) {
            $cache = Cache::getCache(Cache::OUTER);
            $cache->set("search_indexing_step", $new_step);
        }
    }

    /**
     * Get indexing step, from config or from "adapted step"
     *
     * @return int
     */
    static function getIndexingStep()
    {
        $cache = Cache::getCache(Cache::OUTER);

        $step = $cache->get("search_indexing_step");

        if ($step) {
            return $step;
        }

        return (int)CAppUI::conf("search interval_indexing");
    }

    /**
     * @param string $name Index's name
     *
     * @return bool
     */
    function existIndex($name)
    {
        $param = ['index' => $name];

        return $this->_client->indices()->exists($param);
    }

    /**
     * @param string $name Index's name
     *
     * @return void
     */
    function createIndex($name)
    {
        // Index metier
        $params = [
            'index' => $name,
            'body'  => [
                'settings' => self::$settings_default,
            ],
        ];

        $params['body']['settings']["number_of_replicas"] = CAppUI::conf("search nb_replicas");

        $this->_client->indices()->create($params);
    }

    /**
     * Update an index settings
     * Use for change interval
     *
     * @param string $index    the index
     * @param array  $settings the settings you want to apply
     *
     * @return void
     */
    function updateIndexSettings($index = null, $settings = null)
    {
        $settings = ($settings) ?: self::$settings_default;
        $index    = $index ?: $this->_index;

        unset($settings['number_of_shards']);
        unset($settings['number_of_replicas']);

        $params = [
            'index' => $index,
            'body'  => [
                'settings' => $settings,
            ],
        ];

        $this->_client->indices()->close(['index' => $index]);
        $this->_client->indices()->putSettings($params);
        $this->_client->indices()->open(['index' => $index]);
    }

    /**
     * Indexation en bulk avec les données contstruites (avec les fields corrects)
     *
     * @param array $data les data que vous voulez indexer
     * @param bool  $debug
     *
     * @return void
     */
    function bulkIndexing(array $data, $debug = false)
    {
        // create, store, merge
        $bulk = [
            'type' => '_doc',
            'body' => [],
        ];

        foreach ($data as $_doc) {
            $bulk['body'][] = [
                'index' => [
                    '_index' => $this->_index,
                    '_id'    => $_doc['guid'],
                ],
            ];

            $bulk['body'][] = $_doc;
        }

        $response = $this->_client->bulk($bulk);
        if ($debug) {
            dump($response);
        }

        // Controle du retour
        if ($response && is_array($response['items'])) {
            foreach ($response['items'] as $item) {
                $guid   = $item['index']['_id'];
                $status = $item['index']['status'];
                if ($status == 200 || $status == 201) {
                    CSearchIndexing::$current_ids_to_delete[] = $guid;
                } else {
                    CSearchIndexing::$current_ids_to_update[] = $guid;
                }
            }

            // Maj de la table tampon
            CSearchIndexing::majData();
        }
        //@todo this->_client->indices()->refresh(array('index' => $this->_index));
    }

    /**
     * @param array $data Docs to delete
     *
     * @return void
     */
    function deleteDocs(array $data)
    {
        foreach ($data as $_doc) {
            $guid     = $_doc['guid'];
            $params   = [
                'index' => $this->_index,
                'id'    => $guid,
                'type'  => '_doc',
            ];
            $response = $this->_client->delete($params);

            // Controle du retour
            if ($response['result'] == 'deleted') {
                CSearchIndexing::$current_ids_to_delete[] = $guid;
            } else {
                CSearchIndexing::$current_ids_to_update[] = $guid;
            }
        }

        // Maj de la table tampon
        CSearchIndexing::majData();
    }

    /**
     * Traitement d'une response pour affichage
     *
     * @param array $response Response to format
     *
     * @return array $results
     */
    function formatAggregates($response)
    {
        $retour = [];

        foreach ($response['aggregations']['reference']['buckets'] as $result) {
            $object = CStoredObject::loadFromGuid($result['key']);

            // Default
            $titre     = CAppUI::tr('mod-search-results-error_object');
            $praticien = null;
            $patient   = null;

            if ($object->_id) {
                // Titre
                if ($object instanceof CConsultAnesth) {
                    $object->loadRefConsultation();
                }
                $titre = $object->_view;
                // Praticien
                if (method_exists($object, 'loadRefPraticien')) {
                    $praticien = $object->loadRefPraticien();
                } elseif (method_exists($object, 'getIndexablePraticien')) {
                    $praticien = $object->getIndexablePraticien();
                }
                // Patient
                if (method_exists($object, 'loadRelPatient')) {
                    $patient = $object->loadRelPatient();
                }
            }

            // Retour
            $retour[] = [
                'key'       => $result['key'],
                'count'     => $result['doc_count'],
                'titre'     => $titre,
                'patient'   => $patient,
                'praticien' => $praticien,
            ];
        }

        return $retour;
    }

    /**
     * The auto search from favoris
     *
     * @param array   $favoris the favoris
     * @param CSejour $sejour  the sejour
     *
     * @return array
     */
    function searchAuto($favoris, CSejour $sejour)
    {
        $tab_search = [];

        $params = [
            "index" => $this->_index,
            "type"  => $this->_type,
            "body"  => null,
        ];

        // Pour chacun des favoris je fais la recherche associée.
        foreach ($favoris as $_favori) {
            // query
            $searchQueryFilter = new CSearchQueryFilter();
            if ($_favori->types) {
                $searchQueryFilter->setNamesTypes(explode("|", $_favori->types));
            }

            $searchQueryFilter->setSejourId($sejour->_id);
            $searchQueryFilter->setWords($_favori->entry);
            $searchQueryFilter->setFuzzySearch(0);
            $body = $searchQueryFilter->getBodyToElastic();

            $params['body'] = $body;
            $response       = $this->_client->search($params);

            $results = SearchResultsFactory::fromResponse($response);
            $results->setBookmark($_favori);
            $tab_search[] = $results;
        }

        return $tab_search;
    }

    /**
     * Traitement d'une response pour affichage
     *
     * @param array $response Response to format
     *
     * @return array $results
     */
    function formatResults($response)
    {
        $retour = [];

        foreach ($response['hits']['hits'] as $result) {
            // Default data
            $result_format = $result['_source'];

            // title
            $result_format['title'] = CMbString::normalizeUtf8($result_format['title']);
            $result_format['title'] = ucfirst($result_format['title']);

            // author
            if (isset($result['_source']["author_id"])) {
                $author_id               = $result['_source']["author_id"];
                $result_format['author'] = CMbObject::loadFromGuid("CMediusers-$author_id");
                if ($result_format['author'] && $result_format['author'] !== null) {
                    $result_format['author']->loadRefFunction();
                }
            }

            // patient
            if (isset($result['_source']["patient_id"])) {
                $patient_id               = $result['_source']["patient_id"];
                $result_format['patient'] = CMbObject::loadFromGuid("CPatient-$patient_id");
            }

            // body
            $highlight = isset($result['highlight']) ? $result['highlight'] : [];

            if (count($highlight) != 0) {
                $highlight = implode(" [...] ", $highlight['body']);
                $highlight = str_replace("<em>", "<b>", $highlight);
                $highlight = str_replace("</em>", "</b>", $highlight);
            } else {
                $highlight = CMbString::normalizeUtf8($result["_source"]["body"]);
                if (strlen($highlight) > 500) {
                    $highlight = CMbString::truncate($highlight, 500, '');
                    $highlight = preg_replace('/\s+?(\S+)?$/', '', $highlight) . '...';
                }
            }
            $result_format['body'] = $highlight;

            // retour
            $retour[] = $result_format;
        }

        return $retour;
    }

    /**
     * @return array
     */
    function getStats()
    {
        $params = ['index' => $this->_index];

        // Index Stats
        $retour = [];

        $retour['cluster'] = $this->_client->cluster()->stats();
        $retour['nodes']   = $this->_client->nodes()->stats();
        $retour['index']   = [
            $this->_index => $this->_client->indices()->stats($params),
        ];

        $retour['mapping'] = $this->_client->indices()->getMapping($params);

        return $retour;
    }


    /**
     * @param array $params Parameters for the index
     *
     * @return array
     */
    function index($params)
    {
        $response = $this->_client->index($params);

        return $response;
    }
}
