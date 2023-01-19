<?php
/**
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Dicom\Data;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Interop\Dicom\CDicomStreamReader;
use Ox\Interop\Dicom\CDicomStreamWriter;

/**
 * Represent a Dicom data set
 */
class CDicomDataSet implements IShortNameAutoloadable {
  
  /**
   * The group number
   * 
   * @var integer
   */
  protected $group_number = null;
  
  /**
   * The element number
   * 
   * @var integer
   */
  protected $element_number = null;
  
  /**
   * The name of the element
   * 
   * @var string
   */
  protected $name = null;
  
  /**
   * The value representation of the element
   * 
   * @var string
   */
  protected $vr = null;
  
  /**
   * The value multiplicity
   * 
   * @var string
   */
  protected $vm = null;
  
  /** 
   * The length
   * 
   * @var integer
   */
  protected $length = null;
  
  /**
   * The value
   * 
   * @var mixed
   */
  protected $value = null;
  
  /**
   * The transfer syntax
   * 
   * @var string
   */
  protected $transfer_syntax;
  
  /**
   * The constructor.
   * 
   * @param array $datas Default null. 
   * You can set all the field of the class by passing an array, the keys must be the name of the fields.
   */
  function __construct(array $datas = array()) {
    foreach ($datas as $key => $value) {
      $words = explode('_', $key);
      $method = 'set';
      foreach ($words as $_word) {
        $method .= ucfirst($_word);
      }
      if (method_exists($this, $method)) {
        $this->$method($value);
      }
    }
    
    if (!is_null($this->group_number) && !is_null($this->element_number)) {
      $this->setDataSet();
    }
  }
  
  /**
   * Set the group number
   * 
   * @param integer $group The group number
   * 
   * @return null
   */
  public function setGroupNumber($group) {
    $this->group_number = $group;
  }
  
  /**
   * Get the group number
   * 
   * @return integer
   */
  public function getGroupNumber() {
    return $this->group_number;
  }
  
  /**
   * Set the element number
   * 
   * @param integer $element The element number
   * 
   * @return null
   */
  public function setElementNumber($element) {
    $this->element_number = $element;
  }
  
  /**
   * Get the element number
   * 
   * @return integer
   */
  public function getElementNumber() {
    return $this->element_number;
  }
  
  /**
   * Set the name
   * 
   * @param string $name The name
   * 
   * @return null
   */
  public function setName($name) {
    $this->name = $name;
  }
  
  /**
   * Get the name
   * 
   * @return string
   */
  public function getName() {
    return $this->name;
  }
  
  /**
   * Set the value representation
   * 
   * @param string $vr The value representation
   * 
   * @return null
   */
  public function setVr($vr) {
    $this->vr = $vr;
  }
  
  /**
   * Get the value representation
   * 
   * @return string
   */
  public function getVr() {
    return $this->vr;
  }
  
  /**
   * Set the value multiplicity
   * 
   * @param string $vm The value multiplicity
   * 
   * @return null
   */
  public function setVm($vm) {
    $this->vm = $vm;
  }
  
  /**
   * Get the value multiplicity
   * 
   * @return string
   */
  public function getVm() {
    return $this->vm;
  }

  /**
   * Set the length of the value
   *
   * @param integer $length The length
   *
   * @return void
   */
  public function setLength($length) {
    $this->length = $length;
  }

  /**
   * Get the length of the value
   *
   * @return integer
   */
  public function getLength() {
    return $this->length;
  }
  
  /**
   * Set the value
   * 
   * @param mixed $value The value
   * 
   * @return null
   */
  public function setValue($value) {
    $this->value = $value;
  }
  
  /**
   * Get the value
   * 
   * @return mixed
   */
  public function getValue() {
    return $this->value;
  }
  
  /**
   * Get the data set definition from the DICOM dictionary,
   * and set the vr, the vm and the name.
   * 
   * @return null
   */
  public function setDataSet() {
    if (is_array($dataset = CDicomDictionary::getDataSet($this->group_number, $this->element_number))) {
      $this->vr = $dataset[0];
      $this->vm = $dataset[1];
      $this->name = $dataset[2];
    }
  }

