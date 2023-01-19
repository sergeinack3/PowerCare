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
 * Permet d'editer des échéances pour les factures
 */
class CEcheance extends CMbObject {
  // DB Table key
  public $echeance_id;

  // DB Fields
  public $object_id;
  public $object_class;
  public $date;
  public $montant;
  public $description;
  public $num_reference;

  // Object References
  /** @var  CFactureCabinet|CFactureEtablissement $_ref_object*/
  public $_ref_object;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'facture_echeance';
    $spec->key   = 'echeance_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["object_id"]    = "ref notNull class|CStoredObject meta|object_class back|echeances";
    $props["object_class"]  = "enum notNull list|CFactureCabinet|CFactureEtablissement default|CFactureCabinet";
    $props["date"]          = "date notNull";
    $props["montant"]       = "currency notNull decimals|2";
    $props["description"]   = "text";
    $props["num_reference"] = "str minLength|16 maxLength|27";
    return $props;
  }

  /**
   * @inheritDoc
   */
  function store() {
    $generate_num_reference = !$this->_id;
    if ($msg = parent::store()) {
      return $msg;
    }
    if (!$generate_num_reference) {
      return false;
    }
    $this->getNumReference();
    return $this->store();
  }

  /**
   * Génération et récupération du numéro de référence de l'échéance
   *
   * @return string
   */
  function getNumReference() {
    if ($this->num_reference) {
      return $this->num_reference;
    }
    /** @var CFacture $facture */
    $facture = $this->loadTargetObject();
    if (!$facture->num_reference || !$this->_id) {
      return $this->num_reference = "";
    }

    return $this->num_reference = substr($facture->num_reference, 0, 10) .
      sprintf("%08s", $this->_id) .
      substr($facture->num_reference, -9);
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