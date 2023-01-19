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
 * Liaison entre les éléments facturable et leur facture
 */
class CFactureLiaison extends CMbObject {

  // DB Table key
  public $facture_liaison_id;
  
  // DB Fields
  public $facture_id;
  public $facture_class;
  public $object_id;
  public $object_class;

  public $_ref_object;
  
  // Object References
  /** @var  CFacture $_ref_facture*/
  public $_ref_facture;
  /** @var  CFacturable $_ref_facturable*/
  public $_ref_facturable;
  
  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'facture_liaison';
    $spec->key   = 'facture_liaison_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["facture_id"]    = "ref notNull class|CFacture meta|facture_class back|facture_liaison";
    $props["facture_class"] = "enum notNull list|CFactureCabinet|CFactureEtablissement show|0 default|CFactureCabinet";
    $props["object_id"]     = "ref notNull class|CFacturable meta|object_class cascade back|facturable";
    $props["object_class"] = "str notNull class show|0";
    return $props;
  }
     
  /**
   * Chargement de la facture
   *
   * @return CFacture
   */
  function loadRefFacture() {
    return $this->_ref_facture = $this->loadFwdRef("facture_id", true);
  }
     
  /**
   * Chargement de l'objet facturable
   * 
   * @return CFacturable
   */
  function loadRefFacturable() {
    return $this->_ref_facturable =  $this->loadTargetObject();
  }
  
  /**
   * @see parent::store()
   */
  function store() {
    // Standard store
    if ($msg = parent::store()) {
      return $msg;
    }
    
    $this->loadRefFacture();
    return null;
  }

  /**
   * Clonage de la liaison de la facture
   *
   * @param object $item   l'item
   * @param int    $new_id l'identifant de la facture
   *
   * @return void
   */
  function duplicate($item, $new_id){
    $this->object_id     = $item->object_id;
    $this->object_class  = $item->object_class;
    $this->facture_id    = $new_id;
    $this->facture_class = $item->facture_class;
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