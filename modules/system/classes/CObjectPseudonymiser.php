<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use ArrayIterator;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\Chronometer;
use Ox\Core\CPerson;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\OpenData\CHDEtablissement;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CPatient;
use DateInterval;
use DateTime;
use Exception;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbSecurity;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Class CObjectPseudonymiser
 */
class CObjectPseudonymiser implements IShortNameAutoloadable {
  static $classes_handled = array(
    'CPatient'         => array(
      'matricule', /*'civilite',*/
      'adresse', 'province', /*'ville', 'cp',*/ 'tel', 'tel2', 'tel_autre', 'tel_autre_mobile', 'tel_pro',
      'email', 'situation_famille', 'avs', 'rques', 'lieu_naissance', 'cp_naissance', 'patient_link_id', "assure_nom", "assure_prenom",
      "assure_prenoms", "assure_nom_jeune_fille", "assure_sexe", "assure_civilite",
      "assure_naissance", "assure_naissance_amo", "assure_adresse", "assure_ville", "assure_cp", "assure_tel", "assure_tel2",
      "assure_pays", "assure_pays_insee", "assure_lieu_naissance", "assure_cp_naissance", "assure_pays_naissance_insee",
      "assure_profession", "assure_rques", "assure_matricule", 'assure_rang_naissance', 'medecin_traitant',
    ),
    'CUser'            => array(
      'user_email', 'user_phone', 'user_mobile', 'user_address1', 'user_city', 'user_zip',
    ),
    'CGroups'          => array(
      'raison_sociale', 'adresse', 'cp', 'ville', 'tel', 'fax', 'mail', 'mail_apicrypt', 'web', 'directeur', 'domiciliation',
      'siret', 'ape', 'tel_anesth', /*'service_urgences_id', 'pharmacie_id',*/ 'finess', 'ean', 'rcc', 'legal_entity_id', /*'code',*/
      'short_name', 'description', 'user_id', 'opening_reason', 'opening_date', 'closing_reason', 'closing_date', 'activation_date',
      'inactivation_date',
    ),
    'CFunctions' => array(
      'initials', 'soustitre', 'adresse', 'tel', 'fax', 'email', 'finess', 'siret', 'ean', 'rcc',
    ),
    'CMedecin' => array(
      'titre', 'adresse', 'tel', 'tel_autre', 'fax', 'portable', 'email', 'adeli', 'rpps', 'email_apicrypt', 'mssante_address'
    ),
    'CCorrespondantPatient' => array(
      'relation_autre', 'surnom', 'adresse', 'tel', 'tel_autre', 'mob', 'fax', 'urssaf', 'parente_autre', 'email', 'remarques', 'ean',
      'ean_base', 'num_assure', 'employeur'
    ),
    'CEtabExterne' => array('raison_sociale', 'adresse', 'tel', 'fax', 'finess', 'siret', 'ape'),
    'CLegalEntity' => array('finess', 'rmess', 'address', 'insee', 'siren', 'nic'),
    //'CHDEtablissement' => array(), // No fields to empty, only change etab name
  );

  static $counts = array(
    '50', '100', '500', '1000',
  );

  protected $class;
  protected $objects = array();
  protected $is_person = false;
  protected $count_pseudo = 0;

  /**
   * CObjectPseudonymiser constructor.
   *
   * @param string $class Class to pseudonymise
   */
  function __construct($class) {
    if (!array_key_exists($class, self::$classes_handled)) {
      CAppUI::commonError('CObjectPseudonymiser-error-no class');
    }

    $this->class = $class;
  }

  /**
   * Pseudomyse some objects of $class
   *
   * @param int  $count        Number of elements to pseudonymise
   * @param int  $last_id      Last id pseudonymised
   * @param bool $pseudo_admin Pseudonymise administrators
   *
   * @return int
   * @throws Exception
   */
  function pseudonymiseSome($count = 100, $last_id = null, $pseudo_admin = true) {
    $cache = new Cache('pseudonymise', $this->class, Cache::OUTER | Cache::DISTR);
    if ($last_id === "" || $last_id === null) {
      $last_id = $cache->get() ?: 0;
    }

    $this->getObjectsToPseudonymise($last_id, $count, $pseudo_admin);
    $this->pseudonymiseObjects();

    return (count($this->objects) > 0) ? $cache->put(($last_id + $count)) : null;
  }

  /**
   * Get the objects to pseudonymise and put them into $this->objects
   *
   * @param int $last_id             Last_id used
   * @param int $count               Number of objects to get
   * @param bool $pseudonymise_admin Pseudonymiser les utilisateurs administrateurs
   *
   * @return void
   */
  protected function getObjectsToPseudonymise($last_id = 0, $count = 100, $pseudonymise_admin = true) {
    /** @var CMbObject $object */
    $object = new $this->class();
    $key    = $object->_spec->key;

    $this->is_person = ($object instanceof CPerson);

    $ds = $object->getDS();

    $where = array();
    if ($this->class == 'CUser') {
      // Do not pseudonymise current user or admin
      $where['user_id'] = $ds->prepareNotIn(array(1, CUser::get()->_id));
      // Do not pseudonymise Profils
      $where['template'] = " = '0'";

      if (!$pseudonymise_admin) {
        $where['user_type'] = "!= 1";
      }
    }

    $limit = "$last_id,$count";

    $this->objects = $object->loadList($where, "$key ASC", $limit);
  }

