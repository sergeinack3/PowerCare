<?php
/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\OpenData;

use Ox\Core\Cache;
use Ox\Core\CMbObject;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\System\CGeoLocalisation;
use Ox\Mediboard\System\IGeocodable;

/**
 * Description
 */
class CHDEtablissement extends CMbObject implements IGeocodable {
  const GET_ETAB_RS_FOR_ETAB_ID_CACHE = 'CHDEtablissement.getEtabRSForEtabID';

  /** @var integer Primary key */
  public $hd_etablissement_id;

  public $finess;
  public $raison_sociale;
  public $champ_pmsi;
  public $taa;
  public $cat;
  public $taille_mco;
  public $taille_m;
  public $taille_c;
  public $taille_o;
  public $adresse;
  public $cp;
  public $ville;

  /** @var CGeoLocalisation */
  public $_ref_geolocalisation;

  static public $fields = array(
    'finess'     => 'finess',
    'rs'         => 'raison_sociale',
    'champ_pmsi' => 'champ_pmsi',
    'taa'        => 'taa',
    'cat'        => 'cat',
    'taille_MCO' => 'taille_mco',
    'taille_M'   => 'taille_m',
    'taille_C'   => 'taille_c',
    'taille_O'   => 'taille_o',
  );

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec           = parent::getSpec();
    $spec->dsn      = 'hospi_diag';
    $spec->table    = "hd_etablissement";
    $spec->key      = "hd_etablissement_id";
    $spec->loggable = false;

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props['finess']         = 'str length|9 notNull';
    $props['raison_sociale'] = 'str notNull';
    $props['champ_pmsi']     = 'str';
    $props['taa']            = 'str';
    $props['cat']            = 'str';
    $props['taille_mco']     = 'str';
    $props['taille_m']       = 'str';
    $props['taille_c']       = 'str';
    $props['taille_o']       = 'str';
    $props["adresse"]        = "text";
    $props["cp"]             = "str minLength|4 maxLength|10";
    $props["ville"]          = "str maxLength|50";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = "[{$this->finess}] {$this->raison_sociale}";
  }

  static function getEtabRSForEtabID($etab_id) {
    $cache = new Cache(self::GET_ETAB_RS_FOR_ETAB_ID_CACHE, $etab_id, Cache::INNER);
    if ($cache->exists()) {
      return $cache->get();
    }

    $ds = CSQLDataSource::get('hospi_diag');

    $request = new CRequest();
    $request->addSelect('raison_sociale');
    $request->addTable('hd_etablissement');
    $request->addWhere(array('hd_etablissement_id' => $ds->prepare('= ?', $etab_id)));

    return $cache->put($ds->loadResult($request->makeSelect()));
  }

  /**
   * @inheritdoc
   */
  function getGeocodeFields() {
    return array(
      'adresse', 'cp', 'ville',
    );
  }

  function getAddress() {
    return $this->adresse;
  }

  function getZipCode() {
    return $this->cp;
  }

  function getCity() {
    return $this->ville;
  }

  function getCountry() {
    return null;
  }

  function getFullAddress() {
    return $this->getAddress() . ' ' . $this->getZipCode() . ' ' . $this->getCity() . ' ' . $this->getCountry();
  }

  /**
   * @inheritdoc
   */
  function loadRefGeolocalisation() {
    return $this->_ref_geolocalisation = $this->loadUniqueBackRef('geolocalisation');
  }

  /**
   * @inheritdoc
   */
  function createGeolocalisationObject() {

    $this->loadRefGeolocalisation();

    if (!$this->_ref_geolocalisation || !$this->_ref_geolocalisation->_id) {
      $geo = new CGeoLocalisation();
      $geo->setObject($this);
      $geo->processed = '0';
      $geo->store();

      return $geo;
    }
    else{
      return $this->_ref_geolocalisation;

    }

  }

  /**
   * @inheritdoc
   */
  function getLatLng() {
    $this->loadRefGeolocalisation();

    if (!$this->_ref_geolocalisation || !$this->_ref_geolocalisation->_id) {
      return null;
    }

    return $this->_ref_geolocalisation->lat_lng;
  }

  /**
   * @inheritdoc
   */
  function setLatLng($latlng) {
    $this->loadRefGeolocalisation();

    if (!$this->_ref_geolocalisation || !$this->_ref_geolocalisation->_id) {
      return null;
    }

    $this->_ref_geolocalisation->lat_lng = $latlng;

    return $this->_ref_geolocalisation->store();
  }

  /**
   * @inheritdoc
   */
  static function isGeocodable() {
    return true;
  }

  /**
   * @inheritdoc
   */
  function getCommuneInsee() {
    $this->loadRefGeolocalisation();

    if (!$this->_ref_geolocalisation || !$this->_ref_geolocalisation->_id) {
      return null;
    }

    return $this->_ref_geolocalisation->commune_insee;
  }

  /**
   * @inheritdoc
   */
  function setCommuneInsee($commune_insee) {
    $this->loadRefGeolocalisation();

    if (!$this->_ref_geolocalisation || !$this->_ref_geolocalisation->_id) {
      return null;
    }

    $this->_ref_geolocalisation->commune_insee = $commune_insee;

    return $this->_ref_geolocalisation->store();
  }

  /**
   * @inheritdoc
   */
  function resetProcessed(){
    $this->loadRefGeolocalisation();

    if (!$this->_ref_geolocalisation || !$this->_ref_geolocalisation->_id) {
      return null;
    }

    $this->_ref_geolocalisation->processed = "0";

    return $this->_ref_geolocalisation->store();

  }

  function setProcessed(CGeoLocalisation $object = null){
    if (!$object || !$object->_id) {
      $object = $this->loadRefGeolocalisation();
    }

    if (!$object || !$object->_id) {
      return null;
    }

    $object->processed = "1";

    return $object->store();
  }
}