  /**
   * Return the dataset with the given group and element numbers from a sequenced dataset
   *
   * @param integer $group_number   The group number
   * @param integer $element_number The element number
   *
   * @return null|CDicomDataSet
   */
  public function getSequenceDataSet($group_number, $element_number) {
    if ($this->vr != 'SQ' || !is_array($this->value)) {
      return null;
    }

    foreach ($this->value as $_sequence) {
      foreach ($_sequence as $_dataset) {
        if ($_dataset->getGroupNumber() == $group_number && $_dataset->getElementNumber() == $element_number) {
          return $_dataset;
        }
      }
    }

    return null;
  }
  
  /**
   * Calculate the length of the value
   * 
   * @return null
   */
  protected function calculateLength() {
    $vr_def = CDicomDictionary::getValueRepresentation($this->vr);
    if ($vr_def['Fixed'] == 1) {
      $this->length = $vr_def['Length'];
    }
    elseif (is_array($this->value)) {
      $this->length = 0;
    }
    else {
      $this->length = strlen($this->value);
    }
  }
  
  /**
   * Return the total length of the dataset in bytes
   * 
   * @param string $transfer_syntax The transfer syntax
   * 
   * @return integer
   */
  function getTotalLength($transfer_syntax) {
    $this->calculateLength();
    $this->setDataSet();
    if (!in_array($this->vr, array("OB", "OW", "OF", "SQ", "UT", "UN"))) {
      return 8 + $this->length;
    }
    switch ($transfer_syntax) {
      case "1.2.840.10008.1.2" :
        return 8 + $this->length;
      case "1.2.840.10008.1.2.1" :
      case "1.2.840.10008.1.2.2" :
        return 12 + $this->length;
      default :
        
        break;
    }
  }
  
  /**
   * Encode the dataset, depending on the transfer syntax
   * 
   * @param CDicomStreamWriter $stream_writer   The stream writer
   * 
   * @param string             $transfer_syntax The UID of the transfer syntax
   * 
   * @return null
   */
  public function encode(CDicomStreamWriter $stream_writer, $transfer_syntax = "1.2.840.10008.1.2") {
    if (!$transfer_syntax) {
      return;
    }
    $this->transfer_syntax = $transfer_syntax;
    
    $vr_encoding = "";
    $endianness = "";
    switch ($transfer_syntax) {
      case "1.2.840.10008.1.2" :
        $vr_encoding = "Implicit";
        $endianness = "LE";
        break;
      case "1.2.840.10008.1.2.1" :
        $vr_encoding = "Explicit";
        $endianness = "LE";
        break;
      case "1.2.840.10008.1.2.2" :
        $vr_encoding = "Explicit";
        $endianness = "BE";
        break;
      default :
        
        break;
    }
    $this->calculateLength();
    $method = "encode$vr_encoding";
    $this->$method($stream_writer, $endianness);
  }
  
  /**
   * Encode the data set with the implicit VR
   * 
   * @param CDicomStreamWriter $stream_writer The stream writer
   * 
   * @param string             $endianness    The endianness, must be equal to "BE" (Big Endian) or "LE" (Little Endian)
   * 
   * @return null
   */
  protected function encodeImplicit(CDicomStreamWriter $stream_writer, $endianness) {
    $handle = fopen("php://temp", "w+");
    $value_writer = new CDicomStreamWriter($handle);
    $this->encodeValue($value_writer, $endianness);
    $value_writer->close();

    $stream_writer->writeUInt16($this->group_number, $endianness);
    $stream_writer->writeUInt16($this->element_number, $endianness);
    $stream_writer->writeUInt32($this->length, $endianness);
    $stream_writer->write($value_writer->buf);
  }
  
