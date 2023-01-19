<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Request;

use Ox\Core\Api\Exceptions\ApiRequestException;
use Ox\Core\Api\Request\Filter;
use Ox\Core\Api\Request\RequestFilter;
use Ox\Core\CSQLDataSource;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

class RequestFilterTest extends OxUnitTestCase
{
    /** @var RequestFilter */
    private $req_filter;

    /** @var Filter[] */
    private $filters;

    /**
     * @param array $query
     *
     * @dataProvider getSqlFilterProvider
     * @throws ApiRequestException
     */
    public function testGetSqlFilter(array $query): void
    {
        $req = new Request($query['query']);

        $filter = new RequestFilter($req);
        $where  = $filter->getSqlFilters(CSQLDataSource::get('std'));
        $this->assertEquals($query['expected'], $where);
    }

    /**
     * @throws ApiRequestException
     */
    public function testGetSqlFilterOperatorDoesNotExists(): void
    {
        $req = new Request(
            [
                RequestFilter::QUERY_KEYWORD_FILTER => 'bar' . RequestFilter::FILTER_PART_SEPARATOR
                    . RequestFilter::FILTER_IS_NOT_NULL . RequestFilter::FILTER_SEPARATOR . 'foo'
                    . RequestFilter::FILTER_PART_SEPARATOR . RequestFilter::FILTER_LESS_OR_EQUAL
                    . '5' . RequestFilter::FILTER_PART_SEPARATOR . '500' . RequestFilter::FILTER_SEPARATOR . 'arg2'
                    . RequestFilter::FILTER_PART_SEPARATOR . RequestFilter::FILTER_DO_NOT_CONTAINS . 'hey'
                    . RequestFilter::FILTER_PART_SEPARATOR . RequestFilter::FILTER_NOT_EQUAL
                    . RequestFilter::FILTER_PART_SEPARATOR . '   ' . RequestFilter::FILTER_DO_NOT_BEGIN_WITH,
            ]
        );

        $filter = new RequestFilter($req);
        $this->expectException(ApiRequestException::class);
        $filter->getSqlFilters(CSQLDataSource::get('std'));
    }

    /**
     * @throws ApiRequestException
     */
    public function testgetSqlFilterWithoutKey(): void
    {
        $filter = new Filter('', 'equal', ['test']);

        $req        = new Request();
        $req_filter = new RequestFilter($req);
        $req_filter->addFilter($filter);

        $this->expectException(ApiRequestException::class);
        $req_filter->getSqlFilters(CSQLDataSource::get('std'));
    }

    /**
     * Test the iterator functions of RequestFilter
     */
    public function testRequestFilterIterator(): void
    {
        $this->prepareRequestFilter();
        $this->assertIterableCount($this->req_filter, $this->filters, 5);
    }

    /**
     * Test count
     *
     * @return void
     */
    public function testRequestFilterCount(): void
    {
        $this->prepareRequestFilter();
        $this->assertCountableCount($this->req_filter, 5);
    }

    /**
     * @throws ApiRequestException
     */
    public function testRemoveFilterReindex(): void
    {
        $this->prepareRequestFilter();

        $this->req_filter->removeFilter(2, true);
        $this->assertCount(4, $this->req_filter);
        $this->assertEquals([0, 1, 2, 3], array_keys($this->req_filter->getFilters()));
    }

    /**
     * @throws ApiRequestException
     */
    public function testRemoveFilterIndexDoesNotExists(): void
    {
        $this->prepareRequestFilter();
        $this->expectException(ApiRequestException::class);
        $this->req_filter->removeFilter(10);
    }

