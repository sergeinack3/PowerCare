<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Elastic\QueryBuilder;

use Ox\Core\Elastic\QueryBuilder\Filters\AbstractElasticQueryFilter;
use Ox\Core\Elastic\QueryBuilder\RangeOperator;
use Ox\Tests\OxUnitTestCase;

class ElasticQueryFilterTest extends OxUnitTestCase
{
    /**
     * @return void
     */
    public function testBuildQueryFilters(): void
    {
        $data = $this->getFilterData();

        foreach ($data as $_test_name => $_test_data) {
            self::assertEquals($_test_data[1], $_test_data[0]->build(), $_test_name);
        }
    }

    public function getFilterData(): array
    {
        $bool         = AbstractElasticQueryFilter::bool();
        $terms        = AbstractElasticQueryFilter::terms("field_name", ["searched", "terms"]);
        $term         = AbstractElasticQueryFilter::term("field_name", "searched_term");
        $match        = AbstractElasticQueryFilter::match("field_name", "match a term");
        $match_phrase = AbstractElasticQueryFilter::matchPhrase("field_name", "match a phrase");
        $range        = AbstractElasticQueryFilter::range("field_name", RangeOperator::GREATER_THAN(), "12");

        $terms_complete = AbstractElasticQueryFilter::terms("field_name", ["searched", "terms"]);
        $terms_complete->setBoost(1.5)->setMinimumShouldMatch(1);
        $term_complete = AbstractElasticQueryFilter::term("field_name", "searched_term");
        $term_complete->setBoost(2.3);
        $match_complete = AbstractElasticQueryFilter::match("field_name", "match a term");
        $match_complete->setOperator("AND")
            ->setAutoGenerateSynonymsPhraseQuery(true)
            ->setFuzziness("3..5")
            ->setMaxExpansions(100)
            ->setMinimumShouldMatch("2");
        $match_phrase_complete = AbstractElasticQueryFilter::matchPhrase("field_name", "match a phrase");
        $match_phrase_complete->setOperator("OR")
            ->setAutoGenerateSynonymsPhraseQuery(false)
            ->setFuzziness(">5")
            ->setMaxExpansions(20)
            ->setMinimumShouldMatch("0");
        $range_complete = AbstractElasticQueryFilter::range("date", RangeOperator::GREATER_THAN(), "2022-01-01");
        $range_complete->setBoost(5)->setFormat("yyyy-MM-dd")->setRelation("INTERSECTS");

        return [
            "Empty Bool"            => [
                $bool,
                [],
            ],
            "Default terms"         => [
                $terms,
                [
                    "terms" => [
                        "field_name" => ["searched", "terms"],
                    ],
                ],
            ],
            "Default term"          => [
                $term,
                [
                    "term" => [
                        "field_name" => "searched_term",
                    ],
                ],
            ],
            "Default match"         => [
                $match,
                [
                    "match" => [
                        "field_name" => [
                            "query" => "match a term",
                        ],
                    ],
                ],
            ],
            "Default match_phrase"  => [
                $match_phrase,
                [
                    "match_phrase" => [
                        "field_name" => [
                            "query" => "match a phrase",
                        ],
                    ],
                ],
            ],
            "Default range"         => [
                $range,
                [
                    "range" => [
                        "field_name" => [
                            "gt" => "12",
                        ],
                    ],
                ],
            ],
            "Complete terms"        => [
                $terms_complete,
                [
                    "terms" => [
                        "field_name"           => ["searched", "terms"],
                        "minimum_should_match" => 1,
                        "boost"                => 1.5,
                    ],
                ],
            ],
            "Complete term"         => [
                $term_complete,
                [
                    "term" => [
                        "field_name" => "searched_term",
                        "boost"      => 2.3,
                    ],
                ],
            ],
            "Complete match"        => [
                $match_complete,
                [
                    "match" => [
                        "field_name" => [
                            "query"                               => "match a term",
                            "auto_generate_synonyms_phrase_query" => true,
                            "fuzziness"                           => "3..5",
                            "max_expansions"                      => 100,
                            "minimum_should_match"                => "2",
                            "operator"                            => "AND",
                        ],
                    ],
                ],
            ],
            "Complete match_phrase" => [
                $match_phrase_complete,
                [
                    "match_phrase" => [
                        "field_name" => [
                            "query"                               => "match a phrase",
                            "auto_generate_synonyms_phrase_query" => false,
                            "fuzziness"                           => ">5",
                            "max_expansions"                      => 20,
                            "minimum_should_match"                => "0",
                            "operator"                            => "OR",
                        ],
                    ],
                ],
            ],
            "Complete range"        => [
                $range_complete,
                [
                    "range" => [
                        "date" => [
                            "gt"       => "2022-01-01",
                            "boost"    => 5,
                            "format"   => "yyyy-MM-dd",
                            "relation" => "INTERSECTS",
                        ],
                    ],
                ],
            ],
        ];
    }
}
