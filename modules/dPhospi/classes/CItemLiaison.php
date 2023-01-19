<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Ccam\CBillingPeriod;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Lien entre un séjour et des items de prestation
 */
class CItemLiaison extends CMbObject {
  // DB Table key
  public $item_liaison_id;

  // DB Fields
  public $sejour_id;
  public $item_souhait_id;
  public $item_realise_id;
  public $sous_item_id;
  public $prestation_id;
  public $date;
  public $quantite;

  /** @var CAffectation */
  public $_ref_affectation;

  /** @var CItemPrestation */
  public $_ref_item;

  /** @var CItemPrestation */
  public $_ref_item_realise;

  /** @var CSousItemPrestation */
  public $_ref_sous_item;

  /** @var CPrestationJournaliere */
  public $_ref_prestation;

  /** @var CSejour */
  public $_ref_sejour;

  // Behaviour field
  public $_delete = false;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec                    = parent::getSpec();
    $spec->table             = "item_liaison";
    $spec->key               = "item_liaison_id";
    $spec->uniques["unique"] = array("date", "sejour_id", "prestation_id");

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                    = parent::getProps();
    $props["sejour_id"]       = "ref notNull class|CSejour cascade back|items_liaisons";
    $props["item_souhait_id"] = "ref class|CItemPrestation back|liaisons_souhaits";
    $props["item_realise_id"] = "ref class|CItemPrestation back|liaisons_realises";
    $props["sous_item_id"]    = "ref class|CSousItemPrestation back|liaisons";
    $props["prestation_id"]   = "ref class|CPrestationJournaliere back|liaisons";
    $props["date"]            = "date";
    $props["quantite"]        = "num default|0";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function check() {
    $sejour = $this->loadRefSejour();
    if ($this->date && ($this->date < CMbDT::date($sejour->entree) || $this->date > CMbDT::date($sejour->sortie))) {
      return CAppUI::tr("CItemLiaison-error-Date outside admit", $this->date, CMbDT::date($sejour->entree), CMbDT::date($sejour->sortie));
    }

    return parent::check();
  }

  /**
   * Charge l'item de prestation souhaité
   *
   * @return CItemPrestation|CStoredObject
   */
  function loadRefItem() {
    return $this->_ref_item = $this->loadFwdRef("item_souhait_id", true);
  }

  /**
   * Charge l'item de prestation réalisé
   *
   * @return CItemPrestation|CStoredObject
   */
  function loadRefItemRealise() {
    return $this->_ref_item_realise = $this->loadFwdRef("item_realise_id", true);
  }

  /**
   * Charge le sous-item
   *
   * @return CSousItemPrestation|CStoredObject
   */
  function loadRefSousItem() {
    return $this->_ref_sous_item = $this->loadFwdRef("sous_item_id", true);
  }

  /**
   * Charge le séjour
   *
   * @return CSejour|CStoredObject
   */
  function loadRefSejour() {
    return $this->_ref_sejour = $this->loadFwdRef("sejour_id", true);
  }

  function loadRefPrestation() {
    return $this->_ref_prestation = $this->loadFwdRef("prestation_id", true);
  }

  /**
   * @inheritDoc
   */
  function store() {
    $this->completeField("sejour_id");

    $sejour = $this->loadRefSejour();

    if ($msg = CBillingPeriod::checkStore($sejour, $this)) {
      return $msg;
    }

    return parent::store();
  }
}
