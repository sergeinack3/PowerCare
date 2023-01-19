<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Security\Crypt;

use MyCLabs\Enum\Enum;

/**
 * Hashing algorithms enumeration
 *
 * @method static static SHA256()
 */
class Hash extends Enum
{
    private const SHA256 = 'sha256';
}
