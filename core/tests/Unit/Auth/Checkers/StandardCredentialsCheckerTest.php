<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Auth\Checkers;

use Ox\Core\Auth\Checkers\StandardCredentialsChecker;

class StandardCredentialsCheckerTest extends AbstractCredentialsCheckerTest
{
    public function getClassName(): string
    {
        return StandardCredentialsChecker::class;
    }
}
