<?php

/**
 * @package Mediboard\Core\Elastic
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic;

use DateTime;
use Elasticsearch\Common\Exceptions\ElasticsearchException;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Ox\Core\Elastic\Exceptions\ElasticClientException;
use Ox\Core\Elastic\Exceptions\ElasticException;
use Ox\Core\Elastic\Exceptions\ElasticObjectMissingException;
use Ox\Core\Elastic\QueryBuilder\ElasticQueryBuilder;

/**
 * Abstract Class ElasticObjectRepositories defines base repository methods and provide base structure to produce
 * specified repository
 */
abstract class ElasticObjectRepositories
{
    public const SORTING_DATE_ASC   = "date_asc";
    public const SORTING_DATE_DESC  = "date_desc";
    public const SORTING_NO_SORTING = "no_sorting";

    protected ElasticIndexManager $dsn;
    protected ElasticClient $client;
    protected ElasticObject $object;


    /**
     * Construct get the ElasticObject that it will manipulate from the abstract method the specified repository fills
     * It also produce based on this object the datasource and an Elastic Client
     *
     * @throws ElasticClientException
     */
    public function __construct(ElasticObject $object = null, ElasticIndexManager $dsn = null, ElasticClient $client = null)
    {
        $this->object = $object ?: $this->getElasticObject();
        $this->dsn    = $dsn ?: ElasticObjectManager::getDsn($this->object);
        $this->client = $client ?: ElasticObjectManager::getClient($this->object);
    }

    /**
     * Abstract methods that return a specified ElasticObject
     * For example in the repository "ApplicationLogRepository" this method return an "ApplicationLog"
     * @return ElasticObject
     */
    abstract public function getElasticObject(): ElasticObject;

    /**
     * @return ElasticClient
     */
    public function getClient(): ElasticClient
    {
        return $this->client;
    }

    /**
     * Load multiple object from elastic_data
     *
     * @param array $elastic_data
     *
     * @return array
     */
    protected function loadList(array $elastic_data): array
    {
        $objects = [];
        foreach ($elastic_data as $data) {
            $obj       = $this->loadOne($data);
            $objects[] = $obj;
        }

        return $objects;
    }

    /**
     * Prepare data from elastic then create an ElasticObject from those data
     *
     * @param array $elastic_data
     *
     * @return ElasticObject
     */
    protected function loadOne(array $elastic_data): ElasticObject
    {
        $obj            = new $this->object();
        $obj_data       = [];
        $obj_data["id"] = $elastic_data["_id"];
        if (array_key_exists("highlight", $elastic_data)) {
            $obj_data["highlight"] = $elastic_data["highlight"] ?? null;
        }
        $source   = $elastic_data["_source"];
        $obj_data = array_merge($obj_data, $source);
        $obj->fromArray($obj_data);

        return $obj;
    }

    public function loadDataFromElastic(array $data): ElasticObject
    {
        return $this->loadOne($data);
    }

    /**
     * This method will find the designed document.
     *
     * @param string $id Id
     *
     * @return ElasticObject | array
     * @throws ElasticClientException|ElasticException
     */
    public function findById(string $id)
    {
        $settings = $this->object->getSettings();
        try {
            $data = $this->client->get(
                [
                    "index" => $settings->getAliasName(),
                    "id"    => $id,
                ]
            );

            return $this->loadOne($data);
        } catch (NoNodesAvailableException $e) {
            throw new ElasticClientException($e->getMessage());
        } catch (Missing404Exception $e) {
            throw new ElasticObjectMissingException($e->getMessage());
        } catch (ElasticsearchException $e) {
            throw new ElasticException($e->getMessage());
        }
    }


    /**
     * This method will gets $number last objects ordered by date.
     *
     * @param int $number Documents to gather
     *
     * @return ElasticObject[]
     * @throws ElasticException
     */
    public function last(int $number): array
    {
        $settings = $this->object->getSettings();

        try {
            $data = $this->client->search(
                [
                    "index" => $settings->getAliasName(),
                    "size"  => $number,
                    "sort"  => "date:desc",
                ]
            );
        } catch (NoNodesAvailableException $e) {
            throw new ElasticClientException($e->getMessage());
        } catch (ElasticsearchException $e) {
            throw new ElasticException($e->getMessage());
        }


        return $this->loadList($data["hits"]["hits"]);
    }

    /**
     * This method will gets $number first objects ordered by date.
     *
     * @param int $number Documents to gather
     *
     * @return ElasticObject[]
     * @throws ElasticException
     */
    public function first(int $number): array
    {
        $settings = $this->object->getSettings();

        try {
            $data = $this->client->search(
                [
                    "index" => $settings->getAliasName(),
                    "size"  => $number,
                    "sort"  => "date:asc",
                ]
            );
        } catch (NoNodesAvailableException $e) {
            throw new ElasticClientException($e->getMessage());
        } catch (ElasticsearchException $e) {
            throw new ElasticException($e->getMessage());
        }


        return $this->loadList($data["hits"]["hits"]);
    }

    /**
     * @return int The number of elements in the index
     */
    public function count(): int
    {
        $settings = $this->object->getSettings();

        try {
            $data = $this->client->count(
                [
                    "index" => $settings->getAliasName(),
                ]
            );
        } catch (ElasticsearchException $e) {
            return 0;
        }

        return $data["count"];
    }

    /**
     * @return int The number of elements in the index for a specified query
     */
    public function countFromQuery(ElasticQueryBuilder $query): int
    {
        try {
            $query = $query->build();
            unset($query["sort"]);
            unset($query["body"]["sort"]);
            unset($query["from"]);
            unset($query["size"]);
            $data = $this->client->count($query);
        } catch (ElasticsearchException $e) {
            return 0;
        }

        return $data["count"];
    }

