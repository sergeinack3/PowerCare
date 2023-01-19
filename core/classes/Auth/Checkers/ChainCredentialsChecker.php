<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Auth\Checkers;

use Ox\Core\Auth\Exception\CredentialsCheckException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Chaining the credentials checkers using the Composite Pattern.
 */
class ChainCredentialsChecker implements CredentialsCheckerInterface
{
    use CredentialsCheckerTrait;

    /** @var CredentialsCheckerInterface[] */
    private $checkers = [];

    /**
     * @param CredentialsCheckerInterface ...$checkers
     */
    public function __construct(CredentialsCheckerInterface ...$checkers)
    {
        $this->checkers = $checkers;
    }

    public function check(string $password, UserInterface $user): bool
    {
        foreach ($this->checkers as $checker) {
            try {
                if ($checker->check($password, $user)) {
                    return true;
                }
            } catch (CredentialsCheckException $e) {
                return false;
            }
        }

        return false;
    }
}
