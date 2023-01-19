<?php

/**
 * @package Mediboard\Admin\Tests
 * @author  SARL OpenXtrem <dev@openxtrem.com>
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

namespace Ox\Cli\Tests\Fixtures;

use Ox\Tests\Fixtures\Fixtures;

class SkippedFixtures extends Fixtures
{
    /**
     * @inheritDoc
     */
    public function load()
    {
        $this->markSkipped('working progress');
    }
}
