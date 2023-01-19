<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Keys\Exceptions;

use Ox\Mediboard\System\Keys\CKeyMetadata;

/**
 * Exceptions that occurred when we could not persist a key.
 */
class CouldNotPersistKey extends KeyException
{
    /**
     * @return static
     */
    public static function unableToStoreMetadata(): self
    {
        return new static("CouldNotPersistKey-error-Unable to persist key metadata");
    }

    /**
     * @param string $name
     *
     * @return static
     */
    public static function alreadyExists(string $name): self
    {
        return new static('CouldNotPersistKey-error-Provided key already exists: %s', $name);
    }

    /**
     * @param CKeyMetadata $metadata
     *
     * @return static
     */
    public static function unableToCreateKeyStorage(CKeyMetadata $metadata): self
    {
        return new static('CouldNotPersistKey-error-Unable to create key storage: %s', $metadata->name);
    }

    /**
     * @param CKeyMetadata $metadata
     *
     * @return static
     */
    public static function dirNotWriteable(CKeyMetadata $metadata): self
    {
        return new static('CouldNotPersistKey-error-Directory is not writeable for key: %s', $metadata->name);
    }

    /**
     * @param CKeyMetadata $metadata
     *
     * @return static
     */
    public static function unableToWriteKey(CKeyMetadata $metadata): self
    {
        return new static('CouldNotPersistKey-error-Unable to write key: %s', $metadata->name);
    }
}
