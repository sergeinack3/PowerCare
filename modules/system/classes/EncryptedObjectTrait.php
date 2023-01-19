<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CMbException;
use Ox\Core\CMbSecurity;
use Ox\Mediboard\System\Keys\CKeyMetadata;
use Ox\Mediboard\System\Keys\Exceptions\CouldNotGetKeysFromStorage;
use Ox\Mediboard\System\Keys\Exceptions\CouldNotUseKey;
use Ox\Mediboard\System\Keys\Key;
use Ox\Mediboard\System\Keys\KeyChain;

trait EncryptedObjectTrait
{
    /** @var CObjectEncryption */
    protected $_object_encryption;

    /**
     * @param string $data
     * @param string $key_name
     *
     * @return string
     * @throws CMbException
     * @throws CouldNotGetKeysFromStorage
     * @throws CouldNotUseKey
     */
    public function encrypt(string $data, string $key_name): string
    {
        $start = microtime(true);

        $key = $this->getKey($key_name);

        CApp::log(
            sprintf(
                "Getting key '%s' in %.2f ms",
                $key_name,
                (microtime(true) - $start) * 1000
            )
        );

        $this->_object_encryption = $this->getRefObjectEncryption();
        if (!$this->_object_encryption->_id) {
            $this->_object_encryption = $this->createObjectEncryption($this->_object_encryption, $key_name);
        }

        $start = microtime(true);

        $encrypted = CMbSecurity::encryptFromKey($key, $data, $this->_object_encryption->iv);

        CApp::log(
            sprintf(
                "Encrypting with key '%s' done in %.2f ms",
                $key_name,
                (microtime(true) - $start) * 1000
            )
        );

        return $encrypted;
    }

    /**
     * @param string|null $data
     *
     * @return string|null
     * @throws CouldNotGetKeysFromStorage
     * @throws CouldNotUseKey
     */
    public function decrypt(?string $data): ?string
    {
        if ($data === null) {
            return null;
        }

        $object_encryption = $this->getRefObjectEncryption();
        if (!$object_encryption->_id || !$object_encryption->iv) {
            return $data;
        }

        $start = microtime(true);

        $key = $this->getKey($object_encryption->getKeyName());

        CApp::log(
            sprintf(
                "Getting key '%s' in %.2f ms",
                $object_encryption->getKeyName(),
                (microtime(true) - $start) * 1000
            )
        );

        $start = microtime(true);

        $decrypted = CMbSecurity::decryptFromKey($key, $data, $object_encryption->iv);

        CApp::log(
            sprintf(
                "Decrypting with key '%s' done in %.2f ms",
                $object_encryption->getKeyName(),
                (microtime(true) - $start) * 1000
            )
        );

        return $decrypted;
    }

    /**
     * @param string $key_name
     *
     * @return Key
     * @throws CouldNotGetKeysFromStorage
     * @throws CouldNotUseKey
     */
    private function getKey(string $key_name): Key
    {
        return KeyChain::load($key_name);
    }

    /**
     * @return CObjectEncryption
     * @throws Exception
     */
    private function getRefObjectEncryption(): CObjectEncryption
    {
        return $this->loadUniqueBackRef('encryption');
    }

    /**
     * @param CObjectEncryption $object_encryption
     * @param string            $key_name
     *
     * @return CObjectEncryption
     * @throws CMbException
     */
    protected function createObjectEncryption(CObjectEncryption $object_encryption, string $key_name): CObjectEncryption
    {
        // IV should be the same as the block size; which is always 16 bytes with AES.
        $object_encryption->iv     = CMbSecurity::generateIV(16, false);
        $object_encryption->key_id = CKeyMetadata::loadFromName($key_name)->_id;

        return $object_encryption;
    }
}
