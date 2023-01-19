<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Keys\Exceptions;

use Ox\Mediboard\System\Keys\CKeyMetadata;

/**
 * Exceptions that occurred when we could not generate a key.
 */
class CouldNotGenerateKey extends KeyException
{
    /**
     * @param CKeyMetadata $metadata
     *
     * @return static
     */
    public static function invalidAlg(CKeyMetadata $metadata): self
    {
        return new static('CouldNotGenerateKey-error-Invalid provided alg: %s', $metadata->alg);
    }

    /**
     * @param string $message
     *
     * @return static
     */
    public static function errorDuringGeneration(string $message): self
    {
        return new static('CouldNotGenerateKey-error-An error occurred during key generation: %s', $message);
    }
}
