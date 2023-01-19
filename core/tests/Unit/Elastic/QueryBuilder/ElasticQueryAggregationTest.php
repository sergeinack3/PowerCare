<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Elastic\QueryBuilder;

use Ox\Core\Elastic\QueryBuilder\ElasticQueryAggregation;
use Ox\Tests\OxUnitTestCase;

class ElasticQueryAggregationTest extends OxUnitTestCase
{
    /**
     * @return void
     */
    public function testBuildQueryAggregation(): void
    {
        $data = $this->getAggregationData();

        foreach ($data as $_test_name => $_test_data) {
            self::assertEquals($_test_data[1], $_test_data[0]->build(), $_test_name);
        }
    }

    public function getAggregationData(): array
    {
        $aggregation  = new ElasticQueryAggregation();
        $aggregation1 = new ElasticQueryAggregation();
        $aggregation2 = new ElasticQueryAggregation();
        $aggregation3 = new ElasticQueryAggregation();
        $aggregation4 = new ElasticQueryAggregation();

        $sub_aggregation  = new ElasticQueryAggregation();
        $sub_aggregation1 = new ElasticQueryAggregation();

        return [
            "Empty Aggregation"                              => [
                $aggregation,
                [],
            ],
            "Aggregation on terms and cardinality"           => [
                $aggregation1->addCardinality("total", "text")
                    ->addTerms("aggregation_text", "text", 20),
                [
                    "total"            => [
                        "cardinality" => [
                            "field" => "text",
                        ],
                    ],
                    "aggregation_text" => [
                        "terms" => [
                            "field" => "text",
                            "size"  => 20,
                        ],
                    ],
                ],
            ],
            "Aggregation stats on field"                     => [
                $aggregation2->addMax("max_field", "count")
                    ->addMin("min_field", "count")
                    ->addSum("sum_field", "count"),
                [
                    "max_field" => [
                        "max" => [
                            "field" => "count",
                        ],
                    ],
                    "min_field" => [
                        "min" => [
                            "field" => "count",
                        ],
                    ],
                    "sum_field" => [
                        "sum" => [
                            "field" => "count",
                        ],
                    ],
                ],
            ],
            "Aggregation on terms with pagination"           => [
                // Aggregate by terms with max int because
                $aggregation3->addTerms("aggregation", "hash", 2147483647)
                    ->addSubAggregation(
                        "aggregation",
                        $sub_aggregation->addBucketSort("pagination", 10, 0)
                    ),
                [
                    "aggregation" => [
                        "terms" => [
                            "field" => "hash",
                            "size"  => 2147483647,
                        ],
                        "aggs"  => [
                            "pagination" => [
                                "bucket_sort" => [
                                    "size" => 10,
                                    "from" => 0,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            "Aggregation on multi-terms and sub aggregation" => [
                // Aggregate by terms with max int because
                $aggregation4->addMultiTermsAggregation("aggregation", ["text", "hash"], 20)
                    ->addOrderOnAggregation("aggregation", "date_max", "desc")
                    ->addSubAggregation(
                        "aggregation",
                        $sub_aggregation1->addTopHit("data")
                            ->addMax("date_max", "date")
                    ),
                [
                    "aggregation" => [
                        "multi_terms" => [
                            "terms" => [
                                [
                                    "field" => "text",
                                ],
                                [
                                    "field" => "hash",
                                ],
                            ],
                            "order" => [
                                "date_max" => "desc",
                            ],
                            "size"  => 20,
                        ],
                        "aggs"        => [
                            "data"     => [
                                "top_hits" => [
                                    "size" => 1,
                                ],
                            ],
                            "date_max" => [
                                "max" => [
                                    "field" => "date",
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
