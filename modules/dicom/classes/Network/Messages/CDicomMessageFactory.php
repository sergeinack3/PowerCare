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
use Ox\Interop\Dicom\Data\CDicomDataSet;

/**
 * The messages factory
 */
class CDicomMessageFactory implements IShortNameAutoloadable {
  
  /**
   * Decode a message, and return the corresponding object
   * 
   * @param CDicomStreamReader $stream_reader   The stream reader
   * 
   * @param integer            $control_header  The control header
   * 
   * @param string             $transfer_syntax The transfer syntax
   * 
   * @return object
   */
  static function decodeMessage(CDicomStreamReader $stream_reader, $control_header, $transfer_syntax) {
    if ($control_header == 0 || $control_header == 2) {
      $message_class = self::getMessageClass("data");
    }
    else {
      $datasets = array();
      $dataset = new CDicomDataSet();
      $dataset->decode($stream_reader, $transfer_syntax);
      $datasets[$dataset->getElementNumber()] = $dataset;
      
      $dataset = new CDicomDataSet();
      $dataset->decode($stream_reader, $transfer_syntax);
      $datasets[$dataset->getElementNumber()] = $dataset;
      
      $dataset = new CDicomDataSet();
      $dataset->decode($stream_reader, $transfer_syntax);
      $datasets[$dataset->getElementNumber()] = $dataset;
      $stream_reader->rewind();

      if (!array_key_exists(0x0100, $datasets)) {
        return null;
      }
      $message_class = self::getMessageClass($datasets[0x0100]->getValue());
    }
    $message = new $message_class();
    $message->decode($stream_reader, $transfer_syntax);

    return $message;
  }
  
  /**
   * Return the message class
   * 
   * @param mixed $message_type The message type
   * 
   * @return string
   */
  static function getMessageClass($message_type) {
    $class = "";
    switch ($message_type) {
      case "data" :
        $class = "CDicomMessageCFindData";
        break;
      case 0x0030 :
        $class = "CDicomMessageCEchoRQ";
        break;
      case 0x8030 :
        $class = "CDicomMessageCEchoRSP";
        break;
      case 0x0020 :
        $class = "CDicomMessageCFindRQ";
        break;
      case 0x8020 :
        $class = "CDicomMessageCFindRSP";
        break;
      case 0x0FFF :
        $class = "CDicomMessageCCancelFindRQ";
        break;
      default :
        break;
    }
    return $class;
  }
}