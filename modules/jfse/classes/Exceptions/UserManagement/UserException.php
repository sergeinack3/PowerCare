<?php

/**
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Exceptions\UserManagement;

use Exception;
use Ox\Mediboard\Jfse\Exceptions\JfseException;

/**
 * Class UserException
 *
 * @package Ox\Mediboard\Jfse\Exceptions\UserManagement
 */
class UserException extends JfseException
{
    /**
     * @param string         $message
     * @param Exception|null $previous
     *
     * @return static
     */
    public static function persistenceError(string $message, Exception $previous = null): self
    {
        return new static('PersistenceError', $message, [], 0, $previous);
    }

    /**
     * @param int $mediuser_id         The CMediuser's id
     * @param Exception|null $previous The previously thrown exception
     *
     * @return UserException
     */
    public static function mediuserNotFound(int $mediuser_id, Exception $previous = null): self
    {
        return new static(
            'MediuserNotFound',
            'JfseUserException-error-mediuser_not_found',
            [$mediuser_id],
            0,
            $previous
        );
    }

    /**
     * @param int $establishment_id    The CJfseEstablishment's id
     * @param Exception|null $previous The previously thrown exception
     *
     * @return UserException
     */
    public static function establishmentNotFound(string $establishment_id, Exception $previous = null): self
    {
        return new static(
            'EstablishmentNotFound',
            'JfseUserException-error-establishment_not_found',
            [$establishment_id],
            0,
            $previous
        );
    }

    /**
     * @param string $name
     * @param int    $user_id
     *
     * @return UserException
     */
    public static function userAlreadyLinked(string $name = null, int $user_id = null): self
    {
        return new static('UserAlreadyLinked', 'JfseUserException-error-user_already_linked', [$name, $user_id]);
    }

    /**
     * @return static
     */
    public static function signatureNotFound(): self
    {
        return new static('UserSignatureNotFound', 'JfseUserException-error-signature_not_found');
    }
}
