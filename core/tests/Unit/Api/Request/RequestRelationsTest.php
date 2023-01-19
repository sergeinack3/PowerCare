<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Request;

use Ox\Core\Api\Request\RequestRelations;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

class RequestRelationsTest extends OxUnitTestCase
{
    /**
     * @param array $query_content
     * @param array $expected_includes
     * @param array $expected_excludes
     *
     * @dataProvider relationsProvider
     */
    public function testRelations(array $query_content, $expected_includes, $expected_excludes)
    {
        $req = new Request($query_content);

        $req_relations = new RequestRelations($req);
        $this->assertEquals($expected_includes, $req_relations->getRelations());
        $this->assertEquals($expected_excludes, $req_relations->getRelationsExcludes());
    }

    /**
     * @return array
     */
    public function relationsProvider()
    {
        return [
            'noRelations'       => [
                [],
                [],
                [],
            ],
            'includeAll'        => [
                [RequestRelations::QUERY_KEYWORD_INCLUDE => RequestRelations::QUERY_KEYWORD_ALL],
                [RequestRelations::QUERY_KEYWORD_ALL],
                [],
            ],
            'includeNone'       => [
                [RequestRelations::QUERY_KEYWORD_INCLUDE => RequestRelations::QUERY_KEYWORD_NONE],
                [RequestRelations::QUERY_KEYWORD_NONE],
                [],
            ],
            'includeMulti'      => [
                [
                    RequestRelations::QUERY_KEYWORD_INCLUDE => 'foo' . RequestRelations::RELATION_SEPARATOR . 'bar'
                        . RequestRelations::RELATION_SEPARATOR,
                ],
                ['foo', 'bar', ''],
                [],
            ],
            'excludeSingle'     => [
                [RequestRelations::QUERY_KEYWORD_EXCLUDE => 'foo'],
                [],
                ['foo'],
            ],
            'excludeMulti'      => [
                [
                    RequestRelations::QUERY_KEYWORD_EXCLUDE => 'foo' . RequestRelations::RELATION_SEPARATOR . 'bar'
                        . RequestRelations::RELATION_SEPARATOR,
                ],
                [],
                ['foo', 'bar', ''],
            ],
            'excludeAndInclude' => [
                [
                    RequestRelations::QUERY_KEYWORD_INCLUDE => 'foo' . RequestRelations::RELATION_SEPARATOR . 'bar',
                    RequestRelations::QUERY_KEYWORD_EXCLUDE => 'toto' . RequestRelations::RELATION_SEPARATOR . 'tata',
                ],
                ['foo', 'bar'],
                ['toto', 'tata'],
            ],
        ];
    }
}
