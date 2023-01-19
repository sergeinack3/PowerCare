<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Keys;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbPath;
use Ox\Core\CMbSecurity;
use Ox\Core\Security\Crypt\Alg;
use Ox\Core\Security\Crypt\Mode;
use Ox\Mediboard\System\Keys\Exceptions\CouldNotGenerateKey;
use Ox\Mediboard\System\Keys\Exceptions\CouldNotGetKeysFromStorage;
use Ox\Mediboard\System\Keys\Exceptions\CouldNotPersistKey;
use Throwable;

/**
 * Key builder.
 * Performs key metadata storage, key generation and key storage.
 */
class KeyBuilder
{
    use KeyFSTrait;

    // Should always use a key length of 32 bytes with AES.
    private const KEY_LENGTH = 32;

    /**
     * @param string $name
     * @param Alg    $alg
     * @param Mode   $mode
     *
     * @throws CouldNotPersistKey
     */
    public function persistMetadata(string $name, Alg $alg, Mode $mode): void
    {
        $metadata       = new CKeyMetadata();
        $metadata->name = $name;
        $metadata->alg  = $alg->getValue();
        $metadata->mode = $mode->getValue();

        // Keep creation date empty!

        try {
            // Let CKeyMetadata handle persistence checks
            if ($msg = $metadata->store()) {
                throw new Exception($msg);
            }
        } catch (Throwable $t) {
            throw CouldNotPersistKey::unableToStoreMetadata();
        }
    }

    /**
     * @param CKeyMetadata $metadata
     *
     * @throws CouldNotGenerateKey
     * @throws CouldNotGetKeysFromStorage
     * @throws CouldNotPersistKey
     */
    public function generateKey(CKeyMetadata $metadata): void
    {
        if ($metadata->isAlgSymmetric()) {
            $key = $this->generateSymmetricKey();

            $this->persistSymmetricKey($metadata, $key);

            return;
        }

        throw CouldNotGenerateKey::invalidAlg($metadata);
    }

    /**
     * @return string
     * @throws CouldNotGenerateKey
     */
    protected function generateSymmetricKey(): string
    {
        try {
            return CMbSecurity::getRandomString(self::KEY_LENGTH, true);
        } catch (Throwable $t) {
            throw CouldNotGenerateKey::errorDuringGeneration($t->getMessage());
        }
    }

    /**
     * @param CKeyMetadata $metadata
     * @param string       $key
     *
     * @throws CouldNotGetKeysFromStorage
     * @throws CouldNotPersistKey
     */
    protected function persistSymmetricKey(CKeyMetadata $metadata, string $key): void
    {
        $key_path = $this->getKeyPath($metadata);

        if (file_exists($key_path)) {
            throw CouldNotPersistKey::alreadyExists($metadata->name);
        }

        // Todo: UNIX permissions?
        if (CMbPath::forceDir(dirname($key_path)) === false) {
            throw CouldNotPersistKey::unableToCreateKeyStorage($metadata);
        }

        if (!is_writeable(dirname($key_path))) {
            throw CouldNotPersistKey::dirNotWriteable($metadata);
        }

        if (file_put_contents($key_path, $key) === false) {
            throw CouldNotPersistKey::unableToWriteKey($metadata);
        }

        $metadata->creation_date = CMbDT::dateTime();

        try {
            $msg = $metadata->store();

            if ($msg !== null) {
                throw new Exception($msg);
            }
        } catch (Throwable $t) {
            CMbPath::remove($key_path, true);

            throw CouldNotPersistKey::unableToStoreMetadata();
        }
    }
}
