<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Request;

use Ox\Core\Api\Request\Filter;
use Ox\Core\Api\Request\RequestApiBuilder;
use Ox\Core\Api\Request\RequestFieldsets;
use Ox\Core\Api\Request\RequestFilter;
use Ox\Core\Api\Request\RequestLimit;
use Ox\Core\Api\Request\RequestRelations;
use Ox\Core\Api\Request\RequestSort;
use Ox\Core\Api\Request\Sort;
use Ox\Core\Api\Resources\AbstractResource;
use Ox\Core\CMbException;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test the generation of a RequestApi from RequestApiGenerator object.
 */
class RequestApiBuilderTest extends OxUnitTestCase
{
    public function testBuildRequest(): RequestApiBuilder
    {
        $builder = (new RequestApiBuilder())
            ->setLimit(10)
            ->setOffset(20)
            ->setSort([new Sort('lorem'), new Sort('ipsul', Sort::SORT_DESC)])
            ->addSort(new Sort('test'))
            ->addRelation('relation_discarded')
            ->setRelations(['relation1', 'relation2'])
            ->setRelationsExcluded(['exclude1'])
            ->addRelationExcluded('exclude2')
            ->setFieldsets(['fieldset1', 'fieldset2'])
            ->addFieldset('fieldset3')
            ->setFilters([new Filter('field1', RequestFilter::FILTER_BEGIN_WITH, 'value1')])
            ->addFilter(new Filter('field2', RequestFilter::FILTER_CONTAINS, 'value2'))
            ->setUri('/api/test')
            ->setWithPermissions(true);

        /** @var Request $request */
        $request = $this->invokePrivateMethod($builder, 'buildRequest');

        $this->assertEquals(10, $request->query->get(RequestLimit::QUERY_KEYWORD_LIMIT));
        $this->assertEquals(20, $request->query->get(RequestLimit::QUERY_KEYWORD_OFFSET));
        $this->assertEquals(
            implode(RequestSort::SORT_SEPARATOR, ['+lorem', '-ipsul', '+test']),
            $request->query->get(RequestSort::QUERY_KEYWORD_SORT)
        );
        $this->assertEquals(
            implode(RequestRelations::RELATION_SEPARATOR, ['relation1', 'relation2']),
            $request->query->get(RequestRelations::QUERY_KEYWORD_INCLUDE)
        );
        $this->assertEquals(
            implode(RequestRelations::RELATION_SEPARATOR, ['exclude1', 'exclude2']),
            $request->query->get(RequestRelations::QUERY_KEYWORD_EXCLUDE)
        );
        $this->assertEquals(
            implode(RequestFieldsets::FIELDSETS_SEPARATOR, ['fieldset1', 'fieldset2', 'fieldset3']),
            $request->query->get(RequestFieldsets::QUERY_KEYWORD)
        );
        $this->assertEquals(
            implode(RequestFilter::FILTER_SEPARATOR, ['field1.beginWith.value1', 'field2.contains.value2']),
            $request->query->get(RequestFilter::QUERY_KEYWORD_FILTER)
        );
        $this->assertTrue($request->query->get(AbstractResource::PERMISSIONS_KEYWORD));

        return $builder;
    }

    /**
     * @depends testBuildRequest
     */
    public function testBuildRequestApi(RequestApiBuilder $builder): void
    {
        $request_api = $builder->buildRequestApi();

        $this->assertEquals(10, $request_api->getLimit());
        $this->assertEquals(20, $request_api->getOffset());
        $this->assertEquals(
            [new Sort('lorem'), new Sort('ipsul', Sort::SORT_DESC), new Sort('test'),],
            $request_api->getSort()
        );
        $this->assertEquals(['relation1', 'relation2'], $request_api->getRelations());
        $this->assertEquals(['exclude1', 'exclude2'], $request_api->getRelationsExcluded());
        $this->assertEquals(['fieldset1', 'fieldset2', 'fieldset3'], $request_api->getFieldsets());
        $this->assertEquals(
            [
                new Filter('field1', RequestFilter::FILTER_BEGIN_WITH, 'value1'),
                new Filter('field2', RequestFilter::FILTER_CONTAINS, 'value2'),
            ],
            $request_api->getFilters()
        );
        $this->assertEquals('/api/test', $request_api->getUri());
    }

    public function testSetSortThrowException(): void
    {
        $this->expectExceptionObject(
            new CMbException('RequestApiGenerator-Error-All-sorts-element-must-be-instanceof-sort')
        );

        (new RequestApiBuilder())->setSort(['lorem']);
    }

    public function testSetFiltersThrowException(): void
    {
        $this->expectExceptionObject(
            new CMbException('RequestApiGenerator-Error-All-filters-element-must-be-instanceof-Filter')
        );

        (new RequestApiBuilder())->setFilters(['ipsum']);
    }
}
