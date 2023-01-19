<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Exceptions\UserManagement;

use Exception;
use Ox\Mediboard\Jfse\Exceptions\JfseException;

class EstablishmentException extends JfseException
{
    public static function unauthorizedObjectType(string $object_class): self
    {
        return new self(
            'UnauthorizedObjectType',
            'CJfseEstablishment-error-unauthorized_object_type',
            [$object_class]
        );
    }

    public static function objectNotFound(int $object_id, string $object_class, Exception $previous = null): self
    {
        return new self(
            'ObjectNotFound',
            'CJfseEstablishment-error-object_not_found',
            [$object_class, $object_id],
            0,
            $previous
        );
    }

    public static function persistenceError(string $message, Exception $previous = null): self
    {
        return new self('PersistenceError', $message, [], 0, $previous);
    }

    public static function establishmentAlreadyLinked(string $name, int $object_id, string $object_class): self
    {
        return new self(
            'EstablishmentAlreadyLinked',
            'CJfseEstablishment-error-establishment_already_linked',
            [$name, $object_id, $object_class]
        );
    }
}