    /**
     * @return void
     */
    public function testExistingFilters(): void
    {
        $req        = new Request();
        $req_filter = new RequestFilter($req);
        $this->assertEquals(
            [
                RequestFilter::FILTER_EQUAL             => '= ?',
                RequestFilter::FILTER_NOT_EQUAL         => '!= ?',
                RequestFilter::FILTER_LESS              => '< ?',
                RequestFilter::FILTER_LESS_OR_EQUAL     => '<= ?',
                RequestFilter::FILTER_GREATER           => '> ?',
                RequestFilter::FILTER_GREATER_OR_EQUAL  => '>= ?',
                RequestFilter::FILTER_BEGIN_WITH        => '?%',
                RequestFilter::FILTER_CONTAINS          => '%?%',
                RequestFilter::FILTER_END_WITH          => '%?',
                RequestFilter::FILTER_DO_NOT_BEGIN_WITH => '?%',
                RequestFilter::FILTER_DO_NOT_CONTAINS   => '%?%',
                RequestFilter::FILTER_DO_NOT_END_WITH   => '%?',
                RequestFilter::FILTER_IN                => 'IN ?',
                RequestFilter::FILTER_NOT_IN            => 'NOT ' . RequestFilter::FILTER_IN,
                RequestFilter::FILTER_IS_NULL           => 'IS NULL',
                RequestFilter::FILTER_IS_NOT_NULL       => 'IS NOT NULL',
                RequestFilter::FILTER_IS_EMPTY          => '= ""',
                RequestFilter::FILTER_IS_NOT_EMPTY      => '!= ""',
                RequestFilter::FILTER_STRICT_EQUAL      => '?',
            ],
            $req_filter->getExistingFilters()
        );
    }

    public function testGetFilterExists(): void
    {
        $this->prepareRequestFilter();
        $this->assertEquals(new Filter('test2', 'equal', ['value2']), $this->req_filter->getFilter('test2'));
    }

    public function testGetFilterExistsWithOperator(): void
    {
        $this->prepareRequestFilter();
        $filter = new Filter('test2', 'contains', ['value1']);
        $this->req_filter->addFilter($filter);
        $this->assertEquals($filter, $this->req_filter->getFilter('test2', 'contains'));
    }

    public function testGetFilterExistsWithMultipleOperator(): void
    {
        $this->prepareRequestFilter();
        $filter = new Filter('test2', 'contains', ['value1']);
        $this->req_filter->addFilter($filter);
        $this->assertEquals($filter, $this->req_filter->getFilter('test2', ['beginWith', 'contains']));
    }

    public function testGetFilterDoesNotExistsWithtOperator(): void
    {
        $this->prepareRequestFilter();
        $this->assertNull($this->req_filter->getFilter('test2', 'contains'));
    }

    public function testGetFilterDoesNotExist(): void
    {
        $this->prepareRequestFilter();
        $this->assertNull($this->req_filter->getFilter('test10'));
    }

    public function testGetFilterPosition(): void
    {
        $this->prepareRequestFilter();
        $this->assertEquals(1, $this->req_filter->getFilterPosition('test2'));
    }

    /**
     * @return string[][]
     */
    public function providerPrepareFieldName(): array
    {
        $part = "= 'foo'";

        return [
            ['field_name' => 'user_id', 'expected' => "`user_id` $part"],
            ['field_name' => 'users.user_id', 'expected' => "`users`.`user_id` $part"],
            ['field_name' => 'users.user_id.foo', 'expected' => "`users`.`user_id.foo` $part"],
        ];
    }

    /**
     * @dataProvider providerPrepareFieldName
     *
     * @param string $field_name
     * @param string $expected
     *
     * @throws ApiRequestException
     */
    public function testPrepareFieldName(string $field_name, string $expected): void
    {
        $ds         = CSQLDataSource::get('std');
        $req_filter = new RequestFilter(new Request());
        $filter     = new Filter($field_name, RequestFilter::FILTER_EQUAL, 'foo');

        $this->assertEquals($expected, $req_filter->getSqlFilter($filter, $ds));
    }

