<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Request;

use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Exceptions\ApiRequestException;
use Ox\Core\Api\Request\Filter;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Request\RequestFieldsets;
use Ox\Core\Api\Request\RequestFilter;
use Ox\Core\Api\Request\RequestFormats;
use Ox\Core\Api\Request\RequestLanguages;
use Ox\Core\Api\Request\RequestLimit;
use Ox\Core\Api\Request\RequestRelations;
use Ox\Core\Api\Request\RequestSort;
use Ox\Core\Api\Request\Sort;
use Ox\Core\CModelObject;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

class RequestApiTest extends OxUnitTestCase
{
    /**
     * @param array $request_params
     * @param array $request_head
     * @param array $expected
     *
     * @dataProvider requestApiProvider
     * @throws ApiRequestException|ApiException
     */
    public function testSortGetFieldsOk(array $request_params, array $request_head, array $expected)
    {
        $req_api = RequestApi::createFromRequest(new Request($request_params, [], [], [], [], $request_head));
        $this->assertEquals($expected['request'], $req_api->getRequest());
        $this->assertEquals($expected['request_formats'], $req_api->getFormats());
        $this->assertEquals($expected['request_limit'], $req_api->getLimit());
        $this->assertEquals($expected['request_offset'], $req_api->getOffset());
        $this->assertEquals($expected['request_limit_sql'], $req_api->getLimitAsSql());
        $this->assertEquals($expected['request_sort'], $req_api->getSort());
        $this->assertEquals($expected['request_sort_sql'], $req_api->getSortAsSql());
        $this->assertEquals($expected['request_relations'], $req_api->getRelations());
        $this->assertEquals($expected['request_relations_exclude'], $req_api->getRelationsExcluded());
        $this->assertEquals($expected['request_fieldsets'], $req_api->getFieldsets());
        $this->assertEquals($expected['request_filter'], $req_api->getFilters());
        $this->assertEquals($expected['request_filter_sql'], $req_api->getFilterAsSQL(CSQLDataSource::get('std')));
        $this->assertEquals($expected['request_languages'], $req_api->getLanguages());
        $this->assertEquals($expected['request_language_expected'], $req_api->getLanguageExpected());
    }

    /**
     * @throws ApiRequestException
     */
    public function testGetRequestParameterOk()
    {
        $req       = new Request([RequestLimit::QUERY_KEYWORD_LIMIT => 20]);
        $req_limit = new RequestLimit($req);

        $req_api = RequestApi::createFromRequest($req);
        $this->assertEquals($req_limit, $req_api->getRequestParameter(RequestLimit::class));
    }

    /**
     * @throws ApiRequestException
     */
    public function testGetRequestParameterParameterDoesNotExists()
    {
        $req     = new Request();
        $req_api = RequestApi::createFromRequest($req);
        $this->expectException(ApiRequestException::class);
        $req_api->getRequestParameter('foo');
    }


