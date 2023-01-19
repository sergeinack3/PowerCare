<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Exception;
use Ox\Core\FieldSpecs\CPasswordSpec;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\PasswordSpecs\PasswordSpecBuilder;
use Ox\Mediboard\System\Keys\Key;
use phpseclib\Crypt\AES as AESCompat;
use phpseclib\Crypt\Base;
use phpseclib\Crypt\DES as DESCompat;
use phpseclib\Crypt\Rijndael as RijndaelCompat;
use phpseclib\Crypt\RSA;
use phpseclib\Crypt\TripleDES as TripleDESCompat;
use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\DES;
use phpseclib3\Crypt\Hash;
use phpseclib3\Crypt\Random;
use phpseclib3\Crypt\Rijndael;
use phpseclib3\Crypt\TripleDES;
use phpseclib3\File\X509;
use phpseclib3\Math\BigInteger;

/**
 * Generic security class, uses pure-PHP library phpseclib
 */
class CMbSecurity
{
    // Ciphers
    public const AES      = 'aes';
    public const DES      = 'des';
    public const TDES     = '3-des';
    public const RIJNDAEL = 'rijndael';
    public const RSA      = 'rsa';

    public const AES_COMPAT      = 'aes_compat';
    public const DES_COMPAT      = 'des_compat';
    public const TDES_COMPAT     = '3-des_compat';
    public const RIJNDAEL_COMPAT = 'rijndael_compat';

    // Encryption modes
    public const CTR = 'ctr';
    public const ECB = 'ecb';
    public const CBC = 'cbc';
    public const CFB = 'cfb8';
    public const OFB = 'ofb';

    private const MODE_COMPAT = [
        self::CTR => Base::MODE_CTR,
        self::ECB => Base::MODE_ECB,
        self::CBC => Base::MODE_CBC,
        self::CFB => Base::MODE_CFB8,
        self::OFB => Base::MODE_OFB,
    ];

    // Hash algorithms
    public const MD2     = 'md2';
    public const MD5     = 'md5';
    public const MD5_96  = 'md5-96';
    public const SHA1    = 'sha1';
    public const SHA1_96 = 'sha1-96';
    public const SHA256  = 'sha256';
    public const SHA384  = 'sha384';
    public const SHA512  = 'sha512';

    private const HASH_ALGOS = [
        self::MD2,
        self::MD5,
        self::MD5_96,
        self::SHA1,
        self::SHA1_96,
        self::SHA256,
        self::SHA384,
        self::SHA512,
    ];

    public const FORMAT_BIN = 'bin';
    public const FORMAT_HEX = 'hex';
    public const FORMAT_B64 = 'b64';

    private const FORMATS = [
        self::FORMAT_BIN,
        self::FORMAT_HEX,
        self::FORMAT_B64,
    ];

    public const AMBIGUOUS_CHARACTERS = [
        'I',
        'l',
        '1',
        'O',
        'o',
        '0',
    ];

    /**
     * Generate a pseudo random string with the given length.
     *
     * @param int  $length
     * @param bool $binary
     *
     * @return string
     * @throws CMbException
     */
    static function getRandomString(int $length, bool $binary = false)
    {
        if ($length < 1) {
            return '';
        }

        if (!$binary) {
            if ($length % 2 !== 0) {
                throw new CMbException('CMbSecurity-error-Length should be a multiple of 2.');
            }

            $length /= 2;

            return bin2hex(Random::string($length));
        }

        return Random::string($length);
    }

    /**
     * Get random alphanumeric string from a given charset
     *
     * @param array $chars  Allowed characters
     * @param int   $length String length
     *
     * @return string
     * @throws Exception
     */
    public static function getRandomAlphaNumericString($chars = [], $length = 16): string
    {
        $string  = '';
        $charset = ($chars) ?: array_merge(range('a', 'z'), range('A', 'Z'), range(0, 9));

        $count = count($charset) - 1;
        for ($i = 0; $i < $length; $i++) {
            $string .= $charset[random_int(0, $count)];
        }

        return $string;
    }

    /**
     * @param int $length
     *
     * @return string
     * @throws Exception
     */
    public static function getRandomBase58String($length = 16): string
    {
        return self::getRandomAlphaNumericString(CMbString::CHARSET_BASE58, $length);
    }

    /**
     * Generate a pseudo random binary string
     *
     * @param int $length Binary string length
     *
     * @return string
     */
    static function getRandomBinaryString($length)
    {
        return Random::string($length);
    }

    /**
     * @param int  $length
     * @param bool $binary
     *
     * @return string
     * @throws CMbException
     */
    public static function generateIV(int $length = 16, bool $binary = true)
    {
        return self::getRandomString($length, $binary);
    }

