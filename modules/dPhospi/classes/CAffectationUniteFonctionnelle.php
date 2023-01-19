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
 * Lien entre une UF et un élément (chambre, service, praticien, etc)
 */
class CAffectationUniteFonctionnelle extends CMbObject {
  // DB Table key
  public $affectation_uf_id;

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
    $spec        = parent::getSpec();
    $spec->table = 'affectation_uf';
    $spec->key   = 'affectation_uf_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                 = parent::getProps();
    $props["uf_id"]        = "ref class|CUniteFonctionnelle notNull back|affectations_uf";
    $props["object_id"]    = "ref class|CMbObject meta|object_class cascade notNull back|ufs";
    $props["object_class"] = "enum list|CService|CChambre|CLit|CMediusers|CFunctions|CSejour|CProtocole show|0 notNull";

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
   * Retourne les affectations d'uf étant dans les bornes d'une date
   *
   * @return CAffectationUniteFonctionnelle[]
   */
  function getAffDates() {
    $uf     = $this->loadRefUniteFonctionnelle();
    $object = $this->loadTargetObject();

    $ljoin       = array();
    $ljoin["uf"] = "affectation_uf.uf_id = uf.uf_id";

    $where                 = array();
    $where["object_id"]    = " = '$object->_id'";
    $where["object_class"] = " = '$object->_class'";
    $where["uf.type"]      = " = '$uf->type'";
    if ($uf->type_sejour) {
      $where[] = "uf.type_sejour = '$uf->type_sejour' OR uf.type_sejour IS NULL";
    }

    $dates_empty = "OR (uf.date_fin IS NULL AND uf.date_debut IS NULL)";
    if ($uf->date_fin && $uf->date_debut) {
      $where[] = "(uf.date_debut <= '$uf->date_fin' AND uf.date_fin >= '$uf->date_debut')
      OR (uf.date_debut IS NULL AND uf.date_fin BETWEEN '$uf->date_debut' AND '$uf->date_fin')
      OR (uf.date_fin IS NULL AND uf.date_debut BETWEEN '$uf->date_debut' AND '$uf->date_fin')" . $dates_empty;
    }
    elseif ($uf->date_fin) {
      $where[] = "(uf.date_debut <= '$uf->date_fin' AND (uf.date_fin >= '$uf->date_fin' OR uf.date_fin IS NULL))" . $dates_empty;
    }
    elseif ($uf->date_debut) {
      $where[] = "(uf.date_fin >= '$uf->date_debut' AND (uf.date_debut <= '$uf->date_fin' OR uf.date_debut IS NULL))" . $dates_empty;
    }

    return $this->loadList($where, null, null, "affectation_uf.affectation_uf_id", $ljoin);
  }

  /**
   * @see parent::store()
   */
  function store() {
    if (!$this->_id && count($this->getAffDates())) {
      return "Collision d'affection d'unité fonctionnelle";
    }

    return parent::store();
  }

  /**
   * Récupération de la liste des meta-objets
   * reliés à un objet donné
   *
   * @param CMbObject $object Objet relié
   *
   * @return CStoredObject[]
   * @throws Exception
   */
  function loadListFor(CMbObject $object) {
    $this->setObject($object);
    return $this->loadMatchingList();
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