  /**
   * @param bool $use_table Use the FirstName to gender table or not
   *
   * @return array
   * @throws Exception
   */
  protected function getRandomNames($use_table) {
    $count = count($this->objects);

    if ($use_table) {
      $firstname_association = new CFirstNameAssociativeSex();
      $max                   = $firstname_association->countList();

      $key         = $firstname_association->_spec->key;
      $first       = rand(1, $max - $count);
      $first_names = $firstname_association->loadList(array($key => "> $first"), "$key ASC", $count);

      return CMbArray::pluck($first_names, 'firstname');
    }
    else {
      $first_names = array();
      for ($i = 0; $i < $count; $i++) {
        $first_names[] = CMbSecurity::getRandomString(12);
      }

      return $first_names;
    }
  }

  /**
   * Pseudonymise the objects
   *
   * @return void
   * @throws Exception
   */
  protected function pseudonymiseObjects() {
    $lastnames_iterator = null;
    $njfs_iterator      = null;
    $name_field         = null;

    if ($this->is_person) {
      $firstname_association = new CFirstNameAssociativeSex();
      $ds                    = $firstname_association->getDS();
      $table_exists          = $ds->hasTable($firstname_association->_spec->table);

      $njfs      = $this->getRandomNames($table_exists);
      $lastnames = $this->getRandomNames($table_exists);

      $lastnames_iterator = new ArrayIterator($lastnames);
      $njfs_iterator      = new ArrayIterator($njfs);


      switch ($this->class) {
        case "CPatient":
        case "CMedecin":
        case "CCorrespondantPatient":
          $name_field = 'nom';
          break;
        case "CUser":
          $name_field = 'user_last_name';
          break;
        default:
          $name_field         = null;
      }
    }

    /** @var CPatient|CUser|CHDEtablissement|CGroups|CFunctions $_object */
    foreach ($this->objects as $_object) {
      $this->count_pseudo++;
      // Disable logging
      $_object->_spec->loggable = false;

      if ($name_field) {
        $_object->$name_field = $lastnames_iterator->current();
        $lastnames_iterator->next();
      }

      foreach (self::$classes_handled[$this->class] as $_field) {
        $_object->$_field = '';
      }

      switch ($_object->_class) {
        case 'CPatient':
        case 'CCorrespondantPatient':
          if ($_object->nom_jeune_fille) {
            $_object->nom_jeune_fille = $njfs_iterator->current();
            $njfs_iterator->next();
          }

          $_object = $this->modifyDate($_object, 'naissance');
          break;

        case 'CUser':
          $_object->user_username = CMbSecurity::getRandomString(24);

          $_object->user_salt     = null;
          $_object->user_password = null;

          $mediuser        = $_object->loadRefMediuser();
          if ($mediuser && $mediuser->_id) {
            $mediuser->_spec->loggable = false;

            $mediuser->adeli = '';
            $mediuser->rpps  = '';
            $mediuser->inami = '';
            $mediuser->cps   = '';
            $mediuser->ean   = '';

            $_object = $mediuser;
          }

          break;

        case 'CHDEtablissement':
          $_object->raison_sociale = "Etablissement " . $_object->_id;
          break;

        case 'CGroups':
          $_object->_name = "MB-Etab $_object->_id";
          $_object->code = "MB-$_object->_id";
          break;

        case 'CFunctions':
          $_object->text = "Fonction $_object->_id";
          break;

        case 'CMedecin':
          if ($_object->jeunefille) {
            $_object->jeunefille = $njfs_iterator->current();
            $njfs_iterator->next();
          }
          break;

        case 'CEtabExterne':
          $_object->nom = "Ext-Etab $_object->_id";
          break;

        case 'CLegalEntity':
          $_object->_name = "LE-Ent $_object->_id";
          $_object->code = "LE-$_object->_id";
          break;

        default:
          // Do nothing
      }

      if (($repair = $_object->repair()) && $this->class == "CPatient" && isset($repair['naissance'])) {
        $_object->naissance = "1850-01-01";
      }

      if ($msg = $_object->store()) {
        CAppUI::setMsg($msg, UI_MSG_WARNING);
      }
      else {
        CAppUI::setMsg("CObjectPseudonymiser-msg-ok", UI_MSG_OK, CAppUI::tr($_object->_class));
      }
    }
  }

  /**
   * Modify a date object
   *
   * @param CMbObject $object Object to modify a date for
   * @param string    $field  Name of the field to modify
   *
   * @return CMbObject
   */
  protected function modifyDate($object, $field) {
    if (!$object->{$field} || CMbDT::isLunarDate($object->{$field})) {
      return $object;
    }

    $old_value = $object->{$field};

    $naissance_modifier = rand(1, 5);
    $signe              = (rand(0, 1) > 0) ? '+' : '-';

    try {
      // Cannot use CMbDT because most of birthdate will be before 1970-01-01
      $date     = new DateTime($object->{$field});
      $interval = "P{$naissance_modifier}D";
      ($signe > 0) ? $date->add(new DateInterval($interval)) : $date->sub(new DateInterval($interval));
    } catch (Exception $e) {
      CAppUI::setMsg($e->getMessage(), UI_MSG_WARNING);
      return $object;
    }

    $object->{$field} = $date->format('Y-m-d');

    if ($object->checkProperty($field)) {
      $object->{$field} = $old_value;
    }

    return $object;
  }

  /**
   * @return int
   */
  function getCount() {
    return $this->count_pseudo;
  }
}