  /**
   * Encode the data set with the explicit VR
   * 
   * @param CDicomStreamWriter $stream_writer The stream writer
   * 
   * @param string             $endianness    The endianness, must be equal to "BE" (Big Endian) or "LE" (Little Endian)
   * 
   * @return null
   */
  protected function encodeExplicit(CDicomStreamWriter $stream_writer, $endianness) {
    $handle = fopen("php://temp", "w+");
    $value_writer = new CDicomStreamWriter($handle);
    $this->encodeValue($value_writer, $endianness);
    $value_writer->close();

    $stream_writer->writeUInt16($this->group_number, $endianness);
    $stream_writer->writeUInt16($this->element_number, $endianness);
    $stream_writer->writeString($this->vr, 2);
    if (in_array($this->vr, array("OB", "OW", "OF", "SQ", "UT", "UN"))) {
      $stream_writer->skip(2);
      $stream_writer->writeUInt32($this->length, $endianness);
    }
    else {
      $stream_writer->writeUInt16($this->length, $endianness);
    }
    $stream_writer->write($value_writer->buf);
  }
  
  /**
   * Encode the value
   * 
   * @param CDicomStreamWriter $stream_writer The stream writer
   * 
   * @param string             $endianness    The endianness, must be equal to "BE" (Big Endian) or "LE" (Little Endian)
   * 
   * @return null
   * 
   * @todo traiter les cas FL, FD, OB, OW, OF, SQ
   */
  protected function encodeValue(CDicomStreamWriter $stream_writer, $endianness) {
    switch ($this->vr) {
      case 'AE' :
      case 'AS' :
      case 'CS' :
      case 'DA' :
      case 'DS' :
      case 'DT' :
      case 'FL' :
      case 'FD' :
      case 'IS' :
      case 'LO' :
      case 'LT' :
      case 'OB' :
      case 'OF' :
      case 'OX' :
      case 'OW' :
      case 'PN' :
      case 'SH' :
      case 'ST' :
      case 'TM' :
      case 'UN' :
      case 'UT' :
        if ($this->length & 1) {
          $this->value .= " ";
          $this->length++;
        }

        $stream_writer->writeString($this->value, $this->length);
        break;
      case 'AT' :
        $stream_writer->writeUInt16($this->value[0], $endianness);
        $stream_writer->writeUInt16($this->value[1], $endianness);
        break;
      case 'SL' :
        $stream_writer->writeInt32($this->value, $endianness);
        break;
      case 'SS' :
        $stream_writer->writeInt16($this->value, $endianness);
        break;
      case 'UI' :
        $stream_writer->writeUID($this->value, $this->length);
        if ($this->length & 1) {
          $stream_writer->writeUInt8(0x00);
          $this->length++;
        }
        break;
      case 'UL' :
        $stream_writer->writeUInt32($this->value, $endianness);
        break;
      case 'US' :
        $stream_writer->writeUInt16($this->value, $endianness);
        break;
      case 'SQ' :
        $value_stream = new CDicomStreamWriter();
        $value_tmp = array();
        
        if (is_array($this->value)) {
          foreach ($this->value as $_sequence) {
            $sequence = array();
            
            $sequence_stream = new CDicomStreamWriter();
            
            foreach ($_sequence as $_item) {
              $dataset = new CDicomDataSet($_item);
              $dataset->encode($sequence_stream, $this->transfer_syntax);
              $sequence[] = $dataset;
            }
            
            $sequence_length = strlen($sequence_stream->buf);
            
            $value_stream->writeUInt16(0xFFFE, $endianness);
            $value_stream->writeUInt16(0xE000, $endianness);
            $value_stream->writeUInt32($sequence_length, $endianness);
            $value_stream->write($sequence_stream->buf, $sequence_length);
            $this->length += 8 + $sequence_length;
            
            $value_tmp[] = $sequence;
          }
          $stream_writer->write($value_stream->buf, $this->length);
          $this->value = $value_tmp;
        }
        else {
          $this->length = 8;
          $stream_writer->writeUInt16(0xFFFE, $endianness);
          $stream_writer->writeUInt16(0xE000, $endianness);
          $stream_writer->writeUInt32(0, $endianness);
        }
        break;
      default :
        break;
    }
  }
  
