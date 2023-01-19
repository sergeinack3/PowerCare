<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * The HPRIM 2.1 sejour class
 */
class CHprim21Sejour extends CHprim21Object {
  // DB Table key
  public $hprim21_sejour_id;
  
  // DB references
  public $hprim21_patient_id;
  public $hprim21_medecin_id;
  public $sejour_id;
  
  // DB Fields
  public $date_mouvement;
  public $statut_admission;
  public $localisation_lit;
  public $localisation_chambre;
  public $localisation_service;
  public $localisation4;
  public $localisation5;
  public $localisation6;
  public $localisation7;
  public $localisation8;
  
  public $_ref_sejour;
  public $_ref_hprim21_medecin;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'hprim21_sejour';
    $spec->key   = 'hprim21_sejour_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specs = parent::getProps();

    $specs["hprim21_patient_id"]   = "ref notNull class|CHprim21Patient back|hprim21_sejours";
    $specs["hprim21_medecin_id"]   = "ref class|CHprim21Medecin back|hprim21_sejours";
    $specs["sejour_id"]            = "ref class|CSejour back|hprim21_sejours";
    $specs["date_mouvement"]       = "dateTime";
    $specs["statut_admission"]     = "enum list|OP|IP|IO|ER|MP|PA";
    $specs["localisation_lit"]     = "str";
    $specs["localisation_chambre"] = "str";
    $specs["localisation_service"] = "str";
    $specs["localisation4"]        = "str";
    $specs["localisation5"]        = "str";
    $specs["localisation6"]        = "str";
    $specs["localisation7"]        = "str";
    $specs["localisation8"]        = "str";
    $specs["echange_hprim21_id"]   .= " back|sejours_hprim21";

    return $specs;
  }

  /**
   * @inheritdoc
   */
  function bindToLine($line, CHPrim21Reader &$reader, CHprim21Object $patient = null, CHprim21Medecin $medecin = null) {
    $this->setHprim21ReaderVars($reader);
    $this->hprim21_patient_id = $patient->_id;
    if ($medecin) {
      $this->hprim21_medecin_id = $medecin->_id;
    }
    
    $elements = explode($reader->separateur_champ, $line);
  
    if (count($elements) < 26) {
      $reader->error_log[] = "Champs manquant dans le segment patient (sejour) : ".count($elements)." champs trouvés";
      return false;
    }
    if (!$elements[4]) {
      //$reader->error_log[] = "Identifiant externe manquant dans le segment patient (sejour)";
      return true;
    }

    $NDA = explode($reader->separateur_sous_champ, $elements[4]);
    $this->external_id          = $NDA[0];
    $this->loadMatchingObject();
    $this->date_mouvement       = $this->getDateTimeFromHprim($elements[23]);
    $this->statut_admission     = $elements[24];
    $localisation               = explode($reader->separateur_sous_champ, $elements[25]);
    $this->localisation_lit     = $localisation[0];
    $this->localisation_chambre = $localisation[1];
    $this->localisation_service = $localisation[2];
    if (isset($localisation[3])) {
      $this->localisation4      = $localisation[3];
    }
    if (isset($localisation[4])) {
      $this->localisation5      = $localisation[4];
    }
    if (isset($localisation[5])) {
      $this->localisation6      = $localisation[5];
    }
    if (isset($localisation[6])) {
      $this->localisation7      = $localisation[6];
    }
    if (isset($localisation[7])) {
      $this->localisation8      = $localisation[7];
    }
    return true;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    $this->_view = "Séjour du ".CMbDT::format($this->date_mouvement, CAppUI::conf("date"))." [".$this->external_id."]";
  }
  
  function loadRefHprim21Medecin(){
    $this->_ref_hprim21_medecin = new CHprim21Medecin();
    $this->_ref_hprim21_medecin->load($this->hprim21_medecin_id);
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd(){
    // Chargement du séjour correspondant
    $this->_ref_sejour = new CSejour();
    $this->_ref_sejour->load($this->sejour_id);
    $this->loadRefHprim21Medecin();
  }
}