    /**
     * Generate a UUID
     * Based on: http://www.php.net/manual/fr/function.uniqid.php#87992
     *
     * @return string
     */
    static function generateUUID()
    {
        $pr_bits = self::getRandomBinaryString(25);

        $time_low = bin2hex(substr($pr_bits, 0, 4));
        $time_mid = bin2hex(substr($pr_bits, 4, 2));

        $time_hi_and_version       = bin2hex(substr($pr_bits, 6, 2));
        $clock_seq_hi_and_reserved = bin2hex(substr($pr_bits, 8, 2));

        $node = bin2hex(substr($pr_bits, 10, 6));

        /**
         * Set the four most significant bits (bits 12 through 15) of the
         * time_hi_and_version field to the 4-bit version number from
         * Section 4.1.3.
         * @see http://tools.ietf.org/html/rfc4122#section-4.1.3
         */
        $time_hi_and_version = hexdec($time_hi_and_version);
        $time_hi_and_version = $time_hi_and_version >> 4;
        $time_hi_and_version = $time_hi_and_version | 0x4000;

        /**
         * Set the two most significant bits (bits 6 and 7) of the
         * clock_seq_hi_and_reserved to zero and one, respectively.
         */
        $clock_seq_hi_and_reserved = hexdec($clock_seq_hi_and_reserved);
        $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved >> 2;
        $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved | 0x8000;

        return sprintf(
            '%08s-%04s-%04x-%04x-%012s',
            $time_low,
            $time_mid,
            $time_hi_and_version,
            $clock_seq_hi_and_reserved,
            $node
        );
    }

    /**
     * @param string $encryption
     * @param string $mode
     *
     * @return AES|DES|Rijndael|TripleDES|AESCompat|DESCompat|RijndaelCompat|RSA|TripleDESCompat|null
     */
    public static function getCipher(string $encryption, string $mode = self::CTR)
    {
        switch ($encryption) {
            case self::AES:
                return new AES($mode);

            case self::AES_COMPAT:
                return new AESCompat(self::MODE_COMPAT[$mode]);

            case self::DES:
                return new DES($mode);

            case self::DES_COMPAT:
                return new DESCompat(self::MODE_COMPAT[$mode]);

            case self::TDES:
                return new TripleDES($mode);

            case self::TDES_COMPAT:
                return new TripleDESCompat(self::MODE_COMPAT[$mode]);

            case self::RIJNDAEL:
                return new Rijndael($mode);

            case self::RIJNDAEL_COMPAT:
                return new RijndaelCompat(self::MODE_COMPAT[$mode]);

            case self::RSA:
                return new RSA();

            default:
                return null;
        }
    }

    /**
     * Encrypt a text
     *
     * @param int    $encryption Cipher to use (AES, DES, TDES or RIJNDAEL)
     * @param int    $mode       Encryption mode to use (CTR, ECB, CBC, CFB or OFB)
     * @param string $key        Key to use
     * @param string $clear      Clear text to encrypt
     * @param string $iv         Initialisation vector to use
     *
     * @return bool|string
     */
    public static function encrypt(string $encryption, string $mode, string $key, string $clear, ?string $iv = null)
    {
        $cipher = self::getCipher($encryption, $mode);

        if (!$cipher) {
            return false;
        }

        $cipher->setKey($key);

        switch ($mode) {
            case self::CBC:
            case self::CFB:
            case self::CTR:
            case self::OFB:
                $cipher->setIV($iv ?? "");

            default:
        }

        return base64_encode($cipher->encrypt($clear));
    }

    /**
     * @param Key         $key
     * @param string      $clear
     * @param string|null $iv
     * @param bool        $binary
     *
     * @return false|int|string
     */
    public static function encryptFromKey(Key $key, string $clear, ?string $iv, string $format = self::FORMAT_B64)
    {
        $alg  = $key->getAlg();
        $mode = $key->getMode();

        $cipher = self::getCipher($alg->getValue(), $mode->getValue());

        if (!$cipher) {
            return false;
        }

        $cipher->setKey($key->getValue());

        switch ($mode->getValue()) {
            case self::CBC:
            case self::CFB:
            case self::CTR:
            case self::OFB:
                $cipher->setIV($iv ?? "");

            default:
        }

        $crypted = $cipher->encrypt($clear);

        switch ($format) {
            case self::FORMAT_BIN:
                return $crypted;

            case self::FORMAT_HEX:
                return bin2hex($crypted);

            case self::FORMAT_B64:
            default:
                return base64_encode($crypted);
        }
    }

