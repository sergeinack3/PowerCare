<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Exception;
use Ox\AppFine\Server\CAppFineItemLiaison;
use Ox\AppFine\Server\Controllers\Front\Privacy\CAccountController;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Items de prestation
 */
class CItemPrestation extends CMbObject {
  /** @var string  */
  public const RESOURCE_TYPE = 'item_prestation';
  /** @var string  */
  public const RELATION_PRESTATION_JOURNALIERE = "prestationJournaliere";
  /** @var string  */
  public const RELATION_SOUS_ITEMS = "sousItems";
  /** @var string  */
  public const RELATION_AF_ITEMS_LIAISON = "afItemsLiaison";

  /** @var string  */
  public const OBJECT_CLASS_PRESTATION_PONCTUELLE = "CPrestationPonctuelle";

  /** @var string  */
  public const OBJECT_CLASS_PRESTATION_JOURNALIERE = "CPrestationJournaliere";

  // DB Table key
  public $item_prestation_id;

  // DB Fields
  public $nom;
  public $actif;
  public $rank;
  public $color;
  public $facturable;
  public $chambre_double;
  public $chambre_part_id;
  public $price;
  public $nom_court;

  public $object_class;
  public $object_id;

  // Form field
  public $_quantite;

  // Pour AppFine
  /** @var bool */
  public $_selected;

  // References
  /** @var CPrestationPonctuelle|CPrestationJournaliere */
  public $_ref_object;

  // Distant fields
  /** @var  CSousItemPrestation[] */
  public $_refs_sous_items;


  // Form fields
  public $_sous_item_facture;

  /**
   * @see parent::getSpec()
   */
  public function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "item_prestation";
    $spec->key   = "item_prestation_id";

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  public function getProps() {
    $props                    = parent::getProps();
    $props["nom"]             = "str notNull seekable fieldset|default";
    $props["actif"]           = "bool default|1 fieldset|default";
    $props["object_id"]       = "ref notNull class|CStoredObject meta|object_class back|items fieldset|default";
    $props["object_class"]    = "enum list|CPrestationPonctuelle|CPrestationJournaliere fieldset|default";
    $props["rank"]            = "num pos default|1 fieldset|extra";
    $props["color"]           = "color show|0 fieldset|extra";
    $props["price"]           = "float fieldset|default";
    $props["facturable"]      = "bool default|1";
    $props["chambre_double"]  = "bool default|0 fieldset|extra";
    $props["chambre_part_id"] = "ref class|CItemPrestation back|chambres_part fieldset|extra";
    $props["nom_court"]       = "str seekable fieldset|default";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  public function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->nom;
  }

  /**
   * Charge la prestation
   *
   * @return CPrestationPonctuelle|CPrestationJournaliere|null
   * @throws Exception
   */
  public function loadRefObject() {
    if (!$this->_id) {
      return null;
    }
    $this->_ref_object = $this->loadTargetObject();
    $this->_shortview  = $this->_ref_object->nom . ' - ' . $this->nom;

    return $this->_ref_object;
  }

  /**
   * Charge les sous-items
   *
   * @param array $where Clauses additionnelles
   *
   * @return CSousItemPrestation[]|CStoredObject[]
   * @throws Exception
   */
  public function loadRefsSousItems($where = []) {
    return $this->_refs_sous_items = $this->loadBackRefs("sous_items", "nom", null, null, null, null, null, $where);
  }

  /**
   * @param CStoredObject $object
   *
   * @return void
   * @todo redefine meta raf
   * @deprecated
   */
  public function setObject(CStoredObject $object) {
    CMbMetaObjectPolyfill::setObject($this, $object);
  }

  /**
   * @param bool $cache
   *
   * @return bool|CStoredObject|CExObject|null
   * @throws Exception
   * @deprecated
   * @todo redefine meta raf
   */
  public function loadTargetObject($cache = true) {
    return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
  }

  /**
   * @inheritDoc
   * @throws Exception
   * @todo remove
   */
  public function loadRefsFwd() {
    parent::loadRefsFwd();
    $this->loadTargetObject();
  }

  /**
   * @return Item|null
   * @throws ApiException
   * @throws Exception
   */
  public function getResourcePrestationJournaliere(): ?Item {
    $prestation_journaliere = $this->loadRefObject();
    if (!$prestation_journaliere || !$prestation_journaliere->_id) {
      return null;
    }

    $res = new Item($prestation_journaliere);
    $res->setType(CPrestationJournaliere::RESOURCE_TYPE);

    return $res;
  }

    /**
     * @return Collection|null
     * @throws ApiException
     * @throws Exception
     */
    public function getResourceAfItemsLiaison(): ?Collection
    {
        $where    = CAccountController::$prestation_evenements_ids ? [
            'evenement_medical_id' => $this->getDS()->prepareIn(
                CAccountController::$prestation_evenements_ids
            ),
        ] : [];
        $af_items = $this->loadBackRefs('appfine_liaisons_souhaits', null, null, null, null, null, null, $where);
        if (!$af_items) {
            return null;
        }

        return new Collection($af_items);
    }

  /**
   * @return Item|null
   * @throws ApiException
   * @throws Exception
   */
  public function getResourceSousItems(): ?Collection {
    $sous_items = $this->loadRefsSousItems();
    if (!$sous_items) {
      return null;
    }

    return new Collection($sous_items);
  }
}
