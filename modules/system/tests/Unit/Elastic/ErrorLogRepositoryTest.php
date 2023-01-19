<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Unit\Elastic;

use Ox\Core\Elastic\ElasticObjectManager;
use Ox\Core\Elastic\QueryBuilder\ElasticQueryBuilder;
use Ox\Mediboard\System\Elastic\ErrorLog;
use Ox\Mediboard\System\Elastic\ErrorLogRepository;
use Ox\Tests\OxUnitTestCase;

class ErrorLogRepositoryTest extends OxUnitTestCase
{
    private const DATE_PATTERN = "/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}.\d{6} \w+\/\w+ \([+\-]\d{2}:\d{2}\)/";
    private const DATE_MIN     = "2022-05-03 10:56:41";
    private const DATE_MAX     = "2022-05-18 18:26:41";
    /**
     * @var ErrorLogRepository
     */
    private static $error_log_repository;

    public static function setUpBeforeClass(): void
    {
        ElasticObjectManager::init(new ErrorLog());
        self::$error_log_repository = new ErrorLogRepository();
    }

    public static function tearDownAfterClass(): void
    {
        ElasticObjectManager::getInstance()->clear(new ErrorLog());
    }

    /**
     * @param ElasticQueryBuilder $input
     * @param array               $expected
     *
     * @dataProvider aggregationSignatureDataProvider
     * @return void
     */
    public function testAddAggregationSignature(ElasticQueryBuilder $input, array $expected): void
    {
        $aggregation = self::$error_log_repository->addAggregation("signature", $input);

        self::assertEquals($expected, $aggregation->build());
    }

    /**
     * @param ElasticQueryBuilder $input
     * @param array               $expected
     *
     * @dataProvider aggregationSimilarDataProvider
     * @return void
     */
    public function testAddAggregationSimilar(ElasticQueryBuilder $input, array $expected): void
    {
        $aggregation = self::$error_log_repository->addAggregation("similar", $input);

        self::assertEquals($expected, $aggregation->build());
    }

    /**
     * @param ElasticQueryBuilder $input
     * @param array               $expected
     *
     * @dataProvider aggregationNoneDataProvider
     * @return void
     */
    public function testAddAggregationNone(ElasticQueryBuilder $input, array $expected): void
    {
        $aggregation = self::$error_log_repository->addAggregation("", $input);

        self::assertEquals($expected, $aggregation->build());
    }

    /**
     * @param array $data
     * @param array $robot
     * @param int   $from
     * @param int   $size
     * @param array $expected
     *
     * @dataProvider buildQueryDataProvider
     * @return void
     */
    public function testBuildQueryFromFormData(array $data, array $robot, int $from, int $size, array $expected): void
    {
        $query = self::$error_log_repository->buildQueryFromFormData($data, $robot, $from, $size);

        self::assertEquals($expected, $query->build());
    }

    /**
     * @param array $data
     * @param array $robot
     * @param int   $from
     * @param int   $size
     *
     * @dataProvider buildQueryDataDateProvider
     * @return void
     */
    public function testBuildQueryFromFormDataDateManagement(array $data, array $robot, int $from, int $size): void
    {
        $query = self::$error_log_repository->buildQueryFromFormData($data, $robot, $from, $size);

        $request = $query->build();
        $range   = $request["body"]["query"]["bool"]["must"][0]["range"];
        self::assertArrayHasKey("date", $range);

        if (array_key_exists("datetime_min", $data)) {
            self::assertArrayHasKey("gte", $range["date"]);
            self::assertMatchesRegularExpression(
                self::DATE_PATTERN,
                $range["date"]["gte"]
            );
        }
        if (array_key_exists("datetime_max", $data)) {
            self::assertArrayHasKey("lte", $range["date"]);
            self::assertMatchesRegularExpression(
                self::DATE_PATTERN,
                $range["date"]["lte"]
            );
        }
    }