    /**
     * @return void
     */
    private function prepareRequestFilter(): void
    {
        $this->req_filter = new RequestFilter(new Request());
        $this->filters    = [
            new Filter('test1', 'equal', ['value1']),
            new Filter('test2', 'equal', ['value2']),
            new Filter('test3', 'equal', ['value3']),
            new Filter('test4', 'equal', ['value4']),
            new Filter('test5', 'equal', ['value5']),
        ];

        foreach ($this->filters as $_filter) {
            $this->req_filter->addFilter($_filter);
        }
    }


    /**
     * @return array
     */
    public function getSqlFilterProvider(): array
    {
        return [
            'filterEqual' => [
                [
                    'query'    => [
                        RequestFilter::QUERY_KEYWORD_FILTER => 'test_bool' . RequestFilter::FILTER_PART_SEPARATOR
                            . RequestFilter::FILTER_EQUAL . RequestFilter::FILTER_PART_SEPARATOR . '1',
                    ],
                    'expected' => [
                        "`test_bool` = '1'",
                    ],
                ],
            ],

            'strictEqual' => [
                [
                    'query'    => [
                        RequestFilter::QUERY_KEYWORD_FILTER => 'test_equal' . RequestFilter::FILTER_PART_SEPARATOR
                            . RequestFilter::FILTER_STRICT_EQUAL . RequestFilter::FILTER_PART_SEPARATOR . 'Foo',
                    ],
                    'expected' => [
                        "`test_equal` LIKE BINARY 'Foo'",
                    ],
                ],
            ],

            'filterBeginWith' => [
                [
                    'query'    => [
                        RequestFilter::QUERY_KEYWORD_FILTER => 'foo' . RequestFilter::FILTER_PART_SEPARATOR
                            . RequestFilter::FILTER_BEGIN_WITH . RequestFilter::FILTER_PART_SEPARATOR . 'test',
                    ],
                    'expected' => [
                        "`foo` LIKE 'test%'",
                    ],
                ],
            ],

            'filterDoNotContains' => [
                [
                    'query'    => [
                        RequestFilter::QUERY_KEYWORD_FILTER => 'foo' . RequestFilter::FILTER_PART_SEPARATOR
                            . RequestFilter::FILTER_DO_NOT_CONTAINS . RequestFilter::FILTER_PART_SEPARATOR . 'test',
                    ],
                    'expected' => [
                        "`foo` NOT LIKE '%test%'",
                    ],
                ],
            ],

            'filterInNotIn' => [
                [
                    'query'    => [
                        RequestFilter::QUERY_KEYWORD_FILTER => 'bar' . RequestFilter::FILTER_PART_SEPARATOR
                            . RequestFilter::FILTER_IN . RequestFilter::FILTER_PART_SEPARATOR . 'test'
                            . RequestFilter::FILTER_PART_SEPARATOR . '0' . RequestFilter::FILTER_PART_SEPARATOR . '1'
                            . RequestFilter::FILTER_PART_SEPARATOR . 'hello' . RequestFilter::FILTER_PART_SEPARATOR
                            . RequestFilter::FILTER_IN . RequestFilter::FILTER_SEPARATOR . 'foo'
                            . RequestFilter::FILTER_PART_SEPARATOR . RequestFilter::FILTER_NOT_IN
                            . RequestFilter::FILTER_PART_SEPARATOR . 'toto' . RequestFilter::FILTER_PART_SEPARATOR
                            . 'hello',
                    ],
                    'expected' => [
                        "`bar` IN ('test', '0', '1', 'hello', 'in')",
                        "`foo` NOT IN ('toto', 'hello')",
                    ],
                ],
            ],

            'filterIsNull' => [
                [
                    'query'    => [
                        RequestFilter::QUERY_KEYWORD_FILTER => 'bar' . RequestFilter::FILTER_PART_SEPARATOR
                            . RequestFilter::FILTER_IS_NULL,
                    ],
                    'expected' => [
                        "`bar` IS NULL",
                    ],
                ],
            ],

            'filterMultiIgnoreLast' => [
                [
                    'query'    => [
                        RequestFilter::QUERY_KEYWORD_FILTER => 'bar' . RequestFilter::FILTER_PART_SEPARATOR
                            . RequestFilter::FILTER_IS_NOT_NULL . RequestFilter::FILTER_SEPARATOR . ' foo'
                            . RequestFilter::FILTER_PART_SEPARATOR . RequestFilter::FILTER_LESS_OR_EQUAL
                            . RequestFilter::FILTER_PART_SEPARATOR . '500' . RequestFilter::FILTER_SEPARATOR
                            . '     arg2    ' . RequestFilter::FILTER_PART_SEPARATOR
                            . RequestFilter::FILTER_DO_NOT_CONTAINS . RequestFilter::FILTER_PART_SEPARATOR
                            . RequestFilter::FILTER_NOT_EQUAL . RequestFilter::FILTER_PART_SEPARATOR . '   '
                            . RequestFilter::FILTER_DO_NOT_BEGIN_WITH,

                    ],
                    'expected' => [
                        "`bar` IS NOT NULL",
                        "`foo` <= '500'",
                        "`arg2` NOT LIKE '%notEqual%'",
                    ],
                ],
            ],

            'filterEmptyArguments' => [
                [
                    'query'    => [
                        RequestFilter::QUERY_KEYWORD_FILTER => 'bar' . RequestFilter::FILTER_PART_SEPARATOR
                            . RequestFilter::FILTER_IN . RequestFilter::FILTER_SEPARATOR . 'toto'
                            . RequestFilter::FILTER_PART_SEPARATOR . RequestFilter::FILTER_NOT_EQUAL
                            . RequestFilter::FILTER_PART_SEPARATOR . RequestFilter::FILTER_SEPARATOR . 'string'
                            . RequestFilter::FILTER_PART_SEPARATOR . RequestFilter::FILTER_NOT_EQUAL
                            . RequestFilter::FILTER_SEPARATOR . 'bar2' . RequestFilter::FILTER_PART_SEPARATOR
                            . RequestFilter::FILTER_NOT_IN . RequestFilter::FILTER_PART_SEPARATOR
                            . RequestFilter::FILTER_SEPARATOR . 'again' . RequestFilter::FILTER_PART_SEPARATOR
                            . RequestFilter::FILTER_END_WITH . RequestFilter::FILTER_PART_SEPARATOR
                            . RequestFilter::FILTER_SEPARATOR . 'toto' . RequestFilter::FILTER_PART_SEPARATOR
                            . RequestFilter::FILTER_IS_NOT_EMPTY,

                    ],
                    'expected' => [
                        '`toto` != ""',
                    ],
                ],
            ],

            'filterEscape' => [
                [
                    'query'    => [
                        RequestFilter::QUERY_KEYWORD_FILTER => 'fo`o' . RequestFilter::FILTER_PART_SEPARATOR
                            . RequestFilter::FILTER_EQUAL . RequestFilter::FILTER_PART_SEPARATOR . '5'
                            . RequestFilter::FILTER_SEPARATOR . 'ba\'r' . RequestFilter::FILTER_PART_SEPARATOR
                            . RequestFilter::FILTER_CONTAINS . RequestFilter::FILTER_PART_SEPARATOR
                            . 'te`s\'t',

                    ],
                    'expected' => [
                        "`foo` = '5'",
                        "`ba'r` LIKE '%te`s\\'t%'",
                    ],
                ],
            ],

            'filterUrlDecode' => [
                [
                    'query'    => [
                        RequestFilter::QUERY_KEYWORD_FILTER => 'fo%20o' . RequestFilter::FILTER_PART_SEPARATOR
                            . RequestFilter::FILTER_CONTAINS . RequestFilter::FILTER_PART_SEPARATOR . 'test%2ehaha',

                    ],
                    'expected' => [
                        "`fo o` LIKE '%test.haha%'",
                    ],
                ],
            ],
        ];
    }
}
