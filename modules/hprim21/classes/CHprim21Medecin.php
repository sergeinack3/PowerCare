<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21;

use Ox\Mediboard\Mediusers\CMediusers;

/**
 * The HPRIM 2.1 medecin class
 */
class CHprim21Medecin extends CHprim21Object {
  // DB Table key
  public $hprim21_medecin_id;
  
  // DB references
  public $user_id;

  // DB Fields
  public $nom;
  public $prenom;
  public $prenom2;
  public $alias;
  public $civilite;
  public $diplome;
  public $type_code;
  
  public $_ref_user;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'hprim21_medecin';
    $spec->key   = 'hprim21_medecin_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specs = parent::getProps();

    $specs["user_id"]             = "ref class|CMediusers back|hprim21_medecins";
    $specs["nom"]                 = "str";
    $specs["prenom"]              = "str";
    $specs["prenom2"]             = "str";
    $specs["alias"]               = "str";
    $specs["civilite"]            = "str";
    $specs["diplome"]             = "str";
    $specs["type_code"]           = "str";
    $specs["echange_hprim21_id"] .= " back|medecins_hprim21";

    return $specs;
  }

  function bindToLine($line, CHPrim21Reader &$reader, CHprim21Object $object = null, CHprim21Medecin $medecin = null) {
    $this->setHprim21ReaderVars($reader);
    
    $elements = explode($reader->separateur_champ, $line);
  
    if (count($elements) < 1) {
      $reader->error_log[] = "Champs manquant dans le segment patient (médecin)";
      return false;
    }
    
    $identite = explode($reader->separateur_sous_champ, $elements[13]);
    if (!$identite[0]) {
      return false;
    }
    $this->external_id = $identite[0];
    $this->loadMatchingObject();
    $this->nom         = $identite[1];
    $this->prenom      = $identite[2];
    $this->prenom2     = $identite[3];
    $this->alias       = $identite[4];
    $this->civilite    = isset($identite[5]) ? $identite[5] : null;
    
    return true;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    $this->_view = $this->nom;
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd(){
    // Chargement du séjour correspondant
    $this->_ref_user = new CMediusers();
    $this->_ref_user->load($this->user_id);
  }
}
