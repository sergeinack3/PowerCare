<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Elastic\QueryBuilder;

use Ox\Core\Elastic\QueryBuilder\ElasticQueryAggregation;
use Ox\Core\Elastic\QueryBuilder\ElasticQueryBuilder;
use Ox\Core\Elastic\QueryBuilder\Filters\AbstractElasticQueryFilter;
use Ox\Core\Elastic\QueryBuilder\Filters\ElasticQueryMust;
use Ox\Core\Elastic\QueryBuilder\Filters\ElasticQueryShould;
use Ox\Tests\OxUnitTestCase;

class ElasticQueryBuilderTest extends OxUnitTestCase
{
    /**
     * @return void
     */
    public function testBuildQueryBuilder(): void
    {
        $data = $this->getQueryData();

        foreach ($data as $_test_name => $_test_data) {
            self::assertEquals($_test_data[1], $_test_data[0]->build(), $_test_name);
        }
    }

    public function getQueryData(): array
    {
        $query  = new ElasticQueryBuilder("query");
        $query1 = new ElasticQueryBuilder("query1");
        $query2 = new ElasticQueryBuilder("query2");
        $query3 = new ElasticQueryBuilder("query3");
        $query4 = new ElasticQueryBuilder("query4");
        $query5 = new ElasticQueryBuilder("query5");
        $query6 = new ElasticQueryBuilder("query6");
        $query7 = new ElasticQueryBuilder("query7");
        $query8 = new ElasticQueryBuilder("query8");

        $bool  = AbstractElasticQueryFilter::bool();
        $bool1 = AbstractElasticQueryFilter::bool();
        $bool2 = AbstractElasticQueryFilter::bool();
        $bool3 = AbstractElasticQueryFilter::bool();
        $bool4 = AbstractElasticQueryFilter::bool();

        $must = new ElasticQueryMust();
        $must->beIn("field_id", ["12", "13"]);
        $must1 = new ElasticQueryMust();
        $must1->beLike("text", "data_to_match");

        $should = new ElasticQueryShould();
        $should->beInBetween("date", "2022-01-01", "2022-12-31");

        $aggregation = new ElasticQueryAggregation();
        $aggregation->addTerms("aggregation_name", "text", 10);

        return [
            "Empty Query"                                                              => [
                $query,
                [
                    "index" => "query",
                ],
            ],
            "Empty Query changing index name"                                          => [
                $query8->setIndex("query8-test"),
                [
                    "index" => "query8-test",
                ],
            ],
            "Empty Query with limit and offset"                                        => [
                $query1->setSize(100)->setFrom(400),
                [
                    "index" => "query1",
                    "size"  => 100,
                    "from"  => 400,
                ],
            ],
            "Query with on terms"                                                      => [
                $query2->addFilter(AbstractElasticQueryFilter::terms("text", ["test", "toto"])),
                [
                    "index" => "query2",
                    "body"  => [
                        "query" => [
                            "terms" => [
                                "text" => [
                                    "test",
                                    "toto",
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            "Query with boolean (must and should)"                                     => [
                $query3->addFilter(
                    $bool->addMust($must)
                        ->addShould($should)
                ),
                [
                    "index" => "query3",
                    "body"  => [
                        "query" => [
                            "bool" => [
                                "must"   => [
                                    [
                                        "terms" => [
                                            "field_id" => [
                                                "12",
                                                "13",
                                            ],
                                        ],
                                    ],
                                ],
                                "should" => [
                                    [
                                        "range" => [
                                            "date" => [
                                                "gte" => "2022-01-01",
                                                "lte" => "2022-12-31",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            "Query with boolean (must and should) and limit with minimum should match" => [
                $query4->setSize(5)->addFilter($bool1->addMust($must)->addShould($should)->addMinimumShouldMatch("1")),
                [
                    "index" => "query4",
                    "size"  => 5,
                    "body"  => [
                        "query" => [
                            "bool" => [
                                "minimum_should_match" => "1",
                                "must"                 => [
                                    [
                                        "terms" => [
                                            "field_id" => [
                                                "12",
                                                "13",
                                            ],
                                        ],
                                    ],
                                ],
                                "should"               => [
                                    [
                                        "range" => [
                                            "date" => [
                                                "gte" => "2022-01-01",
                                                "lte" => "2022-12-31",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            "Query with boolean (must) and limit and offset and sorting"               => [
                $query5->setSize(5)
                    ->setFrom(15)
                    ->addSort("text", "desc")
                    ->addFilter($bool2->addMust($must)),
                [
                    "index" => "query5",
                    "size"  => 5,
                    "from"  => 15,
                    "body"  => [
                        "sort"  => [
                            [
                                "text" => [
                                    "order" => "desc",
                                ],
                            ],
                        ],
                        "query" => [
                            "bool" => [
                                "must" => [
                                    [
                                        "terms" => [
                                            "field_id" => [
                                                "12",
                                                "13",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            "Query with boolean imbrication inside must"                               => [
                $query6->addFilter(
                    $bool3->addMust($must1->addFilter($bool4->addShould($should)))
                ),
                [
                    "index" => "query6",
                    "body"  => [
                        "query" => [
                            "bool" => [
                                "must" => [
                                    [
                                        "match_phrase" => [
                                            "text" => [
                                                "query" => "data_to_match",
                                            ],
                                        ],
                                    ],
                                    [
                                        "bool" => [
                                            "should" => [
                                                [
                                                    "range" => [
                                                        "date" => [
                                                            "gte" => "2022-01-01",
                                                            "lte" => "2022-12-31",
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            "Query with boolean-must with aggregation"                                 => [
                $query7->addFilter($bool2)->addAggregation($aggregation),
                [
                    "index" => "query7",
                    "body"  => [
                        "aggs"  => [
                            "aggregation_name" => [
                                "terms" => [
                                    "field" => "text",
                                    "size"  => 10,
                                ],
                            ],
                        ],
                        "query" => [
                            "bool" => [
                                "must" => [
                                    [
                                        "terms" => [
                                            "field_id" => [
                                                "12",
                                                "13",
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
}
