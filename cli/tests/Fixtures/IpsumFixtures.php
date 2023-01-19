<?php

/**
 * @package Mediboard\Admin\Tests
 * @author  SARL OpenXtrem <dev@openxtrem.com>
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

namespace Ox\Cli\Tests\Fixtures;

use Ox\Cli\Tests\Unit\FixturesFinderTest;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * @description Hello world
 */
class IpsumFixtures extends Fixtures implements GroupFixturesInterface
{
    /**
     * @inheritDoc
     */
    public function load()
    {
        $idex               = new CIdSante400();
        $idex->object_class = CUser::class;
        $idex->object_id    = 1; // admin
        $idex->id400        = uniqid('id400');
        $this->store($idex, "this_is_my_ref");
    }

    /**
     * @return string[]
     */
    public static function getGroup(): array
    {
        return [FixturesFinderTest::GROUP, 50];
    }
}
