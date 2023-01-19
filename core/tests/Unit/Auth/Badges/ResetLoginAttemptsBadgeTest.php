<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Auth\Badges;

use Ox\Core\Auth\Badges\ResetLoginAttemptsBadge;

class ResetLoginAttemptsBadgeTest extends AbstractBadgeTest
{
    public function getClassName(): string
    {
        return ResetLoginAttemptsBadge::class;
    }
}
