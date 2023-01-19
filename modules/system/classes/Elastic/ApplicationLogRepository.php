<?php

/**
 * @package Mediboard\System\Elastic
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Elastic;

use Elasticsearch\Common\Exceptions\ElasticsearchException;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Ox\Core\Elastic\ElasticObject;
use Ox\Core\Elastic\ElasticObjectRepositories;
use Ox\Core\Elastic\Exceptions\ElasticClientException;
use Ox\Core\Elastic\Exceptions\ElasticException;
use Ox\Core\Logger\Wrapper\ApplicationLoggerWrapper;

class ApplicationLogRepository extends ElasticObjectRepositories
{
    /**
     * Defines the Object that the repository will manage
     * @return ElasticObject
     */
    public function getElasticObject(): ElasticObject
    {
        return new ApplicationLog();
    }

    /**
     * This method search through Elastic from the fields you specified
     * and add html highlight tags around words matching the search
     *
     * @param int    $number
     * @param string $search_data
     * @param array  $fields
     * @param int    $from
     * @param string $sort_type
     *
     * @return array
     * @throws ElasticException
     */
    public function searchWithHighlighting(
        int $number,
        string $search_data,
        array $fields,
        int $from = 0,
        string $sort_type = self::SORTING_NO_SORTING
    ): array {
        $settings  = $this->object->getSettings();
        $sort      = $this->getSortingParams($sort_type);
        $search    = $this->getSearchParams($search_data, $fields);
        $highlight = $this->getHighlightParams();


        if ($sort) {
            $query = [
                "size"      => $number,
                "from"      => $from,
                "query"     => $search,
                "highlight" => $highlight,
                "sort"      => $sort,
            ];
        } else {
            $query = [
                "size"      => $number,
                "from"      => $from,
                "min_score" => 0,
                "query"     => $search,
                "highlight" => $highlight,
            ];
        }

        $params = [
            'index' => $settings->getAliasName(),
            'body'  => json_encode($query),
        ];

        try {
            $data = $this->client->search($params);
        } catch (NoNodesAvailableException $e) {
            throw new ElasticClientException($e->getMessage());
        } catch (ElasticsearchException $e) {
            throw new ElasticException($e->getMessage());
        }

        return $this->loadList($data["hits"]["hits"]);
    }


    /**
     * This method search through Elastic from the fields you specified
     * and add html highlight tags around words matching the search
     *
     * @param int    $number
     * @param string $search_data
     * @param array  $fields
     * @param int    $from
     * @param bool   $case_sensitive
     * @param string $sort_type
     *
     * @return array
     * @throws ElasticException
     */
    public function searchWithRegexAndHighlighting(
        int $number,
        string $search_data,
        array $fields,
        int $from = 0,
        bool $case_sensitive = false,
        string $sort_type = self::SORTING_DATE_DESC
    ): array {
        $settings  = $this->object->getSettings();
        $sort      = $this->getSortingParams($sort_type);
        $search    = $this->getSearchRegexParams($search_data, $fields, $case_sensitive);
        $highlight = $this->getHighlightParams();


        if ($sort) {
            $query = [
                "size"      => $number,
                "from"      => $from,
                "query"     => $search,
                "highlight" => $highlight,
                "sort"      => $sort,
            ];
        } else {
            $query = [
                "size"      => $number,
                "from"      => $from,
                "min_score" => 1.1,
                "query"     => $search,
                "highlight" => $highlight,
            ];
        }

        $params = [
            'index' => $settings->getAliasName(),
            'body'  => json_encode($query),
        ];

        try {
            $data = $this->client->search($params);
        } catch (NoNodesAvailableException $e) {
            throw new ElasticClientException($e->getMessage());
        } catch (ElasticsearchException $e) {
            throw new ElasticException($e->getMessage());
        }

        return $this->loadList($data["hits"]["hits"]);
    }

    /**
     * @param int    $timeout
     * @param int    $size_per_shard number of logs per scroll execution
     * @param string $sort_type
     *
     * @return array
     * @throws ElasticException
     */
    public function startScroll(
        int $timeout = 30,
        int $size_per_shard = 50,
        string $sort_type = self::SORTING_DATE_ASC
    ): array {
        $settings = $this->object->getSettings();
        $sort     = $this->getSortingParams($sort_type);

        $params = [
            "scroll" => $timeout . "s",
            "size"   => $size_per_shard,
            "index"  => $settings->getAliasName(),
            "sort"   => json_encode($sort),
            "body"   => [
                "query" => [
                    "match_all" => new \stdClass(),
                ],
            ],
        ];

        try {
            $data = $this->client->search($params);
        } catch (NoNodesAvailableException $e) {
            throw new ElasticClientException($e->getMessage());
        } catch (ElasticsearchException $e) {
            throw new ElasticException($e->getMessage());
        }

        return ["logs" => $this->loadList($data["hits"]["hits"]), "scroll_id" => $data["_scroll_id"]];
    }

    /**
     * @param string $scroll_id That can be retrive from the result of startScroll()["scroll_id]
     * @param int    $timeout
     *
     * @return array
     * @throws ElasticException
     */
    public function continueScroll(string $scroll_id, int $timeout = 30): array
    {
        $params = [
            "body" => [
                "scroll_id" => $scroll_id,
                "scroll"    => $timeout . "s",
            ],
        ];

        try {
            $data = $this->client->scroll($params);
        } catch (NoNodesAvailableException $e) {
            throw new ElasticClientException($e->getMessage());
        } catch (ElasticsearchException $e) {
            throw new ElasticException($e->getMessage());
        }

        return ["logs" => $this->loadList($data["hits"]["hits"]), "scroll_id" => $data["_scroll_id"]];
    }


    /**
     * @param string $filepath
     *
     * @return void
     * @throws ElasticException
     */
    public function dumpIndexIntoFile(string $filepath): void
    {
        $repo   = new ApplicationLogRepository();
        $client = $repo->getClient();

        $response = $repo->startScroll();

        if ($filepath == "") {
            $filepath = ApplicationLoggerWrapper::getPathApplicationLog();
            $filepath = str_replace(".log", "-elastic.log", $filepath);
        }

        $file = fopen($filepath, "w");
        while (count($response["logs"]) > 0) {
            /**
             * @var $applicationLog ApplicationLog
             */
            foreach ($response["logs"] as $applicationLog) {
                $str = $applicationLog->toLogFile();
                fwrite($file, $str . "\n");
            }
            $response = $repo->continueScroll($response["scroll_id"]);
        }
        fclose($file);
    }

    /**
     * Prepared highlight params for ApplicationLog
     * @return array
     */
    protected function getHighlightParams(): array
    {
        return [
            "pre_tags"  => "<highlight style=\"background-color: yellow; display: inline;\">",
            "post_tags" => "</highlight>",
            "order"     => "score",
            "fields"    => [
                "context" => [
                    "fragment_size"   => 80,
                    "fragment_offset" => 30,
                ],
                "message" => [
                    "fragment_size"   => 100,
                    "fragment_offset" => 30,
                ],
            ],
        ];
    }
}
