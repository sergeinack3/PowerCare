<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation;

use Exception;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Les items d'une facture
 *
 */
class CFactureItem extends CMbObject {
  // DB Table key
  public $factureitem_id;
  
  // DB Fields
  public $object_id;
  public $object_class;
  public $executant_id;
  public $date;
  public $libelle;
  public $code;
  public $type;
  public $montant_base;
  public $montant_depassement;
  public $reduction;
  public $quantite;
  public $cote;
  public $coeff;
  public $pm;
  public $pt;
  public $coeff_pm;
  public $coeff_pt;
  public $code_ref;
  public $code_caisse;
  public $seance;

  public $_ref_object;

  // References
  public $_ref_facture;
  public $_montant_facture;
  public $_montant_total_base;
  public $_montant_total_depassement;
   
  public $_ttc;
  
  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'factureitem';
    $spec->key   = 'factureitem_id';
    return $spec;
  }
  
  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specs = parent::getProps();
    $specs["object_id"]    = "ref notNull class|CFacture meta|object_class back|items";
    $specs["object_class"] = "str notNull class show|0";
    $specs["executant_id"] = "ref class|CMediusers back|executant_item_facture";
    $specs["date"]         = "date notNull";
    $specs["libelle"]      = "text notNull";
    $specs["code"]         = "text notNull";
    $specs["type"]         = "enum notNull list|CActeNGAP|CFraisDivers|CActeCCAM|CActeLPP default|CActeCCAM";
    $specs["montant_base"] = "currency notNull";
    $specs["montant_depassement"] = "currency";
    $specs["reduction"]    = "currency";
    $specs["quantite"]     = "float notNull min|0";
    $specs["cote"]         = "enum list|left|right";
    $specs["coeff"]        = "currency notNull";
    $specs["pm"]           = "currency";
    $specs["pt"]           = "currency";
    $specs["coeff_pm"]     = "currency";
    $specs["coeff_pt"]     = "currency";
    $specs["code_ref"]        = "text";
    $specs["code_caisse"]     = "text";
    $specs["seance"]          = "num";
    return $specs;
  }
  
  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_montant_facture = $this->montant_base + $this->montant_depassement;
    $this->_view = $this->libelle;
    if ($this->type == "CActeNGAP") {
      $this->_montant_total_base        = $this->montant_base;
      $this->_montant_total_depassement = $this->montant_depassement;
    }
    else {
      $this->_montant_total_base        = $this->montant_base * $this->quantite * $this->coeff;
      $this->_montant_total_depassement = $this->montant_depassement * $this->quantite * $this->coeff;
    }
  }
  
  /**
   * Chargement de la facture
   * 
   * @return void
  **/
  function loadRefFacture(){
    return $this->loadTargetObject();
  }

  /**
   * @param CStoredObject $object
   * @deprecated
   * @todo redefine meta raf
   * @return void
   */
  public function setObject(CStoredObject $object) {
    CMbMetaObjectPolyfill::setObject($this, $object);
  }

  /**
   * @param bool $cache
   * @deprecated
   * @todo redefine meta raf
   * @return bool|CStoredObject|CExObject|null
   * @throws Exception
   */
  public function loadTargetObject($cache = true) {
    return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
  }

  /**
   * @inheritDoc
   * @todo remove
   */
  function loadRefsFwd() {
    parent::loadRefsFwd();
    $this->loadTargetObject();
  }
}
