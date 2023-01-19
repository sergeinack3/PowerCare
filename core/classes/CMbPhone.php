<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Ox\Mediboard\Notifications\CControlNotification;

class CMbPhone {

  /**
   * Transform a phone number to an internation format
   *
   * @param string $phonenumber the phone number
   *
   * @return string $phonenumber the phone internationalized
   */
  static function phoneToInternational($phonenumber) {

    //french default format
    if (self::checkMobileNumber($phonenumber)) {
      return preg_replace("/0/", "33", $phonenumber, 1);
    }
    elseif (strpos($phonenumber, '+') === 0) {
      return str_replace('+', '', $phonenumber);
    }

    return $phonenumber;
  }

  /**
   * Check for mobile number (06..., 07...)
   *
   * @param string $number  the number
   * @param string $country the country to check
   *
   * @return bool
   */
  static function checkMobileNumber($number, $country ="fr") {
    $phones = array(
      "fr"  => "/(0|\+33\s?)(6|7)(\s?\d{2}){4}/"
    );

    if (!array_key_exists($country, $phones)) {
      return true;
    }

    switch ($country) {
      case "fr":
        if (preg_match($phones[$country], $number)) {
          return true;
        }
        break;
      default:
        return true;
    }

    return false;
  }

  /**
   * Get the phone from guid, ( and check number ?)
   *
   * @param string $guid        object guid
   * @param bool   $checkmobile check for a valid mobile number
   *
   * @return bool|string number found or false if not found or invalid
   */
  static function getPhoneFromGuid($guid, $checkmobile = false) {
    $object = CMbObject::loadFromGuid($guid);
    if (!$object || !$object->_id) {
      return false;
    }
    $object->updateFormFields();

    if ($object instanceof CPerson) {
      $country = CAppUI::conf('ref_pays') == 1 ? 'fr' : 'other';
      if ($object->_p_country) {
        $country = substr(strtolower($object->_p_country), 0, 2);
      }

      //mobile
      if ($mobile_phone_number = $object->_p_mobile_phone_number) {
        if ($object->_p_phone_area_code) {
          $mobile_phone_number = "+$object->_p_phone_area_code" . substr($mobile_phone_number, 1);
        }

        if ($checkmobile && !self::checkMobileNumber($mobile_phone_number, $country)) {
          return false;
        }
        return $mobile_phone_number;
      }

      //fixe
      if ($phone_number = $object->_p_phone_number) {
        if ($checkmobile && !self::checkMobileNumber($phone_number, $country)) {
          return false;
        }
        return $phone_number;
      }

      if ($mobile_phone_number = $object->_p_international_mobile_phone) {
        return $mobile_phone_number;
      }

      if ($phone_number = $object->_p_international_phone) {
        return $phone_number;
      }
    }
    elseif ($object instanceof CControlNotification) {
      return $object->phone_number;
    }

    return false;
  }


  /**
   * Get the mobine phone of an user object
   *
   * @param string $guid MB guid
   *
   * @return string
   */
  static function getMobilePhoneFromGuid($guid) {
    $object = CMbObject::loadFromGuid($guid);
    $object->updateFormFields();

    if ($object instanceof CPerson) {
      return $object->_p_mobile_phone_number;
    }

    return null;
  }
}