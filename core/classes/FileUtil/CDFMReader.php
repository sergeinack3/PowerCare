<?php
/**
 * @package Mediboard\Core\FileUtil
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FileUtil;

use Exception;

/**
 * Binary Delphi Form reader (in DFM files)
 */
class CDFMReader {
  protected $handle;
  protected $filesize;

  const HEADER = "TPF0";

  const TYPE_VARIANT     = 0x00;
  const TYPE_ARRAY       = 0x01;
  const TYPE_BYTE        = 0x02;
  const TYPE_WORD        = 0x03;
  const TYPE_DWORD       = 0x04;
  const TYPE_DOUBLE      = 0x05;
  const TYPE_STRING      = 0x06;
  const TYPE_ENUM        = 0x07;
  const TYPE_BOOL_FALSE  = 0x08;
  const TYPE_BOOL_TRUE   = 0x09;
  const TYPE_BITMAP      = 0x0A;
  const TYPE_SET         = 0x0B;
  const TYPE_LONGSTRING2 = 0x0C;
  const TYPE_NIL         = 0x0D;
  const TYPE_RECORD      = 0x0E;
  const TYPE_UNICODE     = 0x12;
  const TYPE_LONGSTRING  = 0x14;

  /** @var CDFMObject[] */
  public $objects = array();

  public $data = array();

  protected $endReached = false;

  /** @var CDFMObject[] */
  protected $containerStack = array();

  /**
   * DFM file reader
   *
   * @param string $filename DFM file name
   *
   * @throws Exception
   */
  function __construct($filename) {
    $this->filesize = filesize($filename);
    $this->handle = fopen($filename, "rb");

    $header = $this->readString(4);
    if ($header != self::HEADER) {
      throw new Exception("Wrong file format, expected '".self::HEADER."' in header");
    }

    // Handle weird header with repeating TPF0
    $orig_pos = $this->pos();
    $check_weird_header = $this->readString(80);
    if ($pos = strpos($check_weird_header, "\0\0".self::HEADER)) {
      fseek($this->handle, $pos+10, SEEK_SET);
    }
    else {
      fseek($this->handle, $orig_pos, SEEK_SET);
    }
  }

  /**
   * Parse the fil and fill the $this->objects array
   *
   * @return CDFMObject[]
   */
  function parse() {
    while (true) {
      $object = $this->readObject();

      if (!$object) {
        break;
      }

      if (!$object->ischild) {
        $this->objects[] = $object;
      }
    }

    return $this->objects;
  }

  /**
   * Move the stream pointer
   *
   * @param int $pos the position
   *
   * @return void
   */
  function seek($pos) {
    fseek($this->handle, $pos, SEEK_CUR);
  }

  /**
   * Tell the current position in the file
   *
   * @return int
   */
  function pos(){
    return ftell($this->handle);
  }

  /**
   * @return bool|CDFMObject
   */
  function readObject(){
    if ($this->isEndReached()) {
      return false;
    }

    $object = new CDFMObject(
      $this->readPascalString(),
      $this->readPascalString()
    );

    if ($this->isEndReached()) {
      return false;
    }

    $next = $this->mungeByte();

    if ($next !== false) {
      while ($next != 0) {
        list($key, $value) = $this->readLabelledValue();

        $key = strtolower($key);
        $key = str_replace(".", "_", $key);

        $object->$key = $value;

        $next = $this->mungeByte();

        if ($next === false) {
          break;
        }
      }
    }

    $count = 0;
    while ($next === 0) {
      $next = $this->readUByte();

      $count++;
    }

    $object->zeros = $count;

    if (isset($this->data[$object->id])) {
      $object->valeur = $this->data[$object->id];
    }

    // 2 => next is child of self
    // 3 => next is child of parent (sibling)
    // 4 => self is the last child of parent

    $top = end($this->containerStack);
    $parent = ($top != $object ? $top : null);

    if ($parent) {
      $parent->children[] = $object;
      $object->parent = $parent->id;
      $object->ischild = true;
    }

    switch ($count) {
      case 2:
        $this->containerStack[] = $object;
        break;

      case 3:
        break;

      case 4:
        array_pop($this->containerStack);
        break;

      default: // more zeros ...
        if ($this->pos() >= $this->filesize) {
          $this->endReached = true;
        }
        break;
    }

    // Color
    $object->csscolor = self::parseColor($object->color);

    $this->seek(-1);

    return $object;
  }

  function isEndReached() {
    return $this->endReached || $this->pos() >= $this->filesize - 1;
  }

  /**
   * Read a byte and rewind to the position before the read
   *
   * @return int
   */
  function mungeByte() {
    $return = $this->readUByte();
    $this->seek(-1);

    return $return;
  }

  /**
   * Read a Pascal string (length followed wy the string)
   *
   * @return string
   */
  function readPascalString() {
    $l = $this->readUByte();

    if ($l == 0) {
      return "";
    }

    return $this->readString($l);
  }

