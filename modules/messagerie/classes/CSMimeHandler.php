<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Mediboard\System\CSourcePOP;

/**
 * Description
 */
class CSMimeHandler implements IShortNameAutoloadable {

  /**
   * Decrypt a mail encrypted by the S/MIME format
   *
   * @param string     $encrypted_mail The MIME headers and the body of the mail
   * @param CSourcePOP $source         The POP source from which the mail was get
   *
   * @return string|bool
   */
  public static function decryptSMime($encrypted_mail, $source) {
    $mail = false;

    /* Create the temp files needed by the openSSL functions */
    $encrypted = self::createTempFile('crypted_');
    $signed = self::createTempFile('signed_');
    $decrypted = self::createTempFile('decrypted_');

    file_put_contents($encrypted, $encrypted_mail);

    /* Get the certificate and the private key from the PEM file */
    $certificate = self::getCertificateFor($source);
    $private = self::getPrivateKeyFor($source);

    /* Decrypt the S/Mime mail, and put the content in the signed file */
    $result = openssl_pkcs7_decrypt($encrypted, $signed, $certificate, $private);

    /* If the decryption is successfull */
    if ($result) {
      $cert_path = self::createTempFile('cert');
      file_put_contents($cert_path, $certificate);

      /* Check the signature (for now we use the source's certificate, and not the sender cert) */
      $result = openssl_pkcs7_verify($signed, 0, $cert_path, array(), $cert_path, $decrypted);

      if ($result === -1) {
        $error = openssl_error_string();
        $decrypted_mail = false;
      }
      else {
        /* Whereas the signature is verified or not, the signed part will be removed, giving us a raw mail in mime format */
        $mail = file_get_contents($decrypted);
        /* After the decryption and signature verification, the mail is encoded in quoted printable format */
        $mail = self::quotedPrintableDecode($mail);
      }
    }
    else {
      $error = openssl_error_string();
      $decrypted_mail = false;
    }

    @unlink($encrypted);
    @unlink($signed);
    @unlink($decrypted);

    return $mail;
  }

  /**
   * Put the given public key in a file
   *
   * @param CSourcePOP $source The POP source
   * @param string     $cert   The public key
   *
   * @return bool
   */
  public static function setCertificateFor($source, $cert) {
    $path = self::getCertificatePath($source);

    $result = false;
    if ($path) {
      $result = file_put_contents($path, utf8_encode($cert));
    }

    return $result;
  }

  /**
   * Return the public key for the given source
   *
   * @param CSourcePOP $source The POP source
   *
   * @return string
   */
  protected static function getCertificateFor($source) {
    $path = self::getCertificatePath($source);

    $result = false;
    if ($path) {
      $result = file_get_contents($path);
    }

    return $result;
  }

  /**
   * Return the path to the certificate for the given source
   *
   * @param CSourcePOP $source The POP source
   *
   * @return bool|string
   */
  public static function getCertificatePath($source) {
    $directory = self::getCertificateDirectoryPath($source);

    $path = false;
    if ($directory) {
      $path = "{$directory}cert.pem";
    }

    return $path;
  }

  /**
   * Export the private key from the PEM file
   *
   * @param CSourcePOP $source The POP source
   *
   * @return string
   */
  public static function getPrivateKeyFor($source) {
    $private = null;
    $result = openssl_pkey_export(self::getCertificateFor($source), $private, self::getPassphraseFor($source));
    if (!$result) {
      $error = openssl_error_string();
    }

    return $private;
  }

  /**
   * Return the passphrase used for protecting the private key
   *
   * @param CSourcePOP $source The source POP
   *
   * @return string
   */
  protected static function getPassphraseFor($source) {
    /** @var CSMimeKey $smime_key */
    $smime_key = $source->loadUniqueBackRef('smime_key');
    return $smime_key->getPassphrase();
  }

  /**
   * Return the path of the certifcates directory for the given source.
   * If no source is given, return the master directory for the certificates
   *
   * @param CSourcePOP $source The source POP
   *
   * @return string|bool
   */
  public static function getCertificateDirectoryPath($source = null) {
    $directory = CAppUI::conf('messagerie hprimnet_certificates_directory');

    if ($directory != '' && file_exists($directory)) {
      if ($source) {
        $directory = $directory[strlen($directory) -1] != '/' ? $directory . "/$source->_guid/": $directory . "$source->_guid/";
        if (!file_exists($directory)) {
          mkdir($directory, 0700);
        }
      }
    }
    else {
      $directory = false;
    }

    return $directory;
  }

  /**
   * Return the path of the key used to cipher the passphrase linked to the user's certificates
   *
   * @return string
   */
  public static function getMasterKeyPath() {
    $directory = CAppUI::conf('messagerie hprimnet_key_directory');
    $path = '';
    if ($directory != '' && file_exists($directory)) {
      $path = $directory[strlen($directory) -1] != '/' ? $directory . '/hprimnet.key' : $directory . 'hprimnet.key';
    }
    return $path;
  }

  /**
   * Return the key used to cipher the passphrases linked to the certificates
   * If the file doesn't exists, return false
   *
   * @return bool|string
   */
  public static function getMasterKey() {
    $path = self::getMasterKeyPath();

    return file_get_contents($path);
  }

  /**
   * Create a temporary file with the given prefix in the directory tmp/messagerie
   *
   * @param string $prefix The prefix to use
   *
   * @return string
   */
  protected static function createTempFile($prefix) {
    if (file_exists('tmp/messagerie')) {
      mkdir('tmp/messagerie', 0700);
    }

    return tempnam('tmp/messagerie', $prefix);
  }

  /**
   * Decode a string un Quoted Printable format, with a correction on the line's length
   *
   * @param string $str The string to decode
   *
   * @return string
   */
  public static function quotedPrintableDecode($str) {
    return quoted_printable_decode(str_replace("=20\r\n", '', $str));
  }
}
