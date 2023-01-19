<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files\Tests\Unit;

use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CReadFile;
use Ox\Mediboard\Files\Tests\Fixtures\ReadFileFixtures;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\OxUnitTestCase;

class ReadFileTest extends OxUnitTestCase
{
    public function testGetUnread(): void
    {
        $sejour     = $this->getObjectFromFixturesReference(CSejour::class, ReadFileFixtures::SEJOUR_TAG);
        $doc_unread = $this->getObjectFromFixturesReference(CFile::class, ReadFileFixtures::FILE2_TAG);

        $unread = CReadFile::getUnread([$sejour]);

        $this->assertArrayHasKey($sejour->_id, $unread);

        $first_doc_unread = reset($unread[$sejour->_id]);

        $this->assertEquals($doc_unread->_id, $first_doc_unread->_id);
    }
}
