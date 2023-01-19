<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CValue;

/**
 * Audiogramme associé à une consultation
 */
class CExamAudio extends CMbObject {
  static $frequences = array("125Hz", "250Hz", "500Hz", "1kHz", "2kHz", "3kHz", "4kHz", "6kHz", "8kHz", "16kHz");
  static $pressions  = array(-400, -300, -200, -100, 0, 100, 200, 300);
  static $sides      = array("gauche", "droite");
  static $types      = array("aerien", "osseux", "conlat", "ipslat", "pasrep", "vocale", "tympan","aerien_pasrep","osseux_pasrep");

  // DB Table key
  public $examaudio_id;

  // DB References
  public $consultation_id;

  // DB fields
  public $remarques;

  public $gauche_aerien;
  public $gauche_osseux;
  public $gauche_conlat;
  public $gauche_ipslat;
  public $gauche_pasrep;
  public $gauche_aerien_pasrep;
  public $gauche_osseux_pasrep;
  public $gauche_vocale;
  public $gauche_tympan;

  public $droite_aerien;
  public $droite_osseux;
  public $droite_conlat;
  public $droite_ipslat;
  public $droite_pasrep;
  public $droite_aerien_pasrep;
  public $droite_osseux_pasrep;
  public $droite_vocale;
  public $droite_tympan;

  // Form fields
    public $_gauche_aerien        = [];
    public $_gauche_osseux        = [];
    public $_gauche_conlat        = [];
    public $_gauche_ipslat        = [];
    public $_gauche_pasrep        = [];
    public $_gauche_aerien_pasrep = [];
    public $_gauche_osseux_pasrep = [];
    public $_gauche_vocale        = [];
    public $_gauche_tympan        = [];

    public $_droite_aerien        = [];
    public $_droite_osseux        = [];
    public $_droite_conlat        = [];
    public $_droite_ipslat        = [];
    public $_droite_pasrep        = [];
    public $_droite_osseux_pasrep = [];
    public $_droite_aerien_pasrep = [];
    public $_droite_vocale        = [];
    public $_droite_tympan        = [];

  public $_moyenne_gauche_aerien;
  public $_moyenne_gauche_osseux;
  public $_moyenne_droite_aerien;
  public $_moyenne_droite_osseux;

