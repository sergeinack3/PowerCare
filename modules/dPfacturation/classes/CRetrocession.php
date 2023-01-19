<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Ccam\CActe;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Permet d'éditer des relances pour les factures impayées
 */
class CRetrocession extends CMbObject {
  // DB Table key
  public $retrocession_id;
  
  // DB Fields
  public $praticien_id;
  public $nom;
  public $type;
  public $valeur;
  public $pct_pm;
  public $pct_pt;
  public $code_class;
  public $code;
  public $use_pm;
  public $active;

  // Distant Field
  public $_montant_total;
  
  // Object References
  /** @var  CMediusers $_ref_praticien*/
  public $_ref_praticien;
  /** @var  CActe $_ref_acte*/
  public $_ref_acte;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'retrocession';
    $spec->key   = 'retrocession_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["praticien_id"]= "ref notNull class|CMediusers back|retrocession";
    $props["nom"]         = "str notNull";
    $props["type"]        = "enum list|montant|pct|autre default|montant";
    $props["valeur"]      = "currency";
    $props["pct_pm"]      = "pct default|0";
    $props["pct_pt"]      = "pct default|0";
    $props["code_class"]  = "enum list|CActeCCAM|CActeNAGP default|CActeCCAM";
    $props["code"]        = "str";
    $props["use_pm"]      = "bool default|1";
    $props["active"]      = "bool default|1";

    $props["_montant_total"]  = "currency";
    return $props;
  }
  
  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->nom;
  }
  

  /**
   * Chargement du praticien de la rétrocession
   * 
   * @return $this->_ref_praticien
   */
  function loadRefPraticien(){
    if (!$this->_ref_praticien) {
      $this->_ref_praticien = $this->loadFwdRef("praticien_id", true);
    }
    return $this->_ref_praticien;
  }
  
  /**
   * Chargement de l'acte correspondant au code
   * 
   * @return $this->_ref_acte
   */
  function loadRefCode(){
    if (!$this->_ref_acte) {
      $this->_ref_acte = new $this->code_class;
      $this->_ref_acte->code = $this->code;
      $this->_ref_acte->updateMontantBase();
    }
    return $this->_ref_acte;
  }
  
  /**
   * Mise à jour du montant total de la rétrocession
   * 
   * @param string $code code pour mettre à jour
   * 
   * @return $this->_ref_acte
   */
  function updateMontant ($code = "") {
    $this->_montant_total = 0;
    if ($code == $this->code || !$code) {
      if ($this->type == "montant") {
        $this->_montant_total = $this->valeur;
      }
      elseif ($this->type == "pct") {
        $this->loadRefCode();
        $this->_ref_acte->updateFormFields();
        $this->_montant_total = $this->_ref_acte->_montant_facture * $this->valeur/100;
      }
    }
    $this->_montant_total = round($this->_montant_total, 2);
    return $this->_montant_total;
  }
}
