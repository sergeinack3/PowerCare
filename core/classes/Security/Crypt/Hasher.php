<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Security\Crypt;

use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CMbSecurity;

/**
 * Object Facade of CMbSecurity, enabling us to inject it into other classes.
 */
class Hasher
{
    /**
     * @param Hash   $alg
     * @param string $text
     *
     * @return bool|string
     */
    public function hash(Hash $alg, string $text)
    {
        return CMbSecurity::hash($alg->getValue(), $text, false);
    }
}