    /**
     * This method will gets $number documents offset from $from documents
     *
     * @param int $from   Offset number
     * @param int $number Documents to gather
     *
     * @return array
     * @throws ElasticException
     */
    public function list(int $from, int $number): array
    {
        $settings = $this->object->getSettings();

        try {
            $data = $this->client->search(
                [
                    "index" => $settings->getAliasName(),
                    "from"  => $from,
                    "size"  => $number,
                    "sort"  => "date:desc",
                ]
            );
        } catch (NoNodesAvailableException $e) {
            throw new ElasticClientException($e->getMessage());
        } catch (ElasticsearchException $e) {
            throw new ElasticException($e->getMessage());
        }

        return $this->loadList($data["hits"]["hits"]);
    }

    /**
     * This method will gets $number documents offset from $from documents
     *
     * @param int      $from   Offset number
     * @param int      $number Documents to gather
     * @param DateTime $date_start
     * @param DateTime $date_end
     *
     * @return array
     * @throws ElasticException
     */
    public function listBetweenDate(
        int $from,
        int $number,
        DateTime $date_start = null,
        DateTime $date_end = null
    ): array {
        $settings = $this->object->getSettings();

        if ($date_start == null) {
            $date_start = (new DateTime("now"))->modify("-7 days");
        }
        if ($date_end == null) {
            $date_end = new DateTime("now");
        }

        try {
            $data = $this->client->search(
                [
                    "index" => $settings->getAliasName(),
                    "from"  => $from,
                    "size"  => $number,
                    "sort"  => "date:desc",
                    "body"  => [
                        "query" => $this->getDateBetweenParams($date_start, $date_end),
                    ],
                ]
            );
        } catch (NoNodesAvailableException $e) {
            throw new ElasticClientException($e->getMessage());
        } catch (ElasticsearchException $e) {
            throw new ElasticException($e->getMessage());
        }

        return $this->loadList($data["hits"]["hits"]);
    }


    /**
     * This method search through Elastic from the fields you specified
     * And the data you are searching
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
    public function search(
        int $number,
        string $search_data,
        array $fields,
        int $from = 0,
        string $sort_type = self::SORTING_DATE_DESC
    ): array {
        $settings = $this->object->getSettings();
        $sort     = $this->getSortingParams($sort_type);

        $search = $this->getSearchParams($search_data, $fields);

        if ($sort) {
            $query = [
                "size"  => $number,
                "from"  => $from,
                "query" => $search,
                "sort"  => $sort,
            ];
        } else {
            $query = [
                "size"      => $number,
                "from"      => $from,
                "min_score" => 1.1,
                "query"     => $search,
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
     * @param array $query
     *
     * @return array
     * @throws ElasticClientException
     * @throws ElasticException
     */
    public function execQuery(ElasticQueryBuilder $query): array
    {
        try {
            $data = $this->client->search($query->build());
        } catch (NoNodesAvailableException $e) {
            throw new ElasticClientException($e->getMessage());
        } catch (ElasticsearchException $e) {
            throw new ElasticException($e->getMessage());
        }

        return $this->loadList($data["hits"]["hits"]);
    }

    /**
     * @param array $query
     *
     * @return array
     * @throws ElasticClientException
     * @throws ElasticException
     */
    public function execQueryToResult(ElasticQueryBuilder $query): array
    {
        try {
            $data = $this->client->search($query->build());
        } catch (NoNodesAvailableException $e) {
            throw new ElasticClientException($e->getMessage());
        } catch (ElasticsearchException $e) {
            throw new ElasticException($e->getMessage());
        }

        return $data;
    }

    /**
     * Give sorting params from type
     *
     * @param string $sort_type
     *
     * @return array|null
     */
    protected function getSortingParams(string $sort_type): ?array
    {
        switch ($sort_type) {
            case self::SORTING_DATE_ASC:
                $sort = [["date" => ["order" => "asc"]]];
                break;
            case self::SORTING_DATE_DESC:
                $sort = [["date" => ["order" => "desc"]]];
                break;
            case self::SORTING_NO_SORTING:
            default:
                $sort = null;
        }

        return $sort;
    }

    /**
     * Give a formatted array to search through fields
     *
     * @param string $search_data
     * @param array  $fields
     *
     * @return array
     */
    protected function getSearchParams(string $search_data, array $fields): array
    {
        return [
            "bool" => [
                "minimum_should_match" => 0,
                "should"               => [
                    [
                        "multi_match" => [
                            "query"  => $search_data,
                            "fields" => $fields,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Give a formatted array to search regex through fields
     *
     * @param string $search_data
     * @param array  $fields
     * @param bool   $case_sensitive
     *
     * @return array
     */
    protected function getSearchRegexParams(string $search_data, array $fields, bool $case_sensitive): array
    {
        $query         = [];
        $query["bool"] = [
            "minimum_should_match" => 0,
            "should"               => [],
        ];

        foreach ($fields as $field) {
            $query["bool"]["should"][] = [
                "regexp" => [
                    $field => [
                        "value"            => $search_data,
                        "flags"            => "ALL",
                        "case_insensitive" => $case_sensitive,
                    ],
                ],
            ];
        }

        return $query;
    }

    /**
     * Give a formatted range of dates for elastic
     *
     * @param DateTime $date_start
     * @param DateTime $date_end
     *
     * @return array
     */
    protected function getDateBetweenParams(DateTime $date_start, DateTime $date_end): array
    {
        return [
            "range" => [
                "date" => [
                    "gte" => $date_start->format(ElasticObject::DATE_TIME_FORMAT),
                    "lte" => $date_end->format(ElasticObject::DATE_TIME_FORMAT),
                ],
            ],
        ];
    }
}
