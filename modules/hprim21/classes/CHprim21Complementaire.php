<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21;

/**
 * The HPRIM 2.1 assurance complémentaire class
 */
class CHprim21Complementaire extends CHprim21Object {
  // DB Table key
  public $hprim21_complementaire_id;
  
  // DB references
  public $hprim21_patient_id;
  
  // DB Fields
  public $code_organisme;
  public $numero_adherent;
  public $debut_droits;
  public $fin_droits;
  public $type_contrat;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'hprim21_complementaire';
    $spec->key   = 'hprim21_complementaire_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specs = parent::getProps();

    $specs["hprim21_patient_id"] = "ref class|CHprim21Patient back|hprim21_complementaires";
    $specs["code_organisme"]     = "str";
    $specs["numero_adherent"]    = "str";
    $specs["debut_droits"]       = "date";
    $specs["fin_droits"]         = "date";
    $specs["type_contrat"]       = "str";
    $specs["echange_hprim21_id"] .= " back|complementaire_hprim21";

    return $specs;
  }

  function bindToLine($line, CHPrim21Reader &$reader, CHprim21Object $patient = null, CHprim21Medecin $medecin = null) {
    $this->setHprim21ReaderVars($reader);
    $this->hprim21_patient_id = $patient->_id;
    
    $elements                 = explode($reader->separateur_champ, $line);
  
    if (count($elements) < 7) {
      $reader->error_log[] = "Champs manquant dans le segment assurance complémentaire";
      return false;
    }
    if (!$elements[2]) {
      $reader->error_log[] = "Identifiant externe dans le segment assurance complémentaire";
    }
    
    $this->external_id        = $patient->external_id.$elements[2];
    $this->loadMatchingObject();
    $this->code_organisme     = $elements[2];
    $this->numero_adherent    = $elements[3];
    $this->debut_droits       = $this->getDateFromHprim($elements[4]);
    $this->fin_droits         = $this->getDateFromHprim($elements[5]);
    $this->type_contrat       = $elements[6];
    
    return true;
  }
}
