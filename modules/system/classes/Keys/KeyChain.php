<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Keys;

use Ox\Mediboard\System\Keys\Exceptions\CouldNotGetKeysFromStorage;
use Ox\Mediboard\System\Keys\Exceptions\CouldNotUseKey;

/**
 * Key accessor.
 * Allow us to retrieve keys from storage.
 */
class KeyChain
{
    use KeyFSTrait;

    /**
     * @param string $name
     *
     * @return Key
     * @throws CouldNotGetKeysFromStorage
     * @throws CouldNotUseKey
     */
    public function get(string $name): Key
    {
        $metadata = $this->getMetadata($name);
        $key_path = $this->getKeyPath($metadata);

        if (!$this->fileExists($key_path)) {
            throw CouldNotUseKey::doesNotExist($metadata);
        }

        $value = $this->readFile($key_path, $metadata);

        return new Key($metadata, $value);
    }

    /**
     * @param string $name
     *
     * @return Key
     * @throws CouldNotGetKeysFromStorage
     * @throws CouldNotUseKey
     */
    public static function load(string $name): Key
    {
        return (new self())->get($name);
    }

    /**
     * @param string $name
     *
     * @return CKeyMetadata
     * @throws CouldNotUseKey
     */
    protected function getMetadata(string $name): CKeyMetadata
    {
        return CKeyMetadata::loadFromName($name);
    }

    /**
     * @param string $key_path
     *
     * @return bool
     */
    protected function fileExists(string $key_path): bool
    {
        return file_exists($key_path);
    }

    /**
     * @param string       $key_path
     * @param CKeyMetadata $metadata
     *
     * @return string
     * @throws CouldNotUseKey
     */
    protected function readFile(string $key_path, CKeyMetadata $metadata): string
    {
        if (!is_readable($key_path)) {
            throw CouldNotUseKey::notReadable($metadata);
        }

        $value = file_get_contents($key_path);

        if ($value === false) {
            throw CouldNotUseKey::isEmpty($metadata);
        }

        return $value;
    }
}
