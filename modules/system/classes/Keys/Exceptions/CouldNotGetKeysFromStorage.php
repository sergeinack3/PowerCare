<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Keys\Exceptions;

/**
 * Exceptions that occurred when we could not retrieve a key from the storage.
 */
class CouldNotGetKeysFromStorage extends KeyException
{
    /**
     * @return static
     */
    public static function unableToRetrieveStorage(): self
    {
        return new static('CouldNotGetKeysFromStorage-error-No directory');
    }

    /**
     * @param string $storage
     *
     * @return static
     */
    public static function storageIsNotADirectory(string $storage): self
    {
        return new static('CouldNotGetKeysFromStorage-error-Storage is a not a directory: %s', $storage);
    }

    /**
     * @param string $storage
     *
     * @return static
     */
    public static function storageIsNotWriteable(string $storage): self
    {
        return new static('CouldNotGetKeysFromStorage-error-Storage is a not writeable: %s', $storage);
    }
}
