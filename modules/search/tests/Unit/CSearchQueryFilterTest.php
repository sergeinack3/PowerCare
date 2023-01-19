<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Search\Tests\Unit;

use Ox\Mediboard\Search\CSearch;
use Ox\Mediboard\Search\CSearchQueryFilter;
use Ox\Tests\OxUnitTestCase;
use stdClass;

class CSearchQueryFilterTest extends OxUnitTestCase
{

    public function testToElasticWithSimpleTextAndTypes(): void
    {
        $query_filter = new CSearchQueryFilter();
        $query_filter->setWords('words')->setNamesTypes(['CConsultation', 'CCompteRendu']);

        $expected = [
            "explain"   => true,
            'size'      => 15,
            'from'      => 0,
            'query'     => [
                'bool' => [
                    'must' => [
                        [
                            'multi_match' => [
                                'query'    => 'words',
                                'fields'   => ['body', 'title'],
                                'operator' => 'AND',
                                'type'     => 'phrase',
                            ],
                        ],
                        [
                            'query_string' => [
                                'query'            => 'CConsultation CCompteRendu',
                                'fields'           => ['type'],
                                "analyze_wildcard" => true,
                                "default_operator" => "OR",
                            ],
                        ],
                    ],
                ],
            ],
            'highlight' => [
                'fields' => [
                    ['body' => new stdClass()],
                ],
            ],
        ];

        $this->assertEquals($expected, $query_filter->getBodyToElastic());
    }

    public function testToElasticWithComplexPatternAndAggregation(): void
    {
        $query_filter = new CSearchQueryFilter();
        $query_filter->setWords('(words AND word)')
            ->setFuzzySearch(true)
            ->setAggregation(true);

        $expected = [
            "explain"   => true,
            'size'      => 0,
            'from'      => 0,
            'query'     => [
                'bool' => [
                    'must' => [
                        [
                            'query_string' => [
                                'query'            => '(words AND word)',
                                'fields'           => ['body', 'title'],
                                'fuzziness'        => 'AUTO',
                                'default_operator' => 'AND',
                            ],
                        ],
                    ],
                ],
            ],
            'highlight' => [
                'fields' => [
                    ['body' => new stdClass()],
                ],
            ],
            'aggs'      => [
                'reference' => [
                    'terms' => [
                        'script' => "doc['object_ref_class.keyword'].value + '-' + doc['object_ref_id'].value",
                        "size"   => CSearch::REQUEST_AGG_SIZE,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $query_filter->getBodyToElastic());
    }

    public function testToElasticExactMatchAndDates(): void
    {
        $words = '"words to match exactly"';
        $words = addslashes($words); // CView::post adds slashes

        $query_builder = new CSearchQueryFilter();
        $query_builder->setWords($words);
        $query_builder->setDateMin('2021-02-10');
        $query_builder->setDateMax('2021-03-30');

        $expected = [
            "explain"   => true,
            'size'      => 15,
            'from'      => 0,
            'query'     => [
                'bool' => [
                    'must' => [
                        [
                            "query_string" => [
                                'query'    => '\"words to match exactly\"',
                                'fields'   => ['body', 'title'],
                                'fuzziness' => 0,
                                'default_operator' => 'AND'
                            ],
                        ],
                        [
                            'range' => [
                                'date' => ['gte' => '2021/02/10', 'lte' => '2021/03/30'],
                            ],
                        ],
                    ],
                ],
            ],
            'highlight' => [
                'fields' => [
                    ['body' => new stdClass()],
                ],
            ],
        ];

        $this->assertEquals($expected, $query_builder->getBodyToElastic());
    }

    public function testToElasticWithPatientAndUser(): void
    {
        $query_builder = new CSearchQueryFilter();
        $query_builder->setWords('');
        $query_builder->setPatientId(2);
        $query_builder->setSpecificUser(1);

        $expected = [
            "explain"   => true,
            'size'      => 15,
            'from'      => 0,
            'query'     => [
                'bool' => [
                    'must' => [
                        [
                            'match' => [
                                'patient_id' => 2,
                            ],
                        ],
                        [
                            'terms' => [
                                'author_id' => [1],
                            ],
                        ],
                    ],
                ],
            ],
            'highlight' => [
                'fields' => [
                    ['body' => new stdClass()],
                ],
            ],
        ];

        $this->assertEquals($expected, $query_builder->getBodyToElastic());
    }
}
