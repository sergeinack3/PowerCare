<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Request;

use Ox\Core\Api\Request\Content\JsonApiItem;
use Ox\Core\Api\Request\Content\RequestContentException;
use Ox\Core\CModelObject;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class JsonApiItemTest extends OxUnitTestCase
{
    public function test__constructFailed(): void
    {
        $this->expectExceptionMessage('must be present in the item');
        new JsonApiItem(['missing' => 'top level']);
    }

    /**
     * @throws TestsException
     * @throws RequestContentException
     */
    public function test__construct(): JsonApiItem
    {
        /** @var CUser $user */
        $user = $this->getObjectFromFixturesReference(CUser::class, UsersFixtures::REF_USER_LOREM_IPSUM);
        $data = [
            'type'       => 'user',
            'id'         => $user->_id,
            'attributes' => [
                'user_username'   => 'not_allowed_field',
                'user_first_name' => 'lorem',
                'user_last_name'  => 'ipsum',
            ],
        ];

        $item = new JsonApiItem($data);
        $this->assertEquals('user', $this->getPrivateProperty($item, 'type'));
        $this->assertEquals($user->_id, $this->getPrivateProperty($item, 'id'));

        return $item;
    }

    /**
     * @param JsonApiItem $item
     *
     * @throws RequestContentException
     * @throws TestsException
     * @depends test__construct
     */
    public function testModelObject(JsonApiItem $item): void
    {
        $item->createModelObject(CUser::class, false);
        $item->hydrateObject([CModelObject::FIELDSET_DEFAULT], ['user_first_name', 'user_last_name']);
        $original_model = (new CUser())->get($this->getPrivateProperty($item, 'id'));
        $model          = $item->getModelObject();
        $this->assertInstanceOf(CUser::class, $model);
        $this->assertEquals($original_model->user_username, $model->user_username);
        $this->assertEquals('lorem', $model->user_first_name);
        $this->assertEquals('ipsum', $model->user_last_name);
    }

    public function testModelObjectBadType(): void
    {
        $this->expectExceptionObject(
            RequestContentException::requestedClassTypeIsNotTheSameAsResourceType('lorem', CUser::RESOURCE_TYPE)
        );

        $item = new JsonApiItem(['id' => 'toto', 'type' => 'lorem']);
        $item->createModelObject(CUser::class, true);
    }
}
