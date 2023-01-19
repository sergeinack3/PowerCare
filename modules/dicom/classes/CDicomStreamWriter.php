<?php
/**
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Dicom;

use Ox\Core\Autoload\IShortNameAutoloadable;

/**
 * A stream writer who can write several types from a binary stream, in Big Endian or Little Endian syntax
 */
class CDicomStreamWriter implements IShortNameAutoloadable {
  
  /**
   * The stream (usually a stream to the socket connexion)
   * 
   * @var resource
   */
  public $stream;
  
  /**
   * The content of the stream, used to keep a trace of the DICOM exchanges
   * 
   * @var string
   */
  public $buf;
  
  /**
   * The constructor of CDicomStreamwriteer
   * 
   * @param resource $stream The stream
   */
  function __construct($stream = null) {
    if (is_null($stream)) {
      $stream = fopen("php://temp", "w+");
    }
    
    $this->stream = $stream;
    $this->buf = "";
  }
  
  /**
   * Write a number of bytes equal to 0
   * 
   * @param int $bytes The number of bytes you want to skip. This number can't be negative
   * 
   * @return void|int
   */
  function skip($bytes) {
    if ($bytes > 0) {
      $bin = "";
      for ($i = 0; $i < $bytes; $i++) {
        $bin .= pack("H*", "00");
      }
      return $this->write($bin, $bytes);
    }
    return;
  }
  
  /**
   * Write the contents of str to the stream
   * 
   * @param string  $str    The string
   * 
   * @param integer $length The length of the string
   * 
   * @return integer or null
   */
  function write($str, $length = 1) {
    $this->buf .= $str;
    return fwrite($this->stream, $str, $length);
  }
  
  /**
   * Return the current position of the stream pointer.
   * 
   * @return int The current position of the stream pointer
   */
  function getPos() {
    return ftell($this->stream);
  }
  
  /**
   * Close the stream
   * 
   * @return null
   */
  function close() {
    fclose($this->stream);
  }
  
  /**
   * Rewind the position of the stream pointer
   * 
   * @return null
   */
  function rewind() {
    rewind($this->stream);
  }
    
  /**
   * Write hexadecimal numbers from the stream
   *  
   * @param integer $hexa       The hexadecimal string
   * @param int     $length     The length of the number, equal to 1 if not given
   * @param string  $endianness Equal to BE if you need Big Endian, LE if Little Endian. Equal to BE if not given
   * 
   * @return integer|void on error
   */
  function writeHexByte($hexa, $length = 1, $endianness = "BE") {
    if ($endianness == "BE") {
      return $this->writeHexByteBE($hexa, $length);
    }
    elseif ($endianness == "LE") {
      return $this->writeHexByteLE($hexa, $length);
    }
  }
  
  /**
   * Write hexadecimal numbers from the stream. Use Big Endian syntax
   * 
   * @param integer $hexa   The hexadecimal string
   * @param int     $length The length of the number, equal to 1 if not given
   * 
   * @return integer or false on error
   */
  function writeHexByteBE($hexa, $length = 1) {
    $bin = pack("H*", $hexa);
    return $this->write($bin, $length);
  }
  
  /**
   * Write hexadecimal numbers from the stream. Use Little Endian syntax
   * 
   * @param integer $hexa   The hexadecimal string
   * @param int     $length The length of the number, equal to 1 if not given
   * 
   * @return integer or false on error
   */
  function writeHexByteLE($hexa, $length = 1) {
    $bin = pack("C*", str_pad(strrev(hexdec($hexa)), $length*2, 0, STR_PAD_LEFT));
    return $this->write($bin, $length);
  }
  
  /**
   * Write unsigned 32 bits numbers.
   * 
   * @param integer $int        The unsigned integer
   * @param string  $endianness Equal to BE if you need Big Endian, LE if Little Endian. Equal to BE if not given
   * 
   * @return integer or false on error
   */
  function writeUInt32($int, $endianness = "BE") {
    if ($endianness == "BE") {
      return $this->writeUInt32BE($int);
    }
    elseif ($endianness == "LE") {
      return $this->writeUInt32LE($int);
    }
  }
  
