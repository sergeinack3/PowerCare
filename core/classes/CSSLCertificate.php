<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

/**
 * Class for manipulate the certificate SSL
 */
class CSSLCertificate {

  public $certificate_path;
  public $certificate;
  public $chain;
  public $pivate_key;
  public $passphrase;
  public $private_key_handle;


  /**
   * Construct
   *
   * @param String $certificate P12 file
   * @param String $passphrase  Passphrase for the certificate
   * @param bool   $path        The certificate variable is a path
   */
  function __construct($certificate, $passphrase = null, $path = true) {
    if ($path) {
      $this->certificate_path = $certificate;
      $certificate            = file_get_contents($certificate);
      //Il est important que le certificat soit en p12
      openssl_pkcs12_read($certificate, $array_cert, $passphrase);
      $this->certificate = $array_cert["cert"];
      $this->pivate_key  = $array_cert["pkey"];
      $this->chain       = $array_cert["extracerts"];
    }
    else {
      $this->certificate = $certificate;
    }

    $this->passphrase = $passphrase;
  }

  /**
   * Return the certificate with or without the header
   *
   * @param boolean $withHeader 'Begin certificate' present
   *
   * @return String
   */
  function getCertificate($withHeader = true) {
    if ($withHeader) {
      return $this->certificate;
    }

    return self::deleteHeader($this->certificate);
  }

  /**
   * Delete the header of the certificate
   *
   * @param String $certificate Certificate
   *
   * @return String
   */
  static function deleteHeader($certificate) {
    preg_match_all("#(?<=-{5})[^-]+(?=-{5}\\w)#", $certificate, $matches);

    //On supprime les retour chariot potentielle pour un certificat avec la bonne forme
    $certificate = str_replace("\n", "", $matches[0][0]);
    $certificate = wordwrap($certificate, 64, "\n", true);

    return $certificate;
  }

  /**
   * Return a resource of the private key
   *
   * @return resource
   */
  function getPrivateKey() {
    $this->private_key_handle = openssl_pkey_get_private($this->pivate_key, $this->passphrase);
    return $this->private_key_handle;
  }

  /**
   * Sign the data with the certificate
   *
   * @param String  $data      Data to sign
   * @param boolean $base64    Encode the resut ot base 64
   * @param int     $algorithm Algorithm to use
   *
   * @return string
   */
  function sign($data, $base64 = true, $algorithm = OPENSSL_ALGO_SHA1) {
    if (!$this->private_key_handle) {
      $this->getPrivateKey();
    }

    openssl_sign($data, $sign_openssl, $this->private_key_handle, $algorithm);

    if ($base64) {
      $sign_openssl = base64_encode($sign_openssl);
    }

    return $sign_openssl;
  }

  /**
   * Return the issuer of the certificate
   *
   * @param boolean $rfc representation RFC of the dn
   *
   * @return String
   */
  function getIssuerDn($rfc = false) {
    $dn = CMbSecurity::getDNString($this->certificate);
    $dn = utf8_decode($dn);
    if (!$rfc) {
      return $dn;
    }

    preg_match_all("#[^,]+#", $dn, $match);
    $rdn = "";
    $separator = "+";
    $match = array_reverse($match[0]);
    foreach ($match as $_dn) {
      if (strpos(current($match), "OU=") !== false) {
        $separator = ",";
      }
      $rdn .= trim($_dn).$separator;
    }

    $rdn = substr($rdn, 0, -1);
    return $rdn;
  }

  /**
   * Return the certificate to array format
   *
   * @return String[]
   */
  function getCertificateToArray() {
    return CMbSecurity::getInformationCertificate($this->certificate);
  }

  /**
   * Return the fingerPrint of the certificate
   *
   * @param String $certificate PEM Certificate
   *
   * @return string
   */
  static function getFingerPrint($certificate) {

    $pem = preg_replace('/\-+BEGIN CERTIFICATE\-+/', '', $certificate);
    $pem = preg_replace('/\-+END CERTIFICATE\-+/', '', $pem);
    $pem = str_replace(array("\n","\r"), '', trim($pem));

    $result = sha1(base64_decode($pem), true);

    return $result;
  }
}