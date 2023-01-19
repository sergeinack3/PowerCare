<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Ox\Core\CMbException;
use Ox\Core\CMbSecurity;
use Ox\Mediboard\System\Keys\CKeyMetadata;
use Ox\Mediboard\System\Keys\Key;
use Ox\Tests\OxUnitTestCase;

class CMbSecurityTest extends OxUnitTestCase
{
    /** @var string|null */
    private static $iv;

    /** @var string|null|bool */
    private static $crypted;

    public function getLengthForRandomStringProvider(): array
    {
        return [
            'length: 8'        => [8, 8],
            'length: 16'       => [16, 16],
            'length: 15 (odd)' => [15, 15],
            'length: 0'        => [0, 0],
            'length: -2'       => [-2, 0],
            'length: -3'       => [-3, 0],
        ];
    }

    /**
     * Test basic AES CTR encryption
     */
    public function testAESCTREncryption(): void
    {
        $algo          = CMbSecurity::AES;
        $mode          = CMbSecurity::CTR;
        $key           = 'ThisIsMyAESCTRTestingKey';
        $clear         = 'mediboard';
        self::$iv      = CMbSecurity::generateIV();
        self::$crypted = CMbSecurity::encrypt($algo, $mode, $key, $clear, self::$iv);

        $this->assertNotFalse(self::$crypted);
    }

    /**
     * Test basic AES CTR decryption
     * @depends testAESCTREncryption
     */
    public function testAESCTRDecryption(): void
    {
        $algo  = CMbSecurity::AES;
        $mode  = CMbSecurity::CTR;
        $key   = 'ThisIsMyAESCTRTestingKey';
        $clear = CMbSecurity::decrypt($algo, $mode, $key, self::$crypted, self::$iv);

        $this->assertEquals($clear, 'mediboard');
    }

    /**
     * Test basic AES compatibility version CTR encryption
     */
    public function testAESCompatCTREncryption(): void
    {
        $algo    = CMbSecurity::AES_COMPAT;
        $mode    = CMbSecurity::CTR;
        $key     = 'ThisIsMyAESCTRTestingKey';
        $clear   = 'mediboard';
        $crypted = CMbSecurity::encrypt($algo, $mode, $key, $clear);

        $this->assertEquals('ZoICTUtvPeYT', $crypted);
    }

    /**
     * Test basic AES compatibility version CTR decryption
     */
    public function testAESCompatCTRDecryption(): void
    {
        $algo    = CMbSecurity::AES_COMPAT;
        $mode    = CMbSecurity::CTR;
        $key     = 'ThisIsMyAESCTRTestingKey';
        $crypted = 'ZoICTUtvPeYT';
        $clear   = CMbSecurity::decrypt($algo, $mode, $key, $crypted);

        $this->assertEquals('mediboard', $clear);
    }

    /**
     * Test SHA256 hash
     */
    public function testSHA256Hash(): void
    {
        $algo = CMbSecurity::SHA256;
        $text = 'mediboard_testing_SHA256_hash';
        $hash = CMbSecurity::hash($algo, $text);

        $this->assertEquals($hash, 'afbf553953d7772842d44ed9278f1465593ad6fbd45070588530069c300ccd4d');
    }

    /**
     * Test specific key and value with AES CBC (bug with previous rtrim treatment)
     */
    public function testAESCBC(): void
    {
        $key   = 'e0e85fc24544ae6e8561153640e35955';
        $plain = '######1102';

        $iv = CMbSecurity::generateIV();

        $this->assertEquals(
            CMbSecurity::decrypt(
                CMbSecurity::AES,
                CMbSecurity::CBC,
                $key,
                CMbSecurity::encrypt(
                    CMbSecurity::AES,
                    CMbSecurity::CBC,
                    $key,
                    $plain,
                    $iv
                ),
                $iv
            ),
            $plain
        );
    }

    /**
     * @dataProvider getLengthForRandomStringProvider
     *
     * @param int $length
     * @param int $expected_length
     *
     * @throws CMbException
     */
    public function testRandomStringLength(int $length, int $expected_length): void
    {
        if ($expected_length % 2 !== 0) {
            $this->expectException(CMbException::class);
        }

        $hex = CMbSecurity::getRandomString($length);
        $this->assertEquals($expected_length, strlen($hex));

        $bin = CMbSecurity::getRandomString($length, true);
        $this->assertEquals($expected_length, strlen($bin));
    }

    public function testInteroperabilityBetweenEncryptionFormats(): void
    {
        $clear = 'test123';
        $iv    = CMbSecurity::generateIV(16, false);

        $metadata       = new CKeyMetadata();
        $metadata->name = 'test123';
        $metadata->alg  = 'aes';
        $metadata->mode = 'ctr';
        $metadata->updateFormFields();

        $key_value = CMbSecurity::getRandomString(32, true);

        $key = new Key($metadata, $key_value);

        // "Legacy" encryption (base64 format)
        $crypted_legacy = CMbSecurity::encrypt(
            $key->getAlg()->getValue(),
            $key->getMode()->getValue(),
            $key->getValue(),
            $clear,
            $iv
        );
        $this->assertEquals(
            $clear,
            CMbSecurity::decrypt(
                $key->getAlg()->getValue(),
                $key->getMode()->getValue(),
                $key->getValue(),
                $crypted_legacy,
                $iv
            )
        );

        // Default format
        $crypted_default = CMbSecurity::encryptFromKey($key, $clear, $iv);
        $this->assertEquals(
            $clear,
            CMbSecurity::decryptFromKey($key, $crypted_default, $iv)
        );

        // Base64 format
        $crypted_b64 = CMbSecurity::encryptFromKey($key, $clear, $iv, CMbSecurity::FORMAT_B64);
        $this->assertEquals(
            $clear,
            CMbSecurity::decryptFromKey($key, $crypted_b64, $iv, CMbSecurity::FORMAT_B64)
        );

        // Hexadecimal format
        $crypted_hex = CMbSecurity::encryptFromKey($key, $clear, $iv, CMbSecurity::FORMAT_HEX);
        $this->assertEquals(
            $clear,
            CMbSecurity::decryptFromKey($key, $crypted_hex, $iv, CMbSecurity::FORMAT_HEX)
        );

        // Binary format
        $crypted_bin = CMbSecurity::encryptFromKey($key, $clear, $iv, CMbSecurity::FORMAT_BIN);
        $this->assertEquals(
            $clear,
            CMbSecurity::decryptFromKey($key, $crypted_bin, $iv, CMbSecurity::FORMAT_BIN)
        );

        // Decrypted BY DEFAULT from "legacy" format
        $this->assertEquals(
            $clear,
            CMbSecurity::decryptFromKey($key, $crypted_legacy, $iv)
        );

        // Decrypted by "legacy" from base64 format
        $this->assertEquals(
            $clear,
            CMbSecurity::decryptFromKey($key, $crypted_b64, $iv)
        );
    }

    public function testFilterInput(): void
    {
        $input = [
            'lorem'      => 'ipsum',
            'password'   => 'toto',
            'passphrase' => 'toto',
            'pwd'        => 'toto',
            'login'      => 'login',
        ];

        $expected = [
            'lorem'      => 'ipsum',
            'password'   => '******',
            'passphrase' => '******',
            'pwd'        => '******',
            'login'      => 'login',
        ];

        $this->assertEquals($expected, CMbSecurity::filterInput($input));
    }
}
