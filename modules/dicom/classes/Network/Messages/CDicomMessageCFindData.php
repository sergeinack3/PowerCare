<?php
/**
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Dicom\Network\Messages;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Interop\Dicom\CDicomStreamReader;
use Ox\Interop\Dicom\CDicomStreamWriter;
use Ox\Interop\Dicom\Data\CDicomDataSet;

/**
 * The C-Find-Data message
 * 
 * @see DICOM Standard PS 3.07, section 9.1.2 ans 9.3.2
 */
class CDicomMessageCFindData implements IShortNameAutoloadable {
  
  /**
   * The list of the differents attributes
   * The keys are the group number, the values an array (key element number)
   * 
   * @var array
   */
  protected $attributes;
  
  /**
   * An array which contains the datasets
   * 
   * @var array
   */
  protected $datasets = array();
  
  /**
   * The encoded content of the message
   * 
   * @var string
   */
  protected $content;
  
  /**
   * The type of the message
   * 
   * @var string
   */
  public $type = "Datas";
  
  static $type_int = "data"; 
  
  /**
   * The constructor
   * 
   * @param array $attributes The attributes
   */
  function __construct(array $attributes = null) {
    if ($attributes) {
      $this->setAttributes($attributes);
    }
  }
  
  /**
   * Return the list of attributes
   * 
   * @return array
   */
  function getAttributes() {
    return $this->attributes;
  }
  
  /**
   * Set the list of attributes
   * 
   * @param array $attributes The list
   * 
   * @return null
   */
  function setAttributes(array $attributes) {
    $this->attributes = $attributes;
  }
  
  /**
   * Return the datasets
   * 
   * @return array
   */
  function getDatasets() {
    return $this->datasets;
  }
  
  /**
   * Return the encoded content
   * 
   * @return string
   */
  function getContent() {
    return $this->content;
  }
  
  /**
   * Set the encoded content
   * 
   * @param string $content The content
   * 
   * @return string
   */
  function setContent($content) {
    $this->content = $content;
  }
  
  /**
   * Return th total length
   * 
   * @return integer
   */
  function getTotalLength() {
    $length = 0;
    foreach ($this->datasets as $group) {
      $length += $group[0x0000]->getValue + $group[0x0000]->getTotalLength();
    }
    return $length;
  }
  
  /**
   * Encode the datas, depending on the transfer syntax
   * 
   * @param CDicomStreamWriter $stream_writer   The stream writer
   * 
   * @param string             $transfer_syntax The UID of the transfer syntax
   * 
   * @return null
   */
  function encode(CDicomStreamWriter $stream_writer, $transfer_syntax) {
    foreach ($this->attributes as $group_number => $group) {
      $this->datasets[$group_number] = array();
      
      $handle = fopen("php://temp", "w+");
      $group_writer = new CDicomStreamWriter($handle);
      
      foreach ($group as $element_number => $value) {
        $dataset = new CDicomDataSet(array("group_number" => $group_number, "element_number" => $element_number, "value" => $value));
        $dataset->encode($group_writer, $transfer_syntax);
        
        $this->datasets[$group_number][$element_number] = $dataset;
      }
      
      $length = strlen($group_writer->buf);
      $group_length_dataset = new CDicomDataSet(array("group_number" => $group_number, "element_number" => 0x0000, "value" => $length));
      
      $group_length_dataset->encode($stream_writer, $transfer_syntax);
      $this->datasets[$group_number][0x0000] = $group_length_dataset;
      $stream_writer->write($group_writer->buf);
    
      $group_writer->close();
    }
    $this->setContent($stream_writer->buf);
  }
  
  /**
   * Decode the datas, depending on the transfer syntax
   * 
   * @param CDicomStreamReader $stream_reader   The stream writer
   * 
   * @param string             $transfer_syntax The UID of the transfer syntax
   * 
   * @return void
   */
  function decode(CDicomStreamReader $stream_reader, $transfer_syntax) {
    if (!$transfer_syntax) {
      return;
    }
    $this->attributes = array();
    $this->datasets = array();
    $stream_length = $stream_reader->getStreamLength();
    while ($stream_reader->getPos() < $stream_length) {
      $dataset = new CDicomDataSet();
      $dataset->decode($stream_reader, $transfer_syntax);
      
      $group = $dataset->getGroupNumber();
      $element = $dataset->getElementNumber();
      if (!array_key_exists($group, $this->datasets)) {
        $this->datasets[$group] = array();
      }
      if (!array_key_exists($group, $this->attributes)) {
        $this->attributes[$group] = array();
      }
      
      $this->datasets[$group][$element] = $dataset;
      $this->attributes[$group][$element] = $dataset->getValue();
    }
  }

  /**
   * Return the list of the requested datas, when the message follows a C-Find-RQ
   *
   * @return array|void
   */
  function getRequestedDatas() {
    if (!$this->datasets) {
      return null;
    }
    $requested_datas = array();
    foreach ($this->datasets as $_group_number => $_group) {
      foreach ($_group as $_element_number => $element) {
        if ($_element_number == 0x0000) {
          continue;
        }

        if (!array_key_exists($_group_number, $_group)) {
          $requested_datas[$_group_number] = array();
        }
        $requested_datas[$_group_number][$_element_number] = $element;
      }
    }

    return $requested_datas;
  }

  /**
   * Return a string representation of the class
   * 
   * @return string
   */
  function __toString() {
    $str = "<table>
              <tr>
                <th>Tag</th><th>Name</th><th>VR</th><th>Length</th><th>Value</th>
              </tr>";
    foreach ($this->datasets as $group) {
      foreach ($group as $element) {
        $str .= "<tr>" . $element->__toString() . "</tr>";
      }
    }              

    $str .= "</table>";

    return $str;
  }
}
