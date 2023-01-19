<?php

/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files\Tests\Unit;

use Ox\Core\CAppUI;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Patients\CSourceIdentite;
use Ox\Mediboard\Patients\Tests\Fixtures\SimplePatientFixtures;
use Ox\Mediboard\System\CObjectEncryption;
use Ox\Mediboard\System\Keys\CKeyMetadata;
use Ox\Mediboard\System\Keys\Exceptions\CouldNotUseKey;
use Ox\Mediboard\System\Keys\KeyBuilder;
use Ox\Mediboard\System\Keys\KeyChain;
use Ox\Tests\OxUnitTestCase;

/**
 * Test the encryption and decryption of files
 */
class CFileEncryptionTest extends OxUnitTestCase
{

    /**
     * @config [CConfiguration] [static] system KeyChain directory_path /tmp
     */
    public function testEncrypt(): array
    {
        // If key is already on the filesystem don't genere it
        try {
            KeyChain::load(CSourceIdentite::ENCRYPT_KEY_NAME);
        } catch (CouldNotUseKey $e) {
            $key_metadata = CKeyMetadata::loadFromName(CSourceIdentite::ENCRYPT_KEY_NAME);

            $builder = new KeyBuilder();
            $builder->generateKey($key_metadata);
        }

        /** @var CSourceIdentite $source */
        $source = $this->getObjectFromFixturesReference(CSourceIdentite::class, SimplePatientFixtures::SAMPLE_PATIENT);

        $file               = new CFile();
        $file->file_name    = uniqid();
        $file->object_class = $source->_class;
        $file->object_id    = $source->_id;
        $file->file_type    = 'text/plain';
        $file->setContent('Test encrypt');

        $file->fillFields();
        $file->updateFormFields();

        $this->assertNull($file->store());

        /** @var CObjectEncryption $iv */
        $iv = $file->loadUniqueBackRef('encryption');
        $this->assertEquals($source->getKeyName(), $iv->getKeyName());

        return [$file, file_get_contents($file->_file_path), 'Test encrypt'];
    }

    /**
     * @depends testEncrypt
     *
     * @config [CConfiguration] [static] system KeyChain directory_path /tmp
     */
    public function testDecrypt(array $data): void
    {
        [$file, $crypted_text, $expected_text] = $data;

        $this->assertEquals($expected_text, $file->decrypt($crypted_text));
    }

    public function testDecryptEmpty(): void
    {
        $this->assertNull((new CFile())->decrypt(null));
    }

    public function testDecryptNoIv(): void
    {
        $string = uniqid();
        $this->assertEquals($string, (new CFile())->decrypt($string));
    }
}
