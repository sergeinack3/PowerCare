<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Exceptions;

use Exception;

final class DataModelException extends JfseException
{
    /**
     * @param string         $message
     * @param Exception|null $previous
     *
     * @return DataModelException
     */
    public static function persistenceError(string $message, Exception $previous = null): self
    {
        return new self('PersistenceError', $message, [], 0, $previous);
    }
}
