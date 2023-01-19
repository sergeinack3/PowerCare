<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Auth\Exception;

use Ox\Core\Locales\Translator;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Throwable;

class CouldNotValidatePreAuthentication extends AccountStatusException
{
    /** @var Translator */
    private $translator;

    /**
     * @inheritDoc
     */
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        $this->translator = new Translator();

        parent::__construct($this->translator->tr($message), $code, $previous);
    }

    /**
     * @return static
     */
    public static function userIsNotSupported(): self
    {
        return new static('Auth-error-User not supported');
    }

    /**
     * @return static
     */
    public static function userIsATemplate(): self
    {
        return new static('Auth-failed-template');
    }

    /**
     * @return static
     */
    public static function userIsSecondary(): self
    {
        return new static('CUserAuthentication-error-Connection of secondary user is not permitted.');
    }

    /**
     * @return static
     */
    public static function userIsDeactivated(): self
    {
        return new static('Auth-failed-user-deactivated');
    }

    /**
     * @return static
     */
    public static function userAccountHasExpired(): self
    {
        return new static('Auth-failed-user-deactivated');
    }

    /**
     * @return static
     */
    public static function userIsLocked(): self
    {
        return new static('Auth-failed-user-locked');
    }

    /**
     * @return static
     */
    public static function userHasNoRemoteAccess(): self
    {
        return new static('Auth-failed-user-noremoteaccess');
    }

    /**
     * @return static
     */
    public static function systemIsOfflineForNonAdmins(): self
    {
        return new static('Auth-error-System is under maintenance');
    }

    /**
     * @return static
     */
    public static function userHasNoAccessFromThisLocation(): self
    {
        return new static('Auth-failed-Authentication is not allowed from your location.');
    }
}