    /**
     * Decrypt a text
     *
     * @param int    $encryption Cipher to use (AES, DES, TDES or RIJNDAEL)
     * @param int    $mode       Encryption mode to use (CTR, ECB, CBC, CFB or OFB)
     * @param string $key        Key to use
     * @param string $crypted    Cipher text to decrypt
     * @param string $iv         Initialisation vector to use
     *
     * @return bool|string
     */
    public static function decrypt(string $encryption, string $mode, string $key, string $crypted, ?string $iv = null)
    {
        $cipher = self::getCipher($encryption, $mode);

        if (!$cipher) {
            return false;
        }

        $cipher->setKey($key);

        switch ($mode) {
            case self::CBC:
            case self::CFB:
            case self::CTR:
            case self::OFB:
                $cipher->setIV($iv ?? "");

            default:
        }

        return $cipher->decrypt(base64_decode($crypted));
    }

    /**
     * @param Key         $key
     * @param string      $crypted
     * @param string|null $iv
     * @param string      $binary
     *
     * @return false|int|string
     */
    public static function decryptFromKey(Key $key, string $crypted, ?string $iv, string $format = self::FORMAT_B64)
    {
        $alg  = $key->getAlg();
        $mode = $key->getMode();

        $cipher = self::getCipher($alg->getValue(), $mode->getValue());

        if (!$cipher) {
            return false;
        }

        $cipher->setKey($key->getValue());

        switch ($mode->getValue()) {
            case self::CBC:
            case self::CFB:
            case self::CTR:
            case self::OFB:
                $cipher->setIV($iv ?? "");

            default:
        }

        switch ($format) {
            case self::FORMAT_BIN:
                return $cipher->decrypt($crypted);

            case self::FORMAT_HEX:
                return $cipher->decrypt(hex2bin($crypted));

            case self::FORMAT_B64:
            default:
                return $cipher->decrypt(base64_decode($crypted));
        }
    }

    /**
     * Global hashing function
     *
     * @param int    $algo   Hash algorithm to use
     * @param string $text   Text to hash
     * @param bool   $binary Binary or hexa output
     *
     * @return bool|string
     */
    public static function hash(string $algo, string $text, bool $binary = false)
    {
        if (!in_array($algo, self::HASH_ALGOS)) {
            return false;
        }

        $hash        = new Hash($algo);
        $fingerprint = $hash->hash($text);

        if (!$binary) {
            $fingerprint = bin2hex($fingerprint);
        }

        return $fingerprint;
    }

    /**
     * Filtering input data
     *
     * @param array|string $params Array to filter
     *
     * @return array
     */
    public static function filterInput($params)
    {
        if (!is_array($params)) {
            return $params;
        }

        $patterns = [
            "/password|passphrase|pwd/i",
            "/login/i",
        ];

        $replacements = [
            ["/.*/", "***"],
            ["/([^:]*):(.*)/i", "$1:***"],
        ];

        // We replace passwords and passphrases with a mask
        foreach ($params as $_key => $_value) {
            foreach ($patterns as $_k => $_pattern) {
                if ((!empty($_value) && !empty($_key)) && preg_match($_pattern, $_key)) {
                    $params[$_key] = preg_replace($replacements[$_k][0], $replacements[$_k][1], $_value);
                }
            }
        }

        return $params;
    }

    /**
     * Validate the client certificate with the authority certificate
     *
     * @param String $certificate_client Client certificate
     * @param String $certificate_ca     Authority certificate
     *
     * @return bool
     */
    public static function validateCertificate($certificate_client, $certificate_ca)
    {
        $x509 = new X509();

        $x509->loadX509($certificate_client);
        $x509->loadCA($certificate_ca);

        return $x509->validateSignature(X509::VALIDATE_SIGNATURE_BY_CA);
    }

    /**
     * Return the DN of the certificate
     *
     * @param String $certificate_client Client certificate
     *
     * @return String
     */
    public static function getDNString($certificate_client)
    {
        $x509 = new X509();

        $x509->loadX509($certificate_client);

        // Param à 1 pour avoir un string en retour
        return $x509->getDN(1);
    }

    /**
     * Return the Issuer DN of the certificate
     *
     * @param String $certificate_client Client certificate
     *
     * @return String
     */
    public static function getIssuerDnString($certificate_client)
    {
        $x509 = new X509();

        $x509->loadX509($certificate_client);

        // Param à 1 pour avoir un string en retour
        return $x509->getIssuerDN(1);
    }

    /**
     * Validate the client certificate with the current date
     *
     * @param String $certificate_client Client certificate
     *
     * @return bool
     */
    public static function validateCertificateDate($certificate_client)
    {
        $x509 = new X509();

        $x509->loadX509($certificate_client);

        return $x509->validateDate();
    }

