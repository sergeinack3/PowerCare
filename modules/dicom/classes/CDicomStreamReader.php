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
 * A stream reader who can read several types from a binary stream, in Big Endian or Little Endian syntax
 */
class CDicomStreamReader implements IShortNameAutoloadable {

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
   * The stream length
   *
   * @var integer
   */
  protected $stream_length = null;

  /**
   * The constructor of CDicomStreamReader
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
   * Return the stream length
   *
   * @return integer
   */
  function getStreamLength() {
    return $this->stream_length;
  }

  /**
   * Set the stream length
   *
   * @param integer $length The stream length
   *
   * @return void
   */
  function setStreamLength($length) {
    $this->stream_length = $length;
  }

  /**
   * Move forward the stream pointer
   *
   * @param int $bytes The number of bytes you want to skip. This number can't be negative
   *
   * @return void
   */
  function skip($bytes) {
    if ($bytes > 0) {
      $this->read($bytes);
    }
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
   * Move the stream pointer
   *
   * @param int $pos the position
   *
   * @return void
   */
  function seek($pos) {
    fseek($this->stream, $pos, SEEK_CUR);
  }

  /**
   * Rewind the position of the stream pointer
   *
   * @return void
   */
  function rewind() {
    rewind($this->stream);
  }

  /**
   * Read data from the stream, and check if the length of the PDU is passed
   *
   * @param integer $length The number of byte to read
   *
   * @return string
   */
  function read($length = 1) {
    $str = null;
    if ($length < 65535) {
      $str = fread($this->stream, $length);
      $this->buf .= $str;
    }

    return $str;
  }

  /**
   * Close the stream
   *
   * @return void
   */
  function close() {
    fclose($this->stream);
  }

  /**
   * Read hexadecimal numbers from the stream
   *
   * @param int    $length     The length of the number, equal to 1 if not given
   * @param string $endianness Equal to BE if you need Big Endian, LE if Little Endian. Equal to BE if not given
   *
   * @return int
   */
  function readHexByte($length = 1, $endianness = "BE") {
    $hex = null;
    if ($endianness == "BE") {
      $hex = $this->readHexByteBE($length);
    }
    elseif ($endianness == "LE") {
      $hex = $this->readHexByteLE($length);
    }

    return $hex;
  }

  /**
   * Read hexadecimal numbers from the stream. Use Big Endian syntax
   *
   * @param int $length The length of the number, equal to 1 if not given
   *
   * @return int
   */
  function readHexByteBE($length = 1) {
    return $this->unpack("H*", $this->read($length));
  }

  /**
   * Read hexadecimal numbers from the stream. Use Little Endian syntax
   *
   * @param int $length The length of the number, equal to 1 if not given
   *
   * @return int
   */
  function readHexByteLE($length = 1) {
    $hex = $this->unpack("H*", $this->read($length));
    return str_pad(strrev(dechex($hex)), $length * 2, 0, STR_PAD_LEFT);
  }

  /**
   * Read unsigned 32 bits numbers.
   *
   * @param string $endianness Equal to BE if you need Big Endian, LE if Little Endian. Equal to BE if not given
   *
   * @return integer
   */
  function readUInt32($endianness = "BE") {
    $int = null;

    if ($endianness == "BE") {
      $int = $this->readUInt32BE();
    }
    elseif ($endianness == "LE") {
      $int = $this->readUInt32LE();
    }

    return $int;
  }

  /**
   * Read unsigned 32 bits numbers, in Big Endian syntax.
   *
   * @return integer
   */
  function readUInt32BE() {
    return $this->unpack("N", $this->read(4));
  }

  /**
   * Read unsigned 32 bits numbers, in Little Endian syntax.
   *
   * @return integer
   */
  function readUInt32LE() {
    return $this->unpack("V", $this->read(4));
  }

  /**
   * Read 32 bits numbers.
   *
   * @param string $endianness Equal to BE if you need Big Endian, LE if Little Endian. Equal to BE if not given
   *
   * @return integer
   */
  function readInt32($endianness = "BE") {
    $int = 0;

    if ($endianness == "BE") {
      $int = $this->readUInt32BE();
    }
    elseif ($endianness == "LE") {
      $int = $this->readUInt32LE();
    }

    if ($int >= 0x80000000) {
      $int -= 0x100000000;
    }

    return $int;
  }

  /**
   * Read unsigned 16 bits numbers.
   *
   * @param string $endianness Equal to BE if you need Big Endian, LE if Little Endian. Equal to BE if not given
   *
   * @return integer
   */
  function readUInt16($endianness = "BE") {
    $int = null;

    if ($endianness == "BE") {
      $int = $this->readUInt16BE();
    }
    elseif ($endianness == "LE") {
      $int = $this->readUInt16LE();
    }

    return $int;
  }

  /**
   * Read unsigned 16 bits numbers, in Big Endian syntax.
   *
   * @return integer
   */
  function readUInt16BE() {
    return $this->unpack("n", $this->read(2));
  }

  /**
   * Read unsigned 16 bits numbers, in Big Endian syntax.
   *
   * @return integer
   */
  function readUInt16LE() {
    return $this->unpack("v", $this->read(2));
  }

  /**
   * Read 16 bits numbers.
   *
   * @param string $endianness Equal to BE if you need Big Endian, LE if Little Endian. Equal to BE if not given
   *
   * @return integer
   */
  function readInt16($endianness = "BE") {
    $int = 0;

    if ($endianness == "BE") {
      $int = $this->readUInt16BE();
    }
    elseif ($endianness == "LE") {
      $int = $this->readUInt16LE();
    }

    if ($int >= 0x8000) {
      $int -= 0x10000;
    }

    return $int;
  }

  /**
   * Read 8 bits numbers.
   *
   * @return integer
   */
  function readUInt8() {
    return $this->unpack("C", $this->read(1));
  }

  /**
   * Read unsigned 8 bits numbers.
   *
   * @return integer
   */
  function readInt8() {
    $int = $this->readUInt8();
    if ($int >= 0x80) {
      $int -= 0x100;
    }
    return $int;
  }

  /**
   * Read a string
   *
   * @param int $length The length of the string
   *
   * @return string
   */
  function readString($length) {
    return $this->unpack("A*", $this->read($length));
  }

  /**
   * Read a Dicom UID (series of integer, separated by ".")
   *
   * @param int $length The length of the UID, equal to 64 if not given
   *
   * @return string
   */
  function readUID($length = 64) {
    return $this->unpack("A*", $this->read($length));
  }

  /**
   * Unpacks from a binary string into an array according to the given format
   *
   * @param string $format The format code
   * @param string $data   The packed data
   *
   * @return string
   */
  function unpack($format, $data) {
    if (is_null($data) || $data == '') {
      return null;
    }

    $tmp = unpack($format, $data);
    return $tmp[1];
  }
} 
