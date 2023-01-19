<?php

/**
 * @package Mediboard\Admin\Tests
 * @author  SARL OpenXtrem <dev@openxtrem.com>
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

namespace Ox\Cli\Tests\Fixtures\Dolor;

use Ox\Cli\Tests\Unit\FixturesFinderTest;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * @description Dolor is incredible
 */
class DolorFixtures extends Fixtures implements GroupFixturesInterface
{

    /**
     * @inheritDoc
     */
    public function load()
    {
    }

    /**
     * @return string[]
     */
    public static function getGroup(): array
    {
        return [FixturesFinderTest::GROUP , 40];
    }

    public function purge()
    {
        return null;
    }
}