    /**
     * Return the information of certificate
     *
     * @param String $certificate_client Client certificate
     *
     * @return String[]
     */
    public static function getInformationCertificate($certificate_client)
    {
        $x509 = new X509();

        return $x509->loadX509($certificate_client);
    }

    /**
     * Return the certificate serial
     *
     * @param String $certificate_client client certificate
     *
     * @return String
     */
    public static function getCertificateSerial($certificate_client)
    {
        $x509 = new X509();

        $certificate = $x509->loadX509($certificate_client);

        /** @var BigInteger $serial */
        $serial = $certificate["tbsCertificate"]["serialNumber"];

        return $serial->toString();
    }

    /**
     * Verify that certificate is not revoked
     *
     * @param String $certificate_client String
     * @param String $list_revoked       String
     *
     * @return bool
     */
    public static function isRevoked($certificate_client, $list_revoked)
    {
        $certificate = self::getInformationCertificate($certificate_client);

        if (!$certificate) {
            return false;
        }

        $serial = self::getCertificateSerial($certificate_client);

        $x509 = new X509();
        $crl  = $x509->loadCRL($list_revoked);

        foreach ($crl["tbsCertList"]["revokedCertificates"] as $_cert) {
            /** @var BigInteger $_serial */
            $_serial = $_cert['userCertificate'];

            if ($_serial->toString() === $serial) {
                return false;
            }
        }

        return true;
    }

    /**
     * Convert a private key from the pkcs12 format to the pem format
     * Returns false on failure, an array containing the cert (public key) and the private key otherwise
     *
     * @param string $pkcs12            The content of the pkcs12 file
     * @param string $pkcs12_passphrase The passphrase used for deciphering the pkcs12 file
     * @param string $passphrase        The paspshrase that protect the private key
     *
     * @return bool|array
     */
    public static function convertPKCS12ToPEM($pkcs12, $pkcs12_passphrase, $passphrase = null)
    {
        $cert_data = null;
        $result    = openssl_pkcs12_read($pkcs12, $cert_data, $pkcs12_passphrase);
        if ($result || !array_key_exists('pkey', $cert_data) || !array_key_exists('cert', $cert_data)) {
            $pkey   = null;
            $result = openssl_pkey_export($cert_data['pkey'], $pkey, $passphrase);

            return ['cert' => $cert_data['cert'], 'pkey' => $pkey, 'extracerts' => $cert_data['extracerts']];
        }

        return false;
    }

    /**
     * Generate a random password according given specification.
     *
     * Todo: Handle case where no spec is provided and configurations are inconsistent (ie. "strong" stronger than
     * "admin").
     *
     * @param CPasswordSpec|string|null $spec
     * @param bool                      $remove_ambiguous
     *
     * @return false|string
     * @throws Exception
     */
    public static function getRandomPassword($spec = null, bool $remove_ambiguous = false)
    {
        if ($spec instanceof CPasswordSpec) {
            $object = new $spec->className();
            $field  = $spec->fieldName;
        } else {
            // $spec is a string or NULL
            $object = new CUser();
            $field  = '_random_password';

            switch ($spec) {
                case 'admin':
                    $spec = (new PasswordSpecBuilder($object))->getAdminSpec()->getSpec($field);
                    break;

                case 'strong':
                default:
                    $spec = (new PasswordSpecBuilder($object))->getStrongSpec()->getSpec($field);
            }
        }

        $object->_specs[$field] = $spec;

        $charset = $spec->getAllowedCharset();

        if ($remove_ambiguous) {
            $charset = array_values(array_diff($charset, static::AMBIGUOUS_CHARACTERS));
        }

        // "Weak" password spec is not allowed here
        if (!$charset) {
            return false;
        }

        do {
            $object->{$field} = CMbSecurity::getRandomAlphaNumericString($charset, $spec->minLength);
        } while ($spec->checkProperty($object));

        return $object->{$field};
    }

    /**
     * Verify the signature of a message
     *
     * @param RSA    $cipher     The cipher used
     * @param string $certificat The certificate
     * @param string $message    The original message
     * @param string $signature  The generated signature
     *
     * @return bool
     */
    public static function verify($cipher, $hash, $mode, $certificat, $message, $signature)
    {
        $cipher->setHash($hash);
        $cipher->setSignatureMode($mode);

        $openssl_pkey = openssl_pkey_get_public($certificat);
        $public_key   = openssl_pkey_get_details($openssl_pkey);

        $cipher->loadKey($public_key['key']);

        return $cipher->verify($message, $signature);
    }
}
