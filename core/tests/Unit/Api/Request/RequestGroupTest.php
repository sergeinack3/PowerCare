<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Request;

use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestGroup;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

class RequestGroupTest extends OxUnitTestCase
{
    /**
     * @dataProvider constructProvider
     * @runInSeparateProcess
     * @throws ApiException
     */
    public function testConstruct(Request $request, ?int $group_id): void
    {
        $request_group = new RequestGroup($request);
        $this->assertEquals($group_id, $request_group->getGroup()->_id);
    }

    /**
     * @runInSeparateProcess
     */
    public function testConstructWithSession(): void
    {
        $group         = CGroups::get();
        $_SESSION['g'] = $group->_id;

        $this->testConstruct(new Request(), (int)$group->_id);
    }

    /**
     * @runInSeparateProcess
     */
    public function testConstructWithoutGroup(): void
    {
        $group         = CGroups::loadCurrent();
        $_SESSION['g'] = null;

        $this->testConstruct(new Request(), (int)$group->_id);
    }

    /**
     * @runInSeparateProcess
     */
    public function testConstructWithGroupNotExists(): void
    {
        $request = new Request();
        $request->headers->set(RequestGroup::HEADER_GROUP, PHP_INT_MAX);

        $this->expectExceptionMessage('common-error-Object not found');

        new RequestGroup($request);
    }

    public function constructProvider(): array
    {
        $provider = [];

        $group_default  = CGroups::get();
        $group_fixtures = $this->getObjectFromFixturesReference(CGroups::class, UsersFixtures::REF_FIXTURES_GROUP);


        $request = new Request();
        $request->headers->set(RequestGroup::HEADER_GROUP, $group_default->_id);
        $provider['with_header'] = [$request, (int)$group_default->_id];

        $provider['with_get'] = [
            new Request([RequestGroup::QUERY_GROUP[0] => $group_fixtures->_id]),
            (int)$group_fixtures->_id,
        ];

        return $provider;
    }
}
