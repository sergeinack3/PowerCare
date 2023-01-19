<?php
/**
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Dicom\Network\Items;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Interop\Dicom\CDicomStreamReader;
use Ox\Interop\Dicom\CDicomStreamWriter;

/**
 * Represents a Dicom PDU Item
 */
class CDicomPDUItem implements IShortNameAutoloadable {
  
  /**
   * The type of the Item
   * 
   * @var integer
   */
  public $type;
  
  /**
   * The length of the Item
   * 
   * @var integer
   */
  public $length;
  
  /**
   * Set the type
   * 
   * @param string $type The type
   *  
   * @return void
   */
  function setType($type) {
    $this->type = $type;
  }
  
  /**
   * Set the length
   * 
   * @param integer $length The length
   *  
   * @return void
   */
  function setLength($length) {
    $this->length = $length;
  }
  
  /**
   * Decode the item
   * 
   * @param CDicomStreamReader $stream_reader The stream reader
   * 
   * @return void
   */
  function decodeItem(CDicomStreamReader $stream_reader) {
    
  }
  
  /**
   * Encode the item
   * 
   * @param CDicomStreamWriter $stream_writer The stream writer
   * 
   * @return void
   */
  function encodeItem(CDicomStreamWriter $stream_writer) {
    
  }
}