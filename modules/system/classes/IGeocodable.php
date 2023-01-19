<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

/**
 * Description
 */
interface IGeocodable {

  /**
   * Get a list of fields used to geocode entity
   *
   * @return array
   */
  function getGeocodeFields();

  /**
   * Loads geolocalisation reference (lat ; lng ; INSEE)
   *
   * @return CGeoLocalisation
   */
  function loadRefGeolocalisation();

  /**
   * Create and store a geolocalisation object
   *
   * @return CGeoLocalisation
   */
  function createGeolocalisationObject();

  function getAddress();
  function getZipCode();
  function getCity();
  function getCountry();

  function getFullAddress();

  function getLatLng();
  function setLatLng($latlng);
  function getCommuneInsee();
  function setCommuneInsee($communeInsee);
  function resetProcessed();
  function setProcessed(CGeoLocalisation $object = null);
  static function isGeocodable();
}