<?php
/**
 * @package Mediboard\Core\FileUtil
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FileUtil;

use Exception;
use Ox\Core\CMbDT;

/**
 * FBExport file reader
 *
 * Tool for exporting and importing data with Firebird and InterBase databases
 *
 * @link http://fbexport.sourceforge.net/FBExport_file_format.html
 */
class CFBExportReader {
  protected $fields = array();
  protected $handle;
  protected $filesize;
  protected $field_count;
  protected $field_types = array();

  const TYPE_ARRAY     = 0;
  const TYPE_BLOB      = 1;
  const TYPE_DATE      = 2;
  const TYPE_TIME      = 3;
  const TYPE_TIMESTAMP = 4;
  const TYPE_STRING    = 5;
  const TYPE_SMALLINT  = 6;
  const TYPE_INT       = 7;
  const TYPE_BIGINT    = 8;
  const TYPE_FLOAT     = 9;
  const TYPE_DOUBLE    = 10;

  /**
   * FBExport file reader
   *
   * @param string $filename FBX file name
   */
  function __construct($filename) {
    $this->filesize = filesize($filename);

    $this->handle = fopen($filename, "rb");
    $h = $this->handle;

    // 1. Always zero
    fread($h, 1);

    // 2. FBExport file version, currently: 125
    fread($h, 1);

    // 3. Field count (number of columns)
    $this->field_count = min($this->readUByte(), 255);

    for ($i = 0; $i < $this->field_count; $i++) {
      $this->field_types[] = $this->readUByte();
    }
  }

  /**
   * Close file handle
   *
   * @return void
   */
  function close(){
    fclose($this->handle);
  }

  /**
   * Read unsiged byte
   *
   * @return int
   */
  function readUByte() {
    $d = fread($this->handle, 1);

    if ($d === false || $d === "") {
      return false;
    }

    $d = unpack("C", $d);
    return $d[1];
  }

  /**
   * Read string from file
   *
   * @param int $length The length of the string to read
   *
   * @return string
   */
  function readString($length) {
    $d = unpack("A*", fread($this->handle, $length));
    return $d[1];
  }

  /**
   * Read next string
   *
   * @return null|string
   */
  function readNextString() {
    $length = $this->readUByte();

    if ($length == 0) {
      return "";
    }

    // 0-253 Single byte length, just read it.
    if ($length < 254) {
      return $this->readString($length);
    }

    // 254 Special mark, the following two bytes represent the real length of data (byte1 * 256 + byte2)
    if ($length == 254) {
      $length = $this->readUByte() * 256 + $this->readUByte();
      return $this->readString($length);
    }

    // 255 NULL value
    return null;
  }

  /**
   * Read a data row
   *
   * @return array|null
   *
   * @throws Exception
   */
  function readLine() {
    // Because file size may exceed 32 bit limit
    $pos = ftell($this->handle); // pos may be < 0 when reaching signed 32bit max
    if ($pos < 0 || $this->filesize > 0 && $pos >= $this->filesize) {
      return null;
    }

    $row = array();

    foreach ($this->field_types as $_i => $_type) {
      switch ($_type) {
        case self::TYPE_ARRAY:
          // - not yet supported by FBExport -
          $length = $this->readUByte();
          $row[$_i] = $this->readString($length);
          break;

        case self::TYPE_BLOB:
          $is_null = $this->readUByte() == 0;
          $blob = $is_null ? null : "";

          if (!$is_null) {
            while (($length = intval($this->readString(4))) && $length > 0) {
              $blob .= $this->readString($length);
            }
          }

          $row[$_i] = $blob;
          break;

        case self::TYPE_DATE:
          $value = $this->readNextString();
          if ($value !== null) {
            $value = CMbDT::time("+$value DAYS", "1900-00-00");
          }
          $row[$_i] = $value;
          break;

        case self::TYPE_TIME:
          $value = $this->readNextString();
          if ($value !== null) {
            $value = CMbDT::time("+$value SECONDS", "00:00:00");
          }
          $row[$_i] = $value;
          break;

        case self::TYPE_TIMESTAMP:
          $value = $this->readNextString();
          if ($value !== null) {
            $value = preg_replace('/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', "\\1-\\2-\\3 \\4:\\5:\\6", $value);
          }
          $row[$_i] = $value;
          break;

        case self::TYPE_STRING:
          $row[$_i] = $this->readNextString();
          break;

        case self::TYPE_SMALLINT:
        case self::TYPE_INT:
        case self::TYPE_BIGINT:
          $value = $this->readNextString();
          if ($value !== null) {
            $value = intval($value);
          }

          $row[$_i] = $value;
          break;

        case self::TYPE_FLOAT:
        case self::TYPE_DOUBLE:
          $value = $this->readNextString();
          if ($value !== null) {
            $value = floatval($value);
          }

          $row[$_i] = $value;
          break;

        default:
          throw new Exception("Unknown type $_type");
          break;
      }
    }

    return $row;
  }
}