  /**
   * Read a labelled value
   *
   * @return array An array (name, value)
   */
  function readLabelledValue() {
    $l = $this->readUByte();

    $name = $this->readString($l);

    $type = $this->readUByte();
    $value = null;

    switch ($type) {
      case self::TYPE_VARIANT:
      case self::TYPE_NIL:
        break;

      case self::TYPE_BYTE:
        $value = $this->readUByte();
        break;

      case self::TYPE_ARRAY:
        $type = $this->readUByte();

        $value = array();
        while ($type) {
          $length  = $this->readUByte();
          $value[] = ($length > 0) ? $this->readString($length) : null;

          $type = $this->readUByte();
        }
        break;

      case self::TYPE_WORD:
        $value = $this->readInt16();
        break;

      case self::TYPE_DWORD:
        $value = $this->readUInt32();
        break;

      case self::TYPE_DOUBLE:
        $value = $this->read(10);
        break;

      case self::TYPE_ENUM:
      case self::TYPE_STRING:
        $length = $this->readUByte();
        $value = ($length > 0) ? $this->readString($length) : null;
        break;

      case self::TYPE_BOOL_FALSE:
        $value = false;
        break;

      case self::TYPE_BOOL_TRUE:
        $value = true;
        break;

      case self::TYPE_BITMAP:
        $this->readUInt16();

        $this->read(2); // 0x0000

        $type = $this->readPascalString();

        $length = $this->readUInt16();

        $this->read(2); // 0x0000

        $data = $this->read($length);

        $value = array(
          "type" => $type,
          "data" => $data,
        );

        break;

      case self::TYPE_SET:
        $length = $this->readUByte();
        $value = array();
        while ($length) {
          $value[] = $this->readString($length);
          $length = $this->readUByte();
        }
        break;

      case self::TYPE_LONGSTRING:
      case self::TYPE_LONGSTRING2:
        $length = $this->readUInt32();
        $value = $this->readString($length);
        break;
    }

    return array($name, $value);
  }

  /**
   * Close file handle
   *
   * @return void
   */
  function close(){
    fclose($this->handle);
  }

  function read($length) {
    return fread($this->handle, $length);
  }

  /**
   * Read unsiged byte
   *
   * @return int
   */
  function readUByte() {
    $d = $this->read(1);

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
    $d = unpack("A*", $this->read($length));
    return $d[1];
  }

  /**
   * Read unsigned 16 bits numbers
   *
   * @return integer
   */
  function readUInt16() {
    $tmp = unpack("v", $this->read(2));
    return $tmp[1];
  }

  /**
   * Read 16 bits numbers.
   *
   * @return integer
   */
  function readInt16() {
    $int = $this->readUInt16();

    if ($int >= 0x8000) {
      $int -= 0x10000;
    }

    return $int;
  }

  /**
   * Read unsigned 32 bits numbers
   *
   * @return integer
   */
  function readUInt32() {
    $tmp = unpack("V", $this->read(4));
    return $tmp[1];
  }

  function fillFromContent($content) {
    $objects = self::parseTextDFM($content);

    $data = array();
    foreach ($objects as $_key => $_object) {
      if (isset($_object->valeur)) {
        $data[$_key] = $_object->valeur;
      }
    }

    return $this->data = $data;
  }

  /**
   * Parse a text DFM file into a list of objects
   *
   * @param string $content Text DFM content
   *
   * @return CDFMObject[]
   */
  static function parseTextDFM($content) {
    $lines = explode("\r\n", $content);

    $objects = array();
    foreach ($lines as $_line) {
      if (trim($_line) == "") {
        continue;
      }

      list($_id, $_data) = explode("=", $_line, 2);

      $_matches = array();
      preg_match_all('/"?([^=]+)=([^,"]+)"?,?/', $_data, $_matches, PREG_SET_ORDER);

      $_item_data = array();
      foreach ($_matches as $_match) {
        $_key = str_replace(".", "_", strtolower($_match[1]));
        $_item_data[$_key] = $_match[2];
      }

      $_object = new CDFMObject(
        $_item_data["classname"],
        $_id
      );
      foreach ($_item_data as $_field => $_data) {
        $_object->{$_field} = $_data;
      }

      $objects[$_id] = $_object;
    }

    return $objects;
  }

  /**
   * Build a tree from a text DFM content
   *
   * @param string $content
   *
   * @return CDFMObject[]
   */
  static function buildTreeFromTextDFM($content) {
    $objects = self::parseTextDFM($content);

    foreach ($objects as $_id => $_object) {
      $_object->ischild = false;

      // Color
      $_object->csscolor = self::parseColor($_object->color);

      $_parent = $_object->parent;

      if ($_parent && $_parent != "Scene" && isset($objects[$_parent])) {
        if (!isset($objects[$_parent]->children)) {
          $objects[$_parent]->children = array();
        }

        $_object->ischild = true;
        $objects[$_parent]->children[$_id] = $_object;
      }
    }

    foreach ($objects as $_id => $_object) {
      if ($_object->ischild) {
        unset($objects[$_id]);
      }
    }

    return $objects;
  }

  /**
   * Parse a 32bit color
   *
   * @param int $color Color in 32bit integer format
   *
   * @return null|string
   */
  static function parseColor($color) {
    if ($color > 0) {
      return sprintf("#%06X", 0xFFFFFF & $color);
    }

    return null;
  }
}
