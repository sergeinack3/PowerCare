<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Ox\Core\CModelObject;

/**
 * An object for making LDAP search request for the LDAP directory for Jeebox Client, or handle the results of the search
 */
class CJeeboxLDAPRecipient extends CModelObject {
  
  /** @var string The type of adress */
  public $address_type;

  /** @var string The first name */
  public $first_name;

  /** @var string The last name of the owner */
  public $last_name;

  /** @var string The mail adress */
  public $mail;

  /** @var string The national id of the owner (ADELI or RPPS) */
  public $national_id;

  /** @var string The type of national id (0 for ADELI, 8 for RPPS) */
  public $type_national_id;

  /** @var string The profession of the owner */
  public $profession;

  /** @var string The city  */
  public $city;

  /** @var string The structure id of the organization */
  public $structure_id;

  /** @var string The type of structure id (SIRET, SIREN or FINESS) */
  public $type_structure_id;

  /** @var string The organization name */
  public $organization;

  /**
   * @var array The list of the professions, with the id as key
   */
  public static $professions = array(
    10 => 'Médecin',
    21 => 'Pharmacien',
    26 => 'Audioprothésiste',
    28 => 'Opticien-Lunetier',
    40 => 'Chirurgien-Dentiste',
    50 => 'Sage-Femme',
    60 => 'Infirmier',
    69 => 'Infirmier psychiatrique',
    70 => 'Masseur-Kinésithérapeute',
    80 => 'Pédicure-Podologue',
    81 => 'Orthoprothésiste',
    82 => 'Podo-Orthésiste',
    83 => 'Orthopédiste-Orthésiste',
    84 => 'Oculariste',
    85 => 'Epithésiste',
    86 => 'Technicien de labo médical',
    91 => 'Orthophoniste',
    92 => 'Orthoptiste',
    94 => 'Ergothérapeute',
    95 => 'Diététicien',
    96 => 'Psychomotricien',
    98 => 'Manipulateur ERM',
    200 => 'Assistant de service social',
    201 => 'Auxiliaire de vie sociale',
    202 => 'Technicien de l\'intervention sociale et familiale',
    203 => 'Conseiller en économie sociale et familiale',
    204 => 'Médiateur familial',
    205 => 'Assistant familial',
    206 => 'Aide médico psychologique',
    207 => 'Moniteur éducateur',
    208 => 'Educateur de jeunes enfants',
    209 => 'Educateur spécialisé',
    210 => 'Educateur technique spécialisé',
    '*' => 'Personnel d\'établissement',
  );

  /** @var array The list of attrbutes per adress type */
  public static $attributes = array(
    'PER' => array(
      'first_name',
      'last_name',
      'mail',
      'national_id',
      'type_national_id',
      'profession',
      'city'
    ),
    'ORG' => array(
      'organization',
      'mail',
      'structure_id',
      'type_structure_id',
      'city',
      'description'
    ),
    'APP' => array(
      'organization',
      'mail',
      'structure_id',
      'type_structure_id',
      'city',
      'description'
    )
  );

  /** @var array The mapping between the class's fields and the LDAP's attributes */
  public static $mapping = array(
    'address_type'      => 'TypeBAL',
    'first_name'        => 'givenName',
    'last_name'         => 'sn',
    'mail'              => 'mail',
    'national_id'       => 'IdentifiantPP',
    'type_national_id'  => 'TypeIdentifiantPP',
    'profession'        => 'profession',
    'city'              => 'l',
    'structure_id'      => 'IdentifiantPM',
    'type_structure_id' => 'TypeIdentifiantPM',
    'organization'      => 'o',
    'description'       => 'description',
  );

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  public function getProps() {
    $props = parent::getProps();

    $props['address_type']      = 'enum list|PER|ORG|APP default|PER notNull';
    $props['first_name']        = 'str';
    $props['last_name']         = 'str';
    $props['mail']              = 'str';
    $props['national_id']       = 'str';
    $props['type_national_id']  = 'enum list|0|8';
    $props['profession']        = 'str';
    $props['city']              = 'str';
    $props['structure_id']      = 'str';
    $props['type_structure_id'] = 'enum list|FINESS|SIRET|SIREN';
    $props['organization']      = 'str';

    return $props;
  }

  /**
   * CLDAPSearchRecipientQuery constructor.
   *
   * @param array $data The data for the creation of the query
   * @param bool  $ldap If true, handle the data from the ldap entries
   */
  public function __construct($data = array(), $ldap = false) {
    foreach ($data as $_field => $_value) {
      if ($ldap && array_search($_field, self::$mapping)) {
        $_field = self::$mapping[array_search($_field, self::$mapping)];
        if (array_key_exists(0, $_value)) {
          $_value = $_value[0];
        }

        $_value = utf8_decode($_value);
      }

      if (property_exists($this, $_field)) {
        $this->$_field = $_value;
      }
    }

    parent::__construct();
  }

  /**
   * Format the query for the LDAP search
   *
   * @return string
   */
  public function makeQuery() {
    $query = '(&';
    
    $allowed_fields = self::$attributes[$this->address_type];

    foreach (get_object_vars($this) as $_field => $_value) {
      if (!empty($_value) && in_array($_field, $allowed_fields)) {
        $query .= '(' . self::$mapping[$_field] . "=$_value)";
      }
    }

    return utf8_encode($query . ')');
  }

  /**
   * Return an array containing the required LDAP attributes for the LDAP search
   * 
   * @return array
   */
  public function getAttributes() {
    $attributes = array();

    foreach (self::$attributes[$this->address_type] as $_attribute) {
      $attributes[] = self::$mapping[$_attribute];
    }
    
    return $attributes;
  }
}
