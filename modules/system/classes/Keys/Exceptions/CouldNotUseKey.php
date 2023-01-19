<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Keys\Exceptions;

use Ox\Mediboard\System\Keys\CKeyMetadata;

/**
 * Exceptions that occurred when we could not use a key.
 */
class CouldNotUseKey extends KeyException
{
    /**
     * @param CKeyMetadata $metadata
     *
     * @return static
     */
    public static function doesNotExist(CKeyMetadata $metadata): self
    {
        return new static('CouldNotUseKey-error-Key %s does not exist on storage', $metadata->name);
    }

    /**
     * @param string $name
     *
     * @return static
     */
    public static function metadataNotFound(string $name): self
    {
        return new static('CouldNotUseKey-error-Key metadata %s has not been found', $name);
    }

    /**
     * @param CKeyMetadata $metadata
     *
     * @return static
     */
    public static function isEmpty(CKeyMetadata $metadata): self
    {
        return new static('CouldNotUseKey-error-Key %s is empty', $metadata->name);
    }

    /**
     * @param CKeyMetadata $metadata
     *
     * @return static
     */
    public static function notReadable(CKeyMetadata $metadata): self
    {
        return new static('CouldNotUseKey-error-Key file %s is not readable', $metadata->name);
    }
}