    public function aggregationSignatureDataProvider(): array
    {
        $basic = new ElasticQueryBuilder("test");
        $basic->setSize(20)
            ->setFrom(300)
            ->addSort("count", "gte");

        return [
            "empty query builder" => [
                new ElasticQueryBuilder("test"),
                [
                    "index" => "test",
                    "size"  => 0,
                    "from"  => 0,
                    "body"  => [
                        "aggs" => [
                            "total"          => [
                                "cardinality" => [
                                    "field" => "signature_hash.keyword",
                                ],
                            ],
                            "signature_hash" => [
                                "terms" => [
                                    "field" => "signature_hash.keyword",
                                    "size"  => 2147483647,
                                ],
                                "aggs"  => [
                                    "total_count"        => [
                                        "sum" => [
                                            "field" => "count",
                                        ],
                                    ],
                                    "bucket_sort"        => [
                                        "bucket_sort" => [
                                            "size" => 10,
                                            "from" => 0,
                                            "sort" => [
                                                "date_max" => "desc",
                                            ],
                                        ],
                                    ],
                                    "log"                => [
                                        "top_hits" => [
                                            "size" => 1,
                                        ],
                                    ],
                                    "date_min"           => [
                                        "min" => [
                                            "field" => "date",
                                        ],
                                    ],
                                    "date_max"           => [
                                        "max" => [
                                            "field" => "date",
                                        ],
                                    ],
                                    "similar_ids"        => [
                                        "terms" => [
                                            "field" => "_id",
                                            "size"  => 500,
                                        ],
                                    ],
                                    "similar_user_ids"   => [
                                        "terms" => [
                                            "field" => "user_id",
                                            "size"  => 10,
                                        ],
                                    ],
                                    "similar_server_ips" => [
                                        "terms" => [
                                            "field" => "server_ip.keyword",
                                            "size"  => 10,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            "basic query builder" => [
                $basic,
                [
                    "index" => "test",
                    "size"  => 0,
                    "from"  => 0,
                    "body"  => [
                        "sort" => [
                            [
                                "count" => [
                                    "order" => "gte",
                                ],
                            ],
                        ],
                        "aggs" => [
                            "total"          => [
                                "cardinality" => [
                                    "field" => "signature_hash.keyword",
                                ],
                            ],
                            "signature_hash" => [
                                "terms" => [
                                    "field" => "signature_hash.keyword",
                                    "size"  => 2147483647,
                                ],
                                "aggs"  => [
                                    "total_count"        => [
                                        "sum" => [
                                            "field" => "count",
                                        ],
                                    ],
                                    "bucket_sort"        => [
                                        "bucket_sort" => [
                                            "size" => 20,
                                            "from" => 300,
                                            "sort" => [
                                                "total_count" => "desc",
                                            ],
                                        ],
                                    ],
                                    "log"                => [
                                        "top_hits" => [
                                            "size" => 1,
                                        ],
                                    ],
                                    "date_min"           => [
                                        "min" => [
                                            "field" => "date",
                                        ],
                                    ],
                                    "date_max"           => [
                                        "max" => [
                                            "field" => "date",
                                        ],
                                    ],
                                    "similar_ids"        => [
                                        "terms" => [
                                            "field" => "_id",
                                            "size"  => 500,
                                        ],
                                    ],
                                    "similar_user_ids"   => [
                                        "terms" => [
                                            "field" => "user_id",
                                            "size"  => 10,
                                        ],
                                    ],
                                    "similar_server_ips" => [
                                        "terms" => [
                                            "field" => "server_ip.keyword",
                                            "size"  => 10,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function aggregationSimilarDataProvider(): array
    {
        $basic = new ElasticQueryBuilder("test");
        $basic->setSize(20)
            ->setFrom(300)
            ->addSort("count", "gte");

        return [
            "empty query builder" => [
                new ElasticQueryBuilder("test"),
                [
                    "index" => "test",
                    "size"  => 0,
                    "from"  => 0,
                    "body"  => [
                        "aggs" => [
                            "total"          => [
                                "cardinality" => [
                                    "script" => "doc['text.keyword'].value + '#' +
                         doc['stacktrace.keyword'].value + '#' +
                          doc['param_GET.keyword'].value + '#' +
                           doc['param_POST.keyword'].value",
                                ],
                            ],
                            "signature_hash" => [
                                "multi_terms" => [
                                    "size"  => 2147483647,
                                    "terms" => [
                                        [
                                            "field" => "text.keyword",
                                        ],
                                        [
                                            "field" => "stacktrace.keyword",
                                        ],
                                        [
                                            "field" => "param_GET.keyword",
                                        ],
                                        [
                                            "field" => "param_POST.keyword",
                                        ],
                                    ],
                                ],
                                "aggs"        => [
                                    "total_count"        => [
                                        "sum" => [
                                            "field" => "count",
                                        ],
                                    ],
                                    "bucket_sort"        => [
                                        "bucket_sort" => [
                                            "size" => 10,
                                            "from" => 0,
                                            "sort" => [
                                                "date_max" => "desc",
                                            ],
                                        ],
                                    ],
                                    "log"                => [
                                        "top_hits" => [
                                            "size" => 1,
                                        ],
                                    ],
                                    "date_min"           => [
                                        "min" => [
                                            "field" => "date",
                                        ],
                                    ],
                                    "date_max"           => [
                                        "max" => [
                                            "field" => "date",
                                        ],
                                    ],
                                    "similar_ids"        => [
                                        "terms" => [
                                            "field" => "_id",
                                            "size"  => 500,
                                        ],
                                    ],
                                    "similar_user_ids"   => [
                                        "terms" => [
                                            "field" => "user_id",
                                            "size"  => 10,
                                        ],
                                    ],
                                    "similar_server_ips" => [
                                        "terms" => [
                                            "field" => "server_ip.keyword",
                                            "size"  => 10,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            "basic query builder" => [
                $basic,
                [
                    "index" => "test",
                    "size"  => 0,
                    "from"  => 0,
                    "body"  => [
                        "sort" => [
                            [
                                "count" => [
                                    "order" => "gte",
                                ],
                            ],
                        ],
                        "aggs" => [
                            "total"          => [
                                "cardinality" => [
                                    "script" => "doc['text.keyword'].value + '#' +
                         doc['stacktrace.keyword'].value + '#' +
                          doc['param_GET.keyword'].value + '#' +
                           doc['param_POST.keyword'].value",
                                ],
                            ],
                            "signature_hash" => [
                                "multi_terms" => [
                                    "size"  => 2147483647,
                                    "terms" => [
                                        [
                                            "field" => "text.keyword",
                                        ],
                                        [
                                            "field" => "stacktrace.keyword",
                                        ],
                                        [
                                            "field" => "param_GET.keyword",
                                        ],
                                        [
                                            "field" => "param_POST.keyword",
                                        ],
                                    ],
                                ],
                                "aggs"        => [
                                    "total_count"        => [
                                        "sum" => [
                                            "field" => "count",
                                        ],
                                    ],
                                    "bucket_sort"        => [
                                        "bucket_sort" => [
                                            "size" => 20,
                                            "from" => 300,
                                            "sort" => [
                                                "total_count" => "desc",
                                            ],
                                        ],
                                    ],
                                    "log"                => [
                                        "top_hits" => [
                                            "size" => 1,
                                        ],
                                    ],
                                    "date_min"           => [
                                        "min" => [
                                            "field" => "date",
                                        ],
                                    ],
                                    "date_max"           => [
                                        "max" => [
                                            "field" => "date",
                                        ],
                                    ],
                                    "similar_ids"        => [
                                        "terms" => [
                                            "field" => "_id",
                                            "size"  => 500,
                                        ],
                                    ],
                                    "similar_user_ids"   => [
                                        "terms" => [
                                            "field" => "user_id",
                                            "size"  => 10,
                                        ],
                                    ],
                                    "similar_server_ips" => [
                                        "terms" => [
                                            "field" => "server_ip.keyword",
                                            "size"  => 10,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function aggregationNoneDataProvider(): array
    {
        $basic = new ElasticQueryBuilder("test");
        $basic->setSize(20)
            ->setFrom(300)
            ->addSort("count", "gte");

        return [
            "empty query builder" => [
                new ElasticQueryBuilder("test"),
                [
                    "index" => "test",
                    "size"  => 0,
                    "from"  => 0,
                    "body"  => [
                        "aggs" => [],
                    ],
                ],
            ],
            "basic query builder" => [
                $basic,
                [
                    "index" => "test",
                    "size"  => 0,
                    "from"  => 0,
                    "body"  => [
                        "sort" => [
                            [
                                "count" => [
                                    "order" => "gte",
                                ],
                            ],
                        ],
                        "aggs" => [],
                    ],
                ],
            ],
        ];
    }

    public function buildQueryDataProvider(): array
    {
        $index_name = (new ErrorLog())->getSettings()->getAliasName();

        return [
            "default"                       => [
                [],
                [],
                0,
                10,
                [
                    "index" => $index_name,
                    "size"  => 10,
                    "from"  => 0,
                ],
            ],
            "text filter and error type"    => [
                [
                    "text"       => "test",
                    "error_type" => ["warning", "notice"],
                ],
                [],
                0,
                10,
                [
                    "index" => $index_name,
                    "size"  => 10,
                    "from"  => 0,
                    "body"  => [
                        "query" => [
                            "bool" => [
                                "must" => [
                                    [
                                        "terms" => [
                                            "type" => [
                                                0,
                                                1,
                                            ],
                                        ],
                                    ],
                                    [
                                        "match_phrase" => [
                                            "text" => [
                                                "query" => "test",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            "only human and order by date"  => [
                [
                    "order_by" => "date",
                    "human"    => true,
                    "robot"    => false,
                ],
                [16, 17, 18, 1001],
                0,
                10,
                [
                    "index" => $index_name,
                    "size"  => 10,
                    "from"  => 0,
                    "body"  => [
                        "sort"  => [
                            [
                                "date" => [
                                    "order" => "desc",
                                ],
                            ],
                            [
                                "count" => [
                                    "order" => "desc",
                                ],
                            ],
                        ],
                        "query" => [
                            "bool" => [
                                "must_not" => [
                                    [
                                        "terms" => [
                                            "user_id" => [
                                                16,
                                                17,
                                                18,
                                                1001,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            "only robot and order by count" => [
                [
                    "order_by" => "quantity",
                    "human"    => false,
                    "robot"    => true,
                ],
                [16, 17, 18, 1001],
                0,
                10,
                [
                    "index" => $index_name,
                    "size"  => 10,
                    "from"  => 0,
                    "body"  => [
                        "sort"  => [
                            [
                                "count" => [
                                    "order" => "desc",
                                ],
                            ],
                            [
                                "date" => [
                                    "order" => "desc",
                                ],
                            ],
                        ],
                        "query" => [
                            "bool" => [
                                "must" => [
                                    [
                                        "terms" => [
                                            "user_id" => [
                                                16,
                                                17,
                                                18,
                                                1001,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            "filter by user and server ip"  => [
                [
                    "user_id"   => 27,
                    "server_ip" => "127.0.0.1",
                ],
                [],
                0,
                10,
                [
                    "index" => $index_name,
                    "size"  => 10,
                    "from"  => 0,
                    "body"  => [
                        "query" => [
                            "bool" => [
                                "must" => [
                                    [
                                        "match_phrase" => [
                                            "user_id" => [
                                                "query" => "27",
                                            ],
                                        ],
                                    ],
                                    [
                                        "match_phrase" => [
                                            "server_ip" => [
                                                "query" => "127.0.0.1",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function buildQueryDataDateProvider(): array
    {
        return [
            "date min and max" => [
                [
                    "datetime_min" => self::DATE_MIN,
                    "datetime_max" => self::DATE_MAX,
                ],
                [],
                0,
                10,
            ],
            "date max"         => [
                [
                    "datetime_max" => self::DATE_MAX,
                ],
                [],
                0,
                10,
            ],
            "date min"         => [
                [
                    "datetime_min" => self::DATE_MIN,
                ],
                [],
                0,
                10,
            ],
        ];
    }
}
