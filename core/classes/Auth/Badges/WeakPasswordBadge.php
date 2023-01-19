<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Auth\Badges;

/**
 * Badge declaring that the current user's password is considered too weak.
 */
class WeakPasswordBadge extends AbstractToggleBadge
{
    // Override default value
    /** @var bool */
    protected $enabled = false;
}
