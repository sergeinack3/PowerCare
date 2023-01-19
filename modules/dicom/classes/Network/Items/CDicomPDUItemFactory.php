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
 * The PDUItem Factory, who matches the type of item and the corresponding PHP class
 */
class CDicomPDUItemFactory implements IShortNameAutoloadable {
  
  /**
   * Used by the decodeItems function
   * 
   * @var string
   */
  static $next_item = null;
  
  /**
   * Get the type of the Item, and create the corresponding CDicomPDUItem
   * 
   * @param CDicomStreamReader $stream_reader The stream reader
   * 
   * @return CDicomPDUItem The PDU item
   */
  static function decodeItem(CDicomStreamReader $stream_reader) {
    $item_type = self::readItemType($stream_reader);
    $item_length = self::readItemLength($stream_reader);
    
    if ($item_type === false) {
      return null;
    }
    
    $item = new $item_type(array("length" => $item_length));
    $item->decodeItem($stream_reader);
    
    return $item;
  }
  
  /**
   * Decodes consecutive items of the given type
   * 
   * @param CDicomStreamReader $stream_reader The stream reader
   * @param integer            $wanted_type   The code of the wanted type
   * 
   * @return CDicomPDUItem[] The PDU items
   */
  static function decodeConsecutiveItemsByType(CDicomStreamReader $stream_reader, $wanted_type) {
    $items = array();
    $item_type = self::readItemType($stream_reader);
    
    $wanted_type = self::getItemClass($wanted_type);

    while ($item_type == $wanted_type) {
      $item_length = self::readItemLength($stream_reader);

      $item = new $item_type(array("length" => $item_length));
      $item->decodeItem($stream_reader);
      $items[] = $item;
      
      $item_type = self::readItemType($stream_reader);
    }
    self::$next_item = $item_type;
    
    return $items;
  }
  
  /**
   * Decodes consecutive items until the given length is reached
   * 
   * @param CDicomStreamReader $stream_reader The stream reader
   * 
   * @param integer            $length        The code of the wanted type
   * 
   * @return CDicomPDUItem[] The PDU items
   */
  static function decodeConsecutiveItemsByLength(CDicomStreamReader $stream_reader, $length) {
    $items = array();
    
    $pos = $stream_reader->getPos();
    $endOfItem = $pos + $length;

    $item_type = self::readItemType($stream_reader);
    
    while ($item_type && $stream_reader->getPos() < $endOfItem) {
      if (!$item_type) {
        break;
      }
      
      $item_length = self::readItemLength($stream_reader);
      
      $item = new $item_type(array("length" => $item_length));
      
      $item->decodeItem($stream_reader);
      $items[] = $item;
      
      if ($stream_reader->getPos() < $endOfItem) {
        $item_type = self::readItemType($stream_reader);
      }
    }
    
    return $items;
  }
  
  /**
   * Create an item of the given type 
   * 
   * @param CDicomStreamWriter $stream_writer The stream writer
   * 
   * @param string             $type          The type of the PDU you want to create
   * 
   * @return CDicomPDUItem The item
   */
  static function encodeItem(CDicomStreamWriter $stream_writer, $type) {
    return new CDicomPDUItem();
  }
  
  /**
   * Read the type of an item. If a item type has been read but not decoded, it returns this type.
   * 
   * @param CDicomStreamReader $stream_reader The stream reader
   * 
   * @return string The name of the item class
   */
  static function readItemType(CDicomStreamReader $stream_reader) {
    $item_type = null;
    if (!self::$next_item) {
      $tmp = $stream_reader->readUInt8();
      $stream_reader->skip(1);

      if (!$tmp) {
        return false;
      }
      $item_type = self::getItemClass($tmp);//$stream_reader->readHexByte()];
    }
    else {
      $item_type = self::$next_item;
      self::$next_item = null;
    }
    return $item_type;
  }

  /**
   * Read the length of the item from the stream
   * 
   * @param CDicomStreamReader $stream The stream reader
   * 
   * @return integer
   */
  static function readItemLength(CDicomStreamReader $stream) {
    return $stream->readUInt16();
  }
  
  /**
   * Return the name of the class, corresponding to the given type
   * 
   * @param string $type The type of the item
   * 
   * @return string The name of the corresponding class
   */
  static function getItemClass($type) {
    switch ($type) {
      case 0x10 :
        return "CDicomPDUItemApplicationContext";
      case 0x20 :
        return "CDicomPDUItemPresentationContext";
      case 0x21 :
        return "CDicomPDUItemPresentationContextReply";
      case 0x30 :
        return "CDicomPDUItemAbstractSyntax";
      case 0x40 :
        return "CDicomPDUItemTransferSyntax";
      case 0x50 :
        return "CDicomPDUItemUserInfo";
      case 0x51 :
        return "CDicomPDUItemMaximumLength";
      case 0x52 :
        return "CDicomPDUItemImplementationClassUID";
      case 0x53 :
        return "CDicomPDUItemAsynchronousOperations";
      case 0x54 :
        return "CDicomPDUItemRoleSelection";
      case 0x55 :
        return "CDicomPDUItemImplementationVersionName";
      case 0x56 :
        return "CDicomPDUItemSopClassExtendedNegociation";
      case 0x57 :
        return "CDicomPDUItemSopClassCommonExtendedNegociation";
      case 0x58 :
        return "CDicomPDUItemUserIdentityNegociationRQ";
      case 0x59 :
        return "CDicomPDUItemUserIdentityNegociationRP";
      default :
        return false;
    }
  }
}
