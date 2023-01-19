<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Request;

use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Exceptions\ApiRequestException;
use Ox\Core\Api\Request\RequestSort;
use Ox\Core\Api\Request\Sort;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests for the RequestSort utility class.
 */
class RequestSortTest extends OxUnitTestCase
{
    /**
     * @param string $query_content
     * @param array  $expected
     *
     * @dataProvider sortGetFieldsOkProvider
     * @throws ApiException
     */
    public function testSortGetFieldsOk(?string $query_content, $expected)
    {
        $req = new Request([RequestSort::QUERY_KEYWORD_SORT => $query_content]);

        $req_sort = new RequestSort($req);
        $this->assertEquals($expected, $req_sort->getFields());
    }

    /**
     * Test throw exception
     */
    public function testSortGetFieldsKo()
    {
        $req = new Request([RequestSort::QUERY_KEYWORD_SORT => 'foo bar,+toto']);
        $this->expectException(ApiRequestException::class);
        new RequestSort($req);
    }


    /**
     * @param      $query_content
     * @param      $expected
     * @param null $default
     *
     * @throws ApiRequestException
     *
     * @dataProvider sortGetSqlOrderByProvider
     */
    public function testSortGetSqlOrderBy($query_content, $expected, $default = null)
    {
        $req = new Request([RequestSort::QUERY_KEYWORD_SORT => $query_content]);

        $req_sort = new RequestSort($req);
        $this->assertEquals($expected, $req_sort->getSqlOrderBy($default));
    }

    /**
     * @return array
     */
    public function sortGetFieldsOkProvider()
    {
        return [
            'sortNull'       => [
                null,
                [],
            ],
            'sortEmpty'      => [
                '',
                [],
            ],
            'sortOneAsc'     => [
                '+test',
                [new Sort('test')],
            ],
            'sortOneDesc'    => [
                '-bar',
                [new Sort('bar', Sort::SORT_DESC)],
            ],
            'sortOneDefault' => [
                'foo',
                [new Sort('foo')],
            ],
            'sortMulti'      => [
                '-foo' . RequestSort::SORT_SEPARATOR . 'bar' . RequestSort::SORT_SEPARATOR . '+test' . RequestSort::SORT_SEPARATOR
                . '-toto',
                [
                    new Sort('foo', Sort::SORT_DESC),
                    new Sort('bar'),
                    new Sort('test'),
                    new Sort('toto', Sort::SORT_DESC),
                ],
            ],
            'sortAddSlashes' => [
                '-f\o' . RequestSort::SORT_SEPARATOR . 'ba"r' . RequestSort::SORT_SEPARATOR . "+tes\\t",
                [
                    new Sort('f\\\\o', Sort::SORT_DESC),
                    new Sort('ba\"r'),
                    new Sort('tes\\\\t'),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function sortGetSqlOrderByProvider()
    {
        return [
            'sortNullNoDefault'   => [
                null,
                null,
            ],
            'sortNullWithDefault' => [
                null,
                'foo',
                'foo',
            ],
            'sortOneField'        => [
                '-foo',
                '`foo` ' . Sort::SORT_DESC,
            ],
            'sortMultiFields'     => [
                '-foo' . RequestSort::SORT_SEPARATOR . '-bar' . RequestSort::SORT_SEPARATOR . 'toto',
                '`foo` ' . Sort::SORT_DESC . ',`bar` ' . Sort::SORT_DESC . ',`toto` ' . Sort::SORT_ASC,
            ],
        ];
    }
}
