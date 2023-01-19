<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Request;

use Ox\Core\Api\Request\RequestLimit;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

class RequestLimitTest extends OxUnitTestCase
{
    /**
     * @param array $query_content
     * @param int   $expected_offset
     * @param int   $expected_limit
     *
     * @dataProvider limitOkProvider
     */
    public function testLimitOk(array $query_content, $expected_offset, $expected_limit)
    {
        $req = new Request($query_content);

        $req_limit = new RequestLimit($req);
        $this->assertEquals($expected_offset, $req_limit->getOffset());
        $this->assertEquals($expected_limit, $req_limit->getLimit());
        $this->assertEquals("{$expected_offset},{$expected_limit}", $req_limit->getSqlLimit());
    }

    /**
     * Limit in query
     */
    public function testLimitInQuery()
    {
        $req       = new Request([RequestLimit::QUERY_KEYWORD_LIMIT => 20]);
        $req_limit = new RequestLimit($req);
        $this->assertTrue($req_limit->isInQuery());

        $req       = new Request();
        $req_limit = new RequestLimit($req);
        $this->assertFalse($req_limit->isInQuery());
    }

    /**
     * @return array
     */
    public function limitOkProvider()
    {
        return [
            'noLimit'             => [
                [],
                RequestLimit::OFFSET_DEFAULT,
                RequestLimit::LIMIT_DEFAULT,
            ],
            'onlyOffset'          => [
                [RequestLimit::QUERY_KEYWORD_OFFSET => 10],
                10,
                RequestLimit::LIMIT_DEFAULT,
            ],
            'onlyLimit'           => [
                [RequestLimit::QUERY_KEYWORD_LIMIT => 10],
                RequestLimit::OFFSET_DEFAULT,
                10,
            ],
            'offsetAndLimit'      => [
                [RequestLimit::QUERY_KEYWORD_LIMIT => 10, RequestLimit::QUERY_KEYWORD_OFFSET => 20],
                20,
                10,
            ],
            'limitGreaterThanMax' => [
                [RequestLimit::QUERY_KEYWORD_LIMIT => RequestLimit::LIMIT_MAX * 10],
                RequestLimit::OFFSET_DEFAULT,
                RequestLimit::LIMIT_MAX,
            ],
        ];
    }
}