  /**
   * Decode the dataset, depending on the transfer syntax
   * 
   * @param CDicomStreamReader $stream_reader   The stream reader
   * 
   * @param string             $transfer_syntax The UID of the transfer syntax
   * 
   * @return null
   */
  public function decode(CDicomStreamReader $stream_reader, $transfer_syntax = "1.2.840.10008.1.2") {
    $this->transfer_syntax = $transfer_syntax;  
      
    $vr_encoding = "";
    $endianness = "";
    switch ($transfer_syntax) {
      case "1.2.840.10008.1.2" :
        $vr_encoding = "Implicit";
        $endianness = "LE";
        break;
      case "1.2.840.10008.1.2.1" :
        $vr_encoding = "Explicit";
        $endianness = "LE";
        break;
      case "1.2.840.10008.1.2.2" :
        $vr_encoding = "Explicit";
        $endianness = "BE";
        break;
    }

    $method = "decode$vr_encoding";
    if ($method != "decode" && method_exists($this, $method)) {
      $this->$method($stream_reader, $endianness);
    }
  }
  
  /**
   * Decode the data set with the implicit VR
   * 
   * @param CDicomStreamReader $stream_reader The stream reader
   * 
   * @param string             $endianness    The endianness, must be equal to "BE" (Big Endian) or "LE" (Little Endian)
   * 
   * @return null
   */
  protected function decodeImplicit(CDicomStreamReader $stream_reader, $endianness) {
    $this->group_number = $stream_reader->readUInt16($endianness);
    $this->element_number = $stream_reader->readUInt16($endianness);
    $this->length = $stream_reader->readUInt32($endianness);
    $this->setDataSet();
    if ($this->length > 0) {
      $this->decodeValue($stream_reader, $endianness);
    }
  }
  
  /**
   * Decode the data set with the explicit VR
   * 
   * @param CDicomStreamReader $stream_reader The stream reader
   * 
   * @param string             $endianness    The endianness, must be equal to "BE" (Big Endian) or "LE" (Little Endian)
   * 
   * @return null
   */
  protected function decodeExplicit(CDicomStreamReader $stream_reader, $endianness) {
    $this->group_number = $stream_reader->readUInt16($endianness);
    $this->element_number = $stream_reader->readUInt16($endianness);
    $this->vr = $stream_reader->readString(2);
    if (in_array($this->vr, array("OB", "OW", "OF", "SQ", "UT", "UN"))) {
      $stream_reader->skip(2);
      $stream_reader->readUInt32($endianness);
    }
    else {
      $stream_reader->readUInt16($endianness);
    }

    if (($this->length > 0 && $this->length < 0xFFFFFFFF) || $this->vr == "SQ") {
      $this->decodeValue($stream_reader, $endianness);
    }
  }
  