    /**
     * @return array
     */
    public function requestApiProvider()
    {
        $query_params = [
            RequestLimit::QUERY_KEYWORD_LIMIT       => 20,
            RequestLimit::QUERY_KEYWORD_OFFSET      => 100,
            RequestSort::QUERY_KEYWORD_SORT         => '-foo' . RequestSort::SORT_SEPARATOR . '+bar',
            RequestRelations::QUERY_KEYWORD_INCLUDE => 'foo' . RequestRelations::RELATION_SEPARATOR . 'toto',
            RequestRelations::QUERY_KEYWORD_EXCLUDE => 'bar' . RequestRelations::RELATION_SEPARATOR . 'tata',
            RequestFieldsets::QUERY_KEYWORD         => 'test' . RequestFieldsets::FIELDSETS_SEPARATOR . 'titi',
            RequestFilter::QUERY_KEYWORD_FILTER     => 'test' . RequestFilter::FILTER_PART_SEPARATOR . RequestFilter::FILTER_EQUAL
                . RequestFilter::FILTER_PART_SEPARATOR . '0' . RequestFilter::FILTER_SEPARATOR . 'toto'
                . RequestFilter::FILTER_PART_SEPARATOR . RequestFilter::FILTER_GREATER . RequestFilter::FILTER_PART_SEPARATOR . 'titi',
        ];

        $query_head = [
            'HTTP_' . RequestFormats::HEADER_KEY_WORD   => RequestFormats::FORMAT_XML,
            'HTTP_' . RequestLanguages::HEADER_KEY_WORD => RequestLanguages::LONG_TAG_EN,
        ];

        return [
            'emptyRequestApi' => [
                [],
                [],
                [
                    'request'                   => new Request(),
                    'request_formats'           => [RequestFormats::FORMAT_JSON],
                    'request_limit'             => RequestLimit::LIMIT_DEFAULT,
                    'request_offset'            => RequestLimit::OFFSET_DEFAULT,
                    'request_limit_sql'         => RequestLimit::OFFSET_DEFAULT . ',' . RequestLimit::LIMIT_DEFAULT,
                    'request_sort'              => [],
                    'request_sort_sql'          => '',
                    'request_relations'         => [],
                    'request_relations_exclude' => [],
                    'request_fieldsets'         => [],
                    'request_filter'            => [],
                    'request_filter_sql'        => [],
                    'request_languages'         => [RequestLanguages::SHORT_TAG_FR],
                    'request_language_expected' => RequestLanguages::SHORT_TAG_FR,
                ],
            ],
            'RequestApi'      => [
                $query_params,
                $query_head,
                [
                    'request'                   => new Request($query_params, [], [], [], [], $query_head),
                    'request_formats'           => [RequestFormats::FORMAT_XML],
                    'request_limit'             => 20,
                    'request_offset'            => 100,
                    'request_limit_sql'         => '100,20',
                    'request_sort'              => [new Sort('foo', Sort::SORT_DESC), new Sort('bar')],
                    'request_sort_sql'          => '`foo` ' . Sort::SORT_DESC . RequestSort::SORT_SEPARATOR . '`bar` '
                        . Sort::SORT_ASC,
                    'request_relations'         => ['foo', 'toto'],
                    'request_relations_exclude' => ['bar', 'tata'],
                    'request_fieldsets'         => ['test', 'titi'],
                    'request_filter'            => [
                        new Filter('test', RequestFilter::FILTER_EQUAL, [0]),
                        new Filter('toto', RequestFilter::FILTER_GREATER, ['titi']),
                    ],
                    'request_filter_sql'        => [
                        "`test` = '0'",
                        "`toto` > 'titi'",
                    ],
                    'request_languages'         => [RequestLanguages::LONG_TAG_EN],
                    'request_language_expected' => RequestLanguages::LONG_TAG_EN,
                ],
            ],
        ];
    }


    public function testGetModelObject()
    {
        $user = $this->getObjectFromFixturesReference(CUser::class, UsersFixtures::REF_USER_LOREM_IPSUM);

        $content     = [
            'data' => [
                'type'       => 'user',
                'id'         => $user->_id,
                'attributes' => [
                    'user_first_name' => 'lorem',
                    'user_last_name'  => 'ipsum',
                ],
            ],
        ];
        $server      = ['HTTP_Content-type' => RequestFormats::FORMAT_JSON_API];
        $request     = new Request([], [], [], [], [], $server, json_encode($content));
        $request_api = RequestApi::createFromRequest($request);
        $model       = $request_api->getModelObject(CUser::class, [], ['user_last_name', 'user_first_name']);
        $this->assertEquals(get_class($user), get_class($model));
        $this->assertEquals($user->_id, $model->_id);
    }

    public function testGetModelObjectCollection()
    {
        $users = [
            $this->getObjectFromFixturesReference(CUser::class, UsersFixtures::REF_USER_LOREM_IPSUM),
            CUser::get(),
        ];
        $ids   = [];
        $data  = [];
        foreach ($users as $user) {
            $ids[]          = $user->_id;
            $data['data'][] = [
                'type'       => 'user',
                'id'         => $user->_id,
                'attributes' => [
                    'user_last_name' => 'new name',
                ],
            ];
        }
        $server      = ['HTTP_Content-type' => RequestFormats::FORMAT_JSON_API];
        $request     = new Request([], [], [], [], [], $server, json_encode($data));
        $request_api = RequestApi::createFromRequest($request);
        $collection  = $request_api->getModelObjectCollection(CUser::class, [CModelObject::FIELDSET_DEFAULT]);
        $this->assertCount(2, $collection);
        foreach ($collection as $object) {
            $this->assertContains($object->_id, $ids);
        }
    }
}
