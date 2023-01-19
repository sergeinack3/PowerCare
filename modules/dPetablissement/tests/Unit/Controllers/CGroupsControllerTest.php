<?php

/**
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Etablissement\Tests\Unit\Controllers;

use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Etablissement\Controllers\CGroupsController;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mediusers\CSecondaryFunction;
use Ox\Tests\OxUnitTestCase;

class CGroupsControllerTest extends OxUnitTestCase
{
    public function testSetRoles(): void
    {
        $current_user  = CMediusers::get();
        $main_function = $current_user->loadRefFunction();

        // Get one function from etab Fixture
        $function       = new CFunctions();
        $function->text = UsersFixtures::REF_FIXTURES_FUNCTION;
        $function->loadMatchingObjectEsc();

        // Add secondary function on this function for current user
        $secondary_function              = new CSecondaryFunction();
        $secondary_function->user_id     = $current_user->_id;
        $secondary_function->function_id = $function->_id;
        $this->assertNull($secondary_function->store());

        // Create Collection from groups (not stored in BDD)
        $main_group      = $current_user->loadRefFunction()->loadRefGroup();
        $secondary_group = $function->loadRefGroup();

        $group_ids = [$secondary_group->_id];

        $groups = [
            $main_group,
            $secondary_group,
            new CGroups(),
            new CGroups(),
        ];

        $collection = new Collection($groups);

        $this->invokePrivateMethod(new CGroupsController(), 'setRoles', $collection);

        /** @var Item $item */
        foreach ($collection as $item) {
            $group = $item->getDatas();

            $add_data = $this->getPrivateProperty($item, 'additional_datas');

            if ($main_function->group_id === $group->group_id) {
                $this->assertTrue($add_data['is_main']);
            } else {
                $this->assertFalse($add_data['is_main']);
            }

            if (in_array($group->group_id, $group_ids)) {
                $this->assertTrue($add_data['is_secondary']);
            } else {
                $this->assertFalse($add_data['is_secondary']);
            }
        }
    }
}