  /**
   * Write unsigned 32 bits numbers, in Big Endian syntax.
   * 
   * @param integer $int The unsigned integer
   * 
   * @return integer or false on error
   */
  function writeUInt32BE($int) {
    $bin = pack("N", $int);
    return $this->write($bin, 4);
  }
  
  /**
   * Write unsigned 32 bits numbers, in Little Endian syntax.
   * 
   * @param integer $int The unsigned integer
   * 
   * @return integer or false on error
   */
  function writeUInt32LE($int) {
    $bin = pack("V", $int);
    return $this->write($bin, 4);
  }
  
  /**
   * Write unsigned 16 bits numbers.
   * 
   * @param integer $int        The unsigned integer
   * @param string  $endianness Equal to BE if you need Big Endian, LE if Little Endian. Equal to BE if not given
   * 
   * @return integer or false on error
   */
  function writeUInt16($int, $endianness = "BE") {
    if ($endianness == "BE") {
      return $this->writeUInt16BE($int);
    }
    elseif ($endianness == "LE") {
      return $this->writeUInt16LE($int);
    }
  }
  
  /**
   * Write unsigned 16 bits numbers, in Big Endian syntax.
   * 
   * @param integer $int The unsigned integer
   * 
   * @return integer or false on error
   */
  function writeUInt16BE($int) {
    $bin = pack("n", $int);
    return $this->write($bin, 2);
  }
  
  /**
   * Write unsigned 16 bits numbers, in Big Endian syntax.
   * 
   * @param integer $int The unsigned integer
   * 
   * @return integer or false on error
   */
  function writeUInt16LE($int) {
    $bin = pack("v", $int);
    return $this->write($bin, 2);
  }
  
  /**
   * Write unsigned 8 bits numbers.
   * 
   * @param integer $int The unsigned integer
   * 
   * @return integer or false on error
   */
  function writeUInt8($int) {
    $bin = pack("C", $int);
    return $this->write($bin, 1);
  }
  
  /**
   * Write 32 bits numbers.
   * 
   * @param integer $int        The integer
   * 
   * @param string  $endianness Equal to BE if you need Big Endian, LE if Little Endian. Equal to BE if not given
   * 
   * @return integer or false on error
   */
  function writeInt32($int, $endianness = "BE") {
    if ($int < 0) {
      $int += 0x100000000;
    }
    if ($endianness == "BE") {
      return $this->writeUInt32BE($int);
    }
    elseif ($endianness == "LE") {
      return $this->writeUInt32LE($int);
    }
  }

  
  /**
   * Write 16 bits numbers.
   * 
   * @param integer $int        The integer
   * 
   * @param string  $endianness Equal to BE if you need Big Endian, LE if Little Endian. Equal to BE if not given
   * 
   * @return integer or false on error
   */
  function writeInt16($int, $endianness = "BE") {
    if ($int < 0) {
      $int += 0x10000;
    }
    if ($endianness == "BE") {
      return $this->writeUInt16BE($int);
    }
    elseif ($endianness == "LE") {
      return $this->writeUInt16LE($int);
    }
  }

  /**
   * Write 8 bits numbers.
   * 
   * @param integer $int The integer
   * 
   * @return integer or false on error
   */
  function writeInt8($int) {
    if ($int < 0) {
      $int += 0x100;
    }
    return $this->writeUInt8($int);
  }

  /**
   * Write a string
   * 
   * @param string $str    The string
   * 
   * @param int    $length The length of the string
   * 
   * @return integer or false on error
   */
  function writeString($str, $length) {
    $bin = pack("A*", $str);
    return $this->write($bin, $length);
  }
  
  /**
   * Write a Dicom UID (series of integer, separated by ".")
   * 
   * @param string $uid    The UID
   * 
   * @param int    $length The length of the UID, equal to 64 if not given
   * 
   * @return integer or false on error
   */
  function writeUID($uid, $length = 64) {
    $bin = pack("A*", $uid);
    return $this->write($bin, $length);
  }
}