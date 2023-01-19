<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Security\Crypt;

use MyCLabs\Enum\Enum;

/**
 * Encryption modes enumeration
 *
 * @method static static CTR()
 */
class Mode extends Enum
{
    private const CTR = 'ctr';
}
