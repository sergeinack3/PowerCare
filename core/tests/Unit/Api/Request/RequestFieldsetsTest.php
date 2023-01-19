<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Request;

use Ox\Core\Api\Request\RequestFieldsets;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

class RequestFieldsetsTest extends OxUnitTestCase
{
    /**
     * @param $query_content
     * @param $expected
     *
     * @dataProvider fieldSetsProvider
     */
    public function testFieldSets($query_content, $expected)
    {
        $req           = new Request([RequestFieldsets::QUERY_KEYWORD => $query_content]);
        $req_fieldsets = new RequestFieldsets($req);
        $this->assertEquals($expected, $req_fieldsets->getFieldsets());
    }

    /**
     * @return array
     */
    public function fieldSetsProvider()
    {
        return [
            'fieldsetsNone'      => [
                'none',
                ['none'],
            ],
            'fieldsetsMulti'     => [
                'hello' . RequestFieldsets::FIELDSETS_SEPARATOR . 'test' . RequestFieldsets::FIELDSETS_SEPARATOR . 'toto',
                ['hello', 'test', 'toto'],
            ],
            'fieldsetsEmpty'     => [
                '',
                [],
            ],
            'fieldsetsWithEmpty' => [
                'foo' . RequestFieldsets::FIELDSETS_SEPARATOR,
                ['foo', ''],
            ],
        ];
    }
}