  /**
   * Decode the value
   * 
   * @param CDicomStreamReader $stream_reader The stream reader
   * @param string             $endianness    The endianness, must be equal to "BE" (Big Endian) or "LE" (Little Endian)
   * 
   * @return null
   * 
   * @todo traiter les cas FL, FD, OB, OW, OF, SQ
   */
  protected function decodeValue(CDicomStreamReader $stream_reader, $endianness) {
    switch ($this->vr) {
      case 'AE' :
      case 'AS' :
      case 'CS' :
      case 'DA' :
      case 'DS' :
      case 'DT' :
      case 'FL' :
      case 'FD' :
      case 'IS' :
      case 'LO' :
      case 'LT' :
      case 'OB' :
      case 'OF' :
      case 'OX' :
      case 'OW' :
      case 'PN' :
      case 'SH' :
      case 'ST' :
      case 'TM' :
      case 'UN' :
      case 'UT' :
        $this->value = $stream_reader->readString($this->length);
        break;
      case 'AT' :
        $this->value = array();
        $this->value[] = $stream_reader->readUInt16($endianness);
        $this->value[] = $stream_reader->readUInt16($endianness);
        break;
      case 'SL' :
        $this->value = $stream_reader->readInt32($endianness);
        break;
      case 'SS' :
        $this->value = $stream_reader->readInt16($endianness);
        break;
      case 'UI' :
        $this->value = $stream_reader->readUID($this->length);
        break;
      case 'UL' :
        $this->value = $stream_reader->readUInt32($endianness);
        break;
      case 'US' :
        $this->value = $stream_reader->readUInt16($endianness);
        break;
      case 'SQ' :
        $tmp_value = array();
        $value_stream = new CDicomStreamReader();

        /** Sequence of items with undefined length **/
        if ($this->length == 0xFFFFFFFF) {
          $delimiter = new CDicomDataSet();
          $delimiter->decode($stream_reader, $this->transfer_syntax);

          while ($delimiter->group_number == 0xFFFE && $delimiter->element_number != 0xE0DD) {
            $sequence = array($delimiter);
            
            if ($delimiter->length == 0xFFFFFFFF) {
              $item_delimiter = new CDicomDataSet();
              $item_delimiter->decode($stream_reader, $this->transfer_syntax);
              $sequence[] = $item_delimiter;
              
              while ($item_delimiter->group_number == 0xFFFE && $item_delimiter->element_number != 0xE00D) {
                $dataset = new CDicomDataSet();
                $dataset->decode($value_stream, $this->transfer_syntax);
                $sequence[] = $dataset;
                
                $item_delimiter = new CDicomDataSet();
                $item_delimiter->decode($stream_reader, $this->transfer_syntax);
                $sequence[] = $item_delimiter;
              }
            }
            else {
              $sequence_end = $value_stream->getPos();// + $sequence_length;
            
              while ($value_stream->getPos() < $sequence_end) {
                $dataset = new CDicomDataSet();
                $dataset->decode($value_stream, $this->transfer_syntax);
                $sequence[] = $dataset;
              }
            }
            
            $delimiter = new CDicomDataSet();
            $delimiter->decode($stream_reader, $this->transfer_syntax);
            $sequence[] = $delimiter;
            $tmp_value[] = $sequence;
          }
        }
        /** Sequence of items with defined length **/
        else {
          $content = $stream_reader->read($this->length);

          fwrite($value_stream->stream, $content, $this->length);
          $value_stream->rewind();
          
          while ($value_stream->getPos() < $this->length) {
            $value_stream->skip(4);
            $sequence_length = $value_stream->readUInt32($endianness);
            $sequence = array();
            $sequence_end = $value_stream->getPos() + $sequence_length;
            
            while ($value_stream->getPos() < $sequence_end) {
              $dataset = new CDicomDataSet();
              $dataset->decode($value_stream, $this->transfer_syntax);
              $sequence[] = $dataset;
            }
            $tmp_value[] = $sequence;
          }
        }
        $this->value = $tmp_value;
        break;
      default :
        
        break;
    }
  }

  /**
   * Return a string representation of the class
   * 
   * @return string
   */
  function __toString() {
    $str = "<td>(" . sprintf("%04X", $this->group_number) . "," . sprintf("%04X", $this->element_number) . ")</td>
            <td>$this->name</td>
            <td>$this->vr</td>
            <td>$this->length</td>";
    if ($this->vr == "SQ") {
      $str .= "<td><ul>";
      if ($this->value) {
        foreach ($this->value as $_sequence) {
          $str .= "<li><table>";

          foreach ($_sequence as $_dataset) {
            $str .= "<tr>" . $_dataset->__toString() . "</tr>";
          }
          $str .= "</table></li>";
        }
      }
      $str .= "</td></ul>";
    }
    else {
      $str .= "<td>$this->value</td>";
    }
    return $str;
  }
}