<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Exception;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Lien entre une UF secondaire et un élément (chambre, service, praticien, etc)
 */
class CAffectationUfSecondaire extends CMbObject {
  // DB Table key
  public $affectation_uf_second_id;

  // DB Fields
  public $uf_id;

  public $object_class;
  public $object_id;
  public $_ref_object;

  /** @var CUniteFonctionnelle */
  public $_ref_uf;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec                    = parent::getSpec();
    $spec->table             = 'affectation_uf_second';
    $spec->key               = 'affectation_uf_second_id';
    $spec->uniques['unique'] = array("object_class", "object_id", "uf_id");

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                 = parent::getProps();
    $props["uf_id"]        = "ref class|CUniteFonctionnelle notNull back|affectations_uf_secondaire";
    $props["object_id"]    = "ref class|CMbObject meta|object_class cascade notNull back|ufs_secondaires";
    $props["object_class"] = "enum list|CMediusers|CFunctions show|0 notNull";

    return $props;
  }

    /**
     * Chargement de l'objet lié au contexte
     * @return CStoredObject|null
     * @throws Exception
     */
    public function loadRefContexte(){
        return $this->_ref_object = $this->loadFwdRef("object_id", true);
    }

  /**
   * Charge l'UF
   *
   * @return CUniteFonctionnelle
   */
  function loadRefUniteFonctionnelle() {
    return $this->_ref_uf = $this->loadFwdRef("uf_id", true);
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    parent::loadRefsFwd();
    $this->loadTargetObject();
    $this->loadRefUniteFonctionnelle();
    $this->_view = $this->_ref_object->_view . " : " . $this->_ref_uf->_view;
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
}
