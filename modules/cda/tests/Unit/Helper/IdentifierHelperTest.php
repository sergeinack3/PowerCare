<?php

/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Tests\Unit\Helper;

use Ox\Core\CMbSecurity;
use Ox\Tests\OxUnitTestCase;

class IdentifierHelperTest extends OxUnitTestCase
{
    public function testGenerateUUID(): void
    {
        $uuid = CMbSecurity::generateUUID();

        $this->assertMatchesRegularExpression(
            "/^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-4[0-9A-Fa-f]{3}-[89ABab][0-9A-Fa-f]{3}-[0-9A-Fa-f]{12}$/",
            $uuid
        );
    }
}