  // Fwd References
  /** @var CConsultation */
  public $_ref_consult;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'examaudio';
    $spec->key   = 'examaudio_id';
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["consultation_id"] = "ref notNull class|CConsultation back|examaudio";
    $props["remarques"] = "text helped";
    foreach (self::$sides as $_side) {
      foreach (self::$types as $_type) {
        $props[$_side . "_" . $_type] = "str maxLength|64";
      }
    }
    return $props;
  }

  /**
   * Vérifie que les abscisses des points vocaux sont conformes
   *
   * @param array $vocal_points Points vocaux
   *
   * @return bool
   */
  function checkAbscisse($vocal_points) {
    $dBs = array();
    foreach ($vocal_points as $point) {
      $point = explode("-", $point);
      $dB = $point[0];
      if (array_search($dB, $dBs) !== false) {
        return false;
      }

      if ($dB) {
        $dBs[] = $dB;
      }
    }

    return true;
  }

  /**
   * @inheritdoc
   */
  function check() {
    $msg = CAppUI::tr('CExamAudio-msg-Two points have the same abscissa in the vocal audiogram of the ear')." ";
    if (!$this->checkAbscisse($this->_gauche_vocale)) {
      return $msg . CAppUI::tr('common-left');
    }

    if (!$this->checkAbscisse($this->_droite_vocale)) {
      return $msg . CAppUI::tr('common-right');
    }

    return parent::check();
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    // Initialisations
    foreach (self::$sides as $_side) {
      foreach (self::$types as $_type) {
        $field = $_side . "_" . $_type;
        $this->$field = CValue::first($this->$field, "|||||||||");

        // Ajout des fréquences pouvant manquer
        if ($_type !== "tympan" && substr_count($this->$field, "|") === 7) {
          $explode = explode("|", $this->$field);

          $temp_field = array_slice($explode, 0, 5); // Les 5 premières fréquences

          $temp_field[5] = "";          //  3 kHz
          $temp_field[6] = $explode[5]; //  4 kHz
          $temp_field[7] = "";          //  6 kHz
          $temp_field[8] = $explode[6]; //  8 kHz
          $temp_field[9] = $explode[7]; // 16 kHz

          $this->$field = implode("|", $temp_field);
        }

        $this->{"_$field"} = explode("|", $this->$field);
      }
    }

    foreach (self::$sides as $_side) {
      foreach (array("aerien", "osseux") as $_type) {
        $field = "_$_side" . "_" . $_type;
        $this->{"_moyenne_$_side" . "_" . $_type} = array_sum(
          array(
            $this->{$field}[2],
            $this->{$field}[3],
            $this->{$field}[4],
            $this->{$field}[6]
          )
        ) / 4;
      }
    }

    foreach ($this->_gauche_vocale as $key => $value) {
      $item =& $this->_gauche_vocale[$key];
      $item = $value ? explode("-", $value) : array("", "");
    }

    foreach ($this->_droite_vocale as $key => $value) {
      $item =& $this->_droite_vocale[$key];
      $item = $value ? explode("-", $value) : array("", "");
    }
  }

  /**
   * @inheritdoc
   */
  function updatePlainFields() {
    parent::updatePlainFields();

    // Tris
    $dBs_gauche = array();
    foreach ($this->_gauche_vocale as $key => $value) {
      $dBs_gauche[] = CMbArray::get($value, 0, "end sort");
      $this->_gauche_vocale[$key] = CMbArray::get($value, 0) . "-" . CMbArray::get($value, 1);
    }

    array_multisort($dBs_gauche, SORT_ASC, $this->_gauche_vocale);

    $dBs_droite = array();
    foreach ($this->_droite_vocale as $key => $value) {
      $dBs_droite[] = CMbArray::get($value, 0, "end sort");
      $this->_droite_vocale[$key] = CMbArray::get($value, 0) . "-" . CMbArray::get($value, 1);
    }

    array_multisort($dBs_droite, SORT_ASC, $this->_droite_vocale);

    // Implodes
    foreach (self::$sides as $_side) {
      foreach (self::$types as $_type) {
        $field = $_side . "_" . $_type;
        $this->$field = $this->{"_$field"} ? implode("|", $this->{"_$field"}) : null;
      }
    }
  }

  /**
   * Charge la consultation hôte
   *
   * @return CConsultation
   */
  function loadRefConsult() {
    return $this->_ref_consult = $this->loadFwdRef("consultation_id", true);
  }

  /**
   * @inheritdoc
   */
  function getPerm($permType) {
    return $this->loadRefConsult()->getPerm($permType);
  }
  
  function getBilan() {
    $bilan = array();
    
    foreach ($this->_gauche_osseux as $index => $perte) {
      $bilan[CExamAudio::$frequences[$index]]["osseux"]["gauche"] = $perte;
    }
    foreach ($this->_gauche_aerien as $index => $perte) {
      $bilan[CExamAudio::$frequences[$index]]["aerien"]["gauche"] = $perte;
    }
    foreach ($this->_droite_osseux as $index => $perte) {
      $bilan[CExamAudio::$frequences[$index]]["osseux"]["droite"] = $perte;
    }
    foreach ($this->_droite_aerien as $index => $perte) {
      $bilan[CExamAudio::$frequences[$index]]["aerien"]["droite"] = $perte;
    }

    foreach ($bilan as $frequence => $value) {
      $pertes =& $bilan[$frequence];
      foreach ($pertes as $keyConduction => $valConduction) {
        $conduction =& $pertes[$keyConduction];
        $conduction["delta"] = intval($conduction["droite"])- intval($conduction["gauche"]);
      }
    }
    
    return $bilan;
  }
}
