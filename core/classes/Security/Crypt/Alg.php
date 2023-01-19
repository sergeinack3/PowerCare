<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Security\Crypt;

use MyCLabs\Enum\Enum;

/**
 * Encryption algorithms enumeration
 *
 * @method static static AES()
 */
class Alg extends Enum
{
    private const AES = 'aes';
    //    private const DES      = 'des';
    //    private const TDES     = '3-des';
    //    private const RIJNDAEL = 'rijndael';

    public function isSymmetric(): bool
    {
        $key = $this->getKey();

        return $key === 'AES';
    }
}
