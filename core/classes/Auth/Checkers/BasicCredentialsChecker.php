<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Auth\Checkers;

use Ox\Core\Security\Crypt\Hasher;
use Ox\Mediboard\System\CUserAuthentication;

/**
 * Same as parent (standard check) but from Basic credentials (for autowiring purposes).
 */
class BasicCredentialsChecker extends StandardCredentialsChecker
{
    /**
     * @inheritDoc
     */
    public function __construct(Hasher $hasher)
    {
        parent::__construct($hasher);

        $this->method = CUserAuthentication::AUTH_METHOD_BASIC;
    }
}
